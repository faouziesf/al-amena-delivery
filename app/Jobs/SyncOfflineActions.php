<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Package;
use App\Models\FinancialTransaction;
use App\Services\ActionLogService;
use App\Services\FinancialTransactionService;
use App\Services\PackageScannerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SyncOfflineActions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $delivererId;
    protected $offlineActions;
    protected $deviceInfo;
    protected $syncTimestamp;

    /**
     * Nombre de tentatives maximum
     */
    public $tries = 3;

    /**
     * Timeout en secondes (plus long pour traitement multiple)
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $delivererId,
        array $offlineActions,
        array $deviceInfo = [],
        string $syncTimestamp = null
    ) {
        $this->delivererId = $delivererId;
        $this->offlineActions = $offlineActions;
        $this->deviceInfo = $deviceInfo;
        $this->syncTimestamp = $syncTimestamp ?? now()->toISOString();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $deliverer = User::find($this->delivererId);
            
            if (!$deliverer || $deliverer->role !== 'DELIVERER') {
                throw new \Exception("Livreur invalide: {$this->delivererId}");
            }

            // Authentifier temporairement le livreur pour les opérations
            Auth::login($deliverer);

            $syncResults = [
                'total_actions' => count($this->offlineActions),
                'successful_actions' => 0,
                'failed_actions' => 0,
                'skipped_actions' => 0,
                'conflicts_resolved' => 0,
                'details' => [],
                'sync_started_at' => now()->toISOString(),
                'sync_completed_at' => null,
                'device_info' => $this->deviceInfo
            ];

            Log::info('Début synchronisation actions offline', [
                'deliverer_id' => $this->delivererId,
                'actions_count' => count($this->offlineActions),
                'device_info' => $this->deviceInfo
            ]);

            // Trier les actions par timestamp pour respecter l'ordre chronologique
            $sortedActions = $this->sortActionsByTimestamp($this->offlineActions);

            // Traiter chaque action
            foreach ($sortedActions as $index => $action) {
                try {
                    $result = $this->processOfflineAction($action, $index);
                    
                    $syncResults['details'][] = $result;
                    
                    if ($result['status'] === 'success') {
                        $syncResults['successful_actions']++;
                    } elseif ($result['status'] === 'skipped') {
                        $syncResults['skipped_actions']++;
                    } elseif ($result['status'] === 'conflict_resolved') {
                        $syncResults['conflicts_resolved']++;
                        $syncResults['successful_actions']++;
                    } else {
                        $syncResults['failed_actions']++;
                    }

                } catch (\Exception $e) {
                    $errorResult = [
                        'action_index' => $index,
                        'action_type' => $action['type'] ?? 'unknown',
                        'status' => 'error',
                        'error_message' => $e->getMessage(),
                        'action_timestamp' => $action['timestamp'] ?? null
                    ];
                    
                    $syncResults['details'][] = $errorResult;
                    $syncResults['failed_actions']++;
                    
                    Log::error('Erreur traitement action offline', [
                        'deliverer_id' => $this->delivererId,
                        'action_index' => $index,
                        'action' => $action,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $syncResults['sync_completed_at'] = now()->toISOString();

            // Log global de la synchronisation
            app(ActionLogService::class)->log(
                'OFFLINE_SYNC_COMPLETED',
                'OfflineSync',
                null,
                null,
                'COMPLETED',
                $syncResults
            );

            Log::info('Synchronisation offline terminée', [
                'deliverer_id' => $this->delivererId,
                'results' => $syncResults
            ]);

            // Envoyer notification de résultat au livreur
            $this->notifyDelivererSyncResult($deliverer, $syncResults);

        } catch (\Exception $e) {
            Log::error('Erreur critique synchronisation offline', [
                'deliverer_id' => $this->delivererId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        } finally {
            Auth::logout();
        }
    }

    /**
     * Traiter une action offline spécifique
     */
    private function processOfflineAction(array $action, int $index): array
    {
        $actionTimestamp = $action['timestamp'] ?? now()->toISOString();
        $actionType = $action['type'] ?? 'unknown';

        // Validation de base de l'action
        $validation = $this->validateOfflineAction($action);
        if (!$validation['valid']) {
            return [
                'action_index' => $index,
                'action_type' => $actionType,
                'status' => 'validation_failed',
                'error_message' => implode(', ', $validation['errors']),
                'action_timestamp' => $actionTimestamp
            ];
        }

        // Vérifier si l'action n'a pas déjà été synchronisée (déduplication)
        if ($this->isActionAlreadyProcessed($action)) {
            return [
                'action_index' => $index,
                'action_type' => $actionType,
                'status' => 'skipped',
                'message' => 'Action déjà synchronisée',
                'action_timestamp' => $actionTimestamp
            ];
        }

        // Traiter selon le type d'action
        return match($actionType) {
            'package_scan' => $this->processScanAction($action, $index),
            'package_accept' => $this->processAcceptAction($action, $index),
            'package_pickup' => $this->processPickupAction($action, $index),
            'package_deliver' => $this->processDeliverAction($action, $index),
            'package_unavailable' => $this->processUnavailableAction($action, $index),
            'package_return' => $this->processReturnAction($action, $index),
            'location_update' => $this->processLocationAction($action, $index),
            'photo_upload' => $this->processPhotoAction($action, $index),
            'batch_scan' => $this->processBatchScanAction($action, $index),
            default => [
                'action_index' => $index,
                'action_type' => $actionType,
                'status' => 'unsupported',
                'error_message' => "Type d'action non supporté: {$actionType}",
                'action_timestamp' => $actionTimestamp
            ]
        };
    }

    /**
     * Valider une action offline
     */
    private function validateOfflineAction(array $action): array
    {
        $errors = [];

        // Vérifications obligatoires
        if (empty($action['type'])) {
            $errors[] = 'Type d\'action manquant';
        }

        if (empty($action['timestamp'])) {
            $errors[] = 'Timestamp manquant';
        } else {
            // Vérifier que le timestamp n'est pas trop ancien (plus de 7 jours)
            $actionTime = Carbon::parse($action['timestamp']);
            if ($actionTime->diffInDays(now()) > 7) {
                $errors[] = 'Action trop ancienne (plus de 7 jours)';
            }
        }

        // Vérifications par type d'action
        if (!empty($action['type'])) {
            switch ($action['type']) {
                case 'package_scan':
                case 'package_accept':
                case 'package_pickup':
                case 'package_deliver':
                case 'package_unavailable':
                case 'package_return':
                    if (empty($action['package_id']) && empty($action['package_code'])) {
                        $errors[] = 'ID ou code du colis manquant';
                    }
                    break;
                    
                case 'location_update':
                    if (empty($action['latitude']) || empty($action['longitude'])) {
                        $errors[] = 'Coordonnées GPS manquantes';
                    }
                    break;
                    
                case 'photo_upload':
                    if (empty($action['photo_data']) && empty($action['photo_path'])) {
                        $errors[] = 'Données photo manquantes';
                    }
                    break;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Vérifier si l'action a déjà été traitée
     */
    private function isActionAlreadyProcessed(array $action): bool
    {
        // Créer un hash unique de l'action pour déduplication
        $actionHash = md5(json_encode([
            'type' => $action['type'],
            'package_id' => $action['package_id'] ?? null,
            'package_code' => $action['package_code'] ?? null,
            'timestamp' => $action['timestamp'],
            'deliverer_id' => $this->delivererId
        ]));

        // Vérifier dans un cache ou table de déduplication
        return cache()->has("offline_action_{$actionHash}");
    }

    /**
     * Marquer une action comme traitée
     */
    private function markActionAsProcessed(array $action): void
    {
        $actionHash = md5(json_encode([
            'type' => $action['type'],
            'package_id' => $action['package_id'] ?? null,
            'package_code' => $action['package_code'] ?? null,
            'timestamp' => $action['timestamp'],
            'deliverer_id' => $this->delivererId
        ]));

        // Marquer dans le cache pour 7 jours
        cache()->put("offline_action_{$actionHash}", true, now()->addDays(7));
    }

    /**
     * Traiter action scan de colis
     */
    private function processScanAction(array $action, int $index): array
    {
        try {
            $packageCode = $action['package_code'] ?? null;
            if (!$packageCode) {
                throw new \Exception('Code colis manquant pour le scan');
            }

            $scannerService = app(PackageScannerService::class);
            $scanResult = $scannerService->scanCode($packageCode);

            $this->markActionAsProcessed($action);

            return [
                'action_index' => $index,
                'action_type' => 'package_scan',
                'status' => 'success',
                'message' => 'Scan synchronisé avec succès',
                'action_timestamp' => $action['timestamp'],
                'scan_result' => $scanResult
            ];

        } catch (\Exception $e) {
            return [
                'action_index' => $index,
                'action_type' => 'package_scan',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'action_timestamp' => $action['timestamp']
            ];
        }
    }

    /**
     * Traiter action acceptation de colis
     */
    private function processAcceptAction(array $action, int $index): array
    {
        try {
            $package = $this->findPackage($action);
            if (!$package) {
                throw new \Exception('Colis introuvable');
            }

            // Vérifier conflit : si le colis n'est plus AVAILABLE
            if ($package->status !== 'AVAILABLE') {
                return $this->handleConflict($action, $index, $package, 'Colis déjà accepté par un autre livreur');
            }

            return DB::transaction(function () use ($package, $action, $index) {
                $package->update([
                    'assigned_deliverer_id' => $this->delivererId,
                    'assigned_at' => $action['timestamp'],
                    'status' => 'ACCEPTED'
                ]);

                app(ActionLogService::class)->log(
                    'OFFLINE_PACKAGE_ACCEPTED',
                    'Package',
                    $package->id,
                    'AVAILABLE',
                    'ACCEPTED',
                    [
                        'package_code' => $package->package_code,
                        'offline_timestamp' => $action['timestamp'],
                        'sync_timestamp' => now()->toISOString()
                    ]
                );

                $this->markActionAsProcessed($action);

                return [
                    'action_index' => $index,
                    'action_type' => 'package_accept',
                    'status' => 'success',
                    'message' => "Colis {$package->package_code} accepté",
                    'action_timestamp' => $action['timestamp'],
                    'package_id' => $package->id
                ];
            });

        } catch (\Exception $e) {
            return [
                'action_index' => $index,
                'action_type' => 'package_accept',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'action_timestamp' => $action['timestamp']
            ];
        }
    }

    /**
     * Traiter action pickup de colis
     */
    private function processPickupAction(array $action, int $index): array
    {
        try {
            $package = $this->findPackage($action);
            if (!$package) {
                throw new \Exception('Colis introuvable');
            }

            if ($package->assigned_deliverer_id !== $this->delivererId) {
                throw new \Exception('Colis non assigné à ce livreur');
            }

            if ($package->status !== 'ACCEPTED') {
                return $this->handleConflict($action, $index, $package, 'Colis pas dans le bon statut pour pickup');
            }

            return DB::transaction(function () use ($package, $action, $index) {
                $updateData = [
                    'status' => 'PICKED_UP',
                    'picked_up_at' => $action['timestamp'],
                    'pickup_notes' => $action['notes'] ?? null
                ];

                $package->update($updateData);

                // Traiter photo si présente
                if (!empty($action['photo_data'])) {
                    $photoPath = $this->saveOfflinePhoto($action['photo_data'], 'pickups/' . $package->id);
                    $package->update(['pickup_photo' => $photoPath]);
                }

                app(ActionLogService::class)->log(
                    'OFFLINE_PACKAGE_PICKED_UP',
                    'Package',
                    $package->id,
                    'ACCEPTED',
                    'PICKED_UP',
                    [
                        'package_code' => $package->package_code,
                        'offline_timestamp' => $action['timestamp'],
                        'sync_timestamp' => now()->toISOString(),
                        'has_photo' => !empty($action['photo_data'])
                    ]
                );

                $this->markActionAsProcessed($action);

                return [
                    'action_index' => $index,
                    'action_type' => 'package_pickup',
                    'status' => 'success',
                    'message' => "Pickup {$package->package_code} synchronisé",
                    'action_timestamp' => $action['timestamp'],
                    'package_id' => $package->id
                ];
            });

        } catch (\Exception $e) {
            return [
                'action_index' => $index,
                'action_type' => 'package_pickup',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'action_timestamp' => $action['timestamp']
            ];
        }
    }

    /**
     * Traiter action livraison de colis
     */
    private function processDeliverAction(array $action, int $index): array
    {
        try {
            $package = $this->findPackage($action);
            if (!$package) {
                throw new \Exception('Colis introuvable');
            }

            if ($package->assigned_deliverer_id !== $this->delivererId) {
                throw new \Exception('Colis non assigné à ce livreur');
            }

            if (!in_array($package->status, ['PICKED_UP', 'UNAVAILABLE'])) {
                return $this->handleConflict($action, $index, $package, 'Colis pas dans le bon statut pour livraison');
            }

            // Vérification COD exacte
            $expectedCod = (float) $package->cod_amount;
            $collectedCod = (float) ($action['cod_collected'] ?? 0);
            
            if (abs($collectedCod - $expectedCod) > 0.001) {
                throw new \Exception("COD incorrect! Attendu: {$expectedCod} DT, Collecté: {$collectedCod} DT");
            }

            return DB::transaction(function () use ($package, $action, $index, $collectedCod) {
                // Mettre à jour le colis
                $updateData = [
                    'status' => 'DELIVERED',
                    'delivered_at' => $action['timestamp'],
                    'delivery_notes' => $action['notes'] ?? null,
                    'recipient_signature' => $action['recipient_signature'] ?? null
                ];

                $package->update($updateData);

                // Traiter photo si présente
                if (!empty($action['photo_data'])) {
                    $photoPath = $this->saveOfflinePhoto($action['photo_data'], 'deliveries/' . $package->id);
                    $package->update(['delivery_photo' => $photoPath]);
                }

                // Ajouter COD au wallet livreur
                $deliverer = User::find($this->delivererId);
                $deliverer->ensureWallet();

                app(FinancialTransactionService::class)->processTransaction([
                    'user_id' => $this->delivererId,
                    'type' => 'COD_COLLECTION',
                    'amount' => $collectedCod,
                    'description' => "COD collecté offline - Colis #{$package->package_code}",
                    'reference' => $package->package_code,
                    'package_id' => $package->id,
                    'metadata' => [
                        'offline_timestamp' => $action['timestamp'],
                        'sync_timestamp' => now()->toISOString(),
                        'delivery_type' => 'offline_sync'
                    ]
                ]);

                app(ActionLogService::class)->log(
                    'OFFLINE_PACKAGE_DELIVERED',
                    'Package',
                    $package->id,
                    $package->getOriginal('status'),
                    'DELIVERED',
                    [
                        'package_code' => $package->package_code,
                        'cod_collected' => $collectedCod,
                        'offline_timestamp' => $action['timestamp'],
                        'sync_timestamp' => now()->toISOString()
                    ]
                );

                $this->markActionAsProcessed($action);

                return [
                    'action_index' => $index,
                    'action_type' => 'package_deliver',
                    'status' => 'success',
                    'message' => "Livraison {$package->package_code} synchronisée - COD {$collectedCod} DT",
                    'action_timestamp' => $action['timestamp'],
                    'package_id' => $package->id,
                    'cod_amount' => $collectedCod
                ];
            });

        } catch (\Exception $e) {
            return [
                'action_index' => $index,
                'action_type' => 'package_deliver',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'action_timestamp' => $action['timestamp']
            ];
        }
    }

    /**
     * Traiter action client indisponible
     */
    private function processUnavailableAction(array $action, int $index): array
    {
        try {
            $package = $this->findPackage($action);
            if (!$package) {
                throw new \Exception('Colis introuvable');
            }

            return DB::transaction(function () use ($package, $action, $index) {
                // Incrémenter tentatives
                $package->increment('delivery_attempts');
                $attemptCount = $package->fresh()->delivery_attempts;

                $updateData = [
                    'unavailable_reason' => $action['reason'] ?? 'CLIENT_ABSENT',
                    'unavailable_notes' => $action['notes'] ?? null,
                    'last_attempt_date' => $action['timestamp']
                ];

                // Déterminer nouveau statut selon tentatives
                if ($attemptCount >= 3) {
                    $updateData['status'] = 'VERIFIED';
                    $updateData['verification_notes'] = "3 tentatives offline échouées";
                    $updateData['verified_at'] = $action['timestamp'];
                } else {
                    $updateData['status'] = 'UNAVAILABLE';
                }

                $package->update($updateData);

                app(ActionLogService::class)->log(
                    'OFFLINE_DELIVERY_ATTEMPT_FAILED',
                    'Package',
                    $package->id,
                    $package->getOriginal('status'),
                    $updateData['status'],
                    [
                        'attempt_count' => $attemptCount,
                        'reason' => $action['reason'] ?? 'CLIENT_ABSENT',
                        'offline_timestamp' => $action['timestamp']
                    ]
                );

                $this->markActionAsProcessed($action);

                return [
                    'action_index' => $index,
                    'action_type' => 'package_unavailable',
                    'status' => 'success',
                    'message' => "Tentative #{$attemptCount} enregistrée pour {$package->package_code}",
                    'action_timestamp' => $action['timestamp'],
                    'package_id' => $package->id,
                    'attempt_count' => $attemptCount
                ];
            });

        } catch (\Exception $e) {
            return [
                'action_index' => $index,
                'action_type' => 'package_unavailable',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'action_timestamp' => $action['timestamp']
            ];
        }
    }

    /**
     * Traiter action retour expéditeur
     */
    private function processReturnAction(array $action, int $index): array
    {
        try {
            $package = $this->findPackage($action);
            if (!$package) {
                throw new \Exception('Colis introuvable');
            }

            return DB::transaction(function () use ($package, $action, $index) {
                $updateData = [
                    'status' => 'RETURNED',
                    'returned_at' => $action['timestamp'],
                    'return_reason' => $action['reason'] ?? 'Retourné offline',
                    'return_notes' => $action['notes'] ?? null
                ];

                $package->update($updateData);

                app(ActionLogService::class)->log(
                    'OFFLINE_PACKAGE_RETURNED',
                    'Package',
                    $package->id,
                    $package->getOriginal('status'),
                    'RETURNED',
                    [
                        'package_code' => $package->package_code,
                        'return_reason' => $action['reason'] ?? 'Retourné offline',
                        'offline_timestamp' => $action['timestamp']
                    ]
                );

                $this->markActionAsProcessed($action);

                return [
                    'action_index' => $index,
                    'action_type' => 'package_return',
                    'status' => 'success',
                    'message' => "Retour {$package->package_code} synchronisé",
                    'action_timestamp' => $action['timestamp'],
                    'package_id' => $package->id
                ];
            });

        } catch (\Exception $e) {
            return [
                'action_index' => $index,
                'action_type' => 'package_return',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'action_timestamp' => $action['timestamp']
            ];
        }
    }

    /**
     * Traiter action mise à jour géolocalisation
     */
    private function processLocationAction(array $action, int $index): array
    {
        try {
            $locationData = [
                'latitude' => $action['latitude'],
                'longitude' => $action['longitude'],
                'accuracy' => $action['accuracy'] ?? null,
                'updated_at' => $action['timestamp'],
                'deliverer_id' => $this->delivererId
            ];

            // Stocker dans le cache
            cache()->put("deliverer_location_{$this->delivererId}", $locationData, now()->addMinutes(30));

            $this->markActionAsProcessed($action);

            return [
                'action_index' => $index,
                'action_type' => 'location_update',
                'status' => 'success',
                'message' => 'Position synchronisée',
                'action_timestamp' => $action['timestamp']
            ];

        } catch (\Exception $e) {
            return [
                'action_index' => $index,
                'action_type' => 'location_update',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'action_timestamp' => $action['timestamp']
            ];
        }
    }

    /**
     * Traiter upload photo
     */
    private function processPhotoAction(array $action, int $index): array
    {
        try {
            if (empty($action['photo_data'])) {
                throw new \Exception('Données photo manquantes');
            }

            $photoPath = $this->saveOfflinePhoto(
                $action['photo_data'],
                $action['storage_path'] ?? 'offline_photos'
            );

            $this->markActionAsProcessed($action);

            return [
                'action_index' => $index,
                'action_type' => 'photo_upload',
                'status' => 'success',
                'message' => 'Photo synchronisée',
                'action_timestamp' => $action['timestamp'],
                'photo_path' => $photoPath
            ];

        } catch (\Exception $e) {
            return [
                'action_index' => $index,
                'action_type' => 'photo_upload',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'action_timestamp' => $action['timestamp']
            ];
        }
    }

    /**
     * Gérer les conflits de synchronisation
     */
    private function handleConflict(array $action, int $index, Package $package, string $reason): array
    {
        app(ActionLogService::class)->log(
            'OFFLINE_SYNC_CONFLICT',
            'Package',
            $package->id,
            null,
            null,
            [
                'conflict_reason' => $reason,
                'action_type' => $action['type'],
                'package_status' => $package->status,
                'offline_timestamp' => $action['timestamp'],
                'deliverer_id' => $this->delivererId
            ]
        );

        return [
            'action_index' => $index,
            'action_type' => $action['type'],
            'status' => 'conflict',
            'message' => "Conflit: {$reason}",
            'action_timestamp' => $action['timestamp'],
            'package_id' => $package->id,
            'current_status' => $package->status
        ];
    }

    /**
     * Trouver un package par ID ou code
     */
    private function findPackage(array $action): ?Package
    {
        if (!empty($action['package_id'])) {
            return Package::find($action['package_id']);
        }
        
        if (!empty($action['package_code'])) {
            return Package::where('package_code', $action['package_code'])->first();
        }
        
        return null;
    }

    /**
     * Sauvegarder une photo depuis les données offline
     */
    private function saveOfflinePhoto(string $photoData, string $storagePath): string
    {
        // Décoder les données base64
        $imageData = base64_decode($photoData);
        
        // Générer nom unique
        $filename = uniqid('offline_') . '_' . time() . '.jpg';
        $fullPath = $storagePath . '/' . $filename;
        
        // Sauvegarder sur disque
        Storage::disk('public')->put($fullPath, $imageData);
        
        return $fullPath;
    }

    /**
     * Trier les actions par timestamp
     */
    private function sortActionsByTimestamp(array $actions): array
    {
        usort($actions, function ($a, $b) {
            $timeA = Carbon::parse($a['timestamp'] ?? now());
            $timeB = Carbon::parse($b['timestamp'] ?? now());
            return $timeA->timestamp <=> $timeB->timestamp;
        });
        
        return $actions;
    }

    /**
     * Notifier le livreur du résultat de synchronisation
     */
    private function notifyDelivererSyncResult(User $deliverer, array $results): void
    {
        $message = "Synchronisation terminée: {$results['successful_actions']}/{$results['total_actions']} actions réussies";
        
        if ($results['failed_actions'] > 0) {
            $message .= ", {$results['failed_actions']} échecs";
        }
        
        if ($results['conflicts_resolved'] > 0) {
            $message .= ", {$results['conflicts_resolved']} conflits résolus";
        }

        SendDelivererNotification::dispatch(
            $deliverer->id,
            'SYNC_COMPLETED',
            [
                'message' => $message,
                'results' => $results
            ],
            $results['failed_actions'] > 0 ? 'HIGH' : 'NORMAL'
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Échec synchronisation actions offline', [
            'deliverer_id' => $this->delivererId,
            'actions_count' => count($this->offlineActions),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        app(ActionLogService::class)->log(
            'OFFLINE_SYNC_FAILED',
            'OfflineSync',
            null,
            null,
            'FAILED',
            [
                'deliverer_id' => $this->delivererId,
                'actions_count' => count($this->offlineActions),
                'error' => $exception->getMessage(),
                'device_info' => $this->deviceInfo
            ]
        );
    }

    /**
     * Méthodes statiques pour faciliter l'usage
     */
    public static function syncDelivererActions(
        int $delivererId,
        array $actions,
        array $deviceInfo = []
    ): void {
        self::dispatch($delivererId, $actions, $deviceInfo);
    }
}