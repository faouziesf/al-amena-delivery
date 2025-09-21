<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Package;
use App\Models\Notification;
use App\Services\ActionLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendDelivererNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $delivererId;
    protected $notificationType;
    protected $notificationData;
    protected $priority;
    protected $relatedModel;
    protected $relatedId;

    /**
     * Nombre de tentatives maximum
     */
    public $tries = 3;

    /**
     * Timeout en secondes
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $delivererId,
        string $notificationType,
        array $notificationData,
        string $priority = 'NORMAL',
        string $relatedModel = null,
        int $relatedId = null
    ) {
        $this->delivererId = $delivererId;
        $this->notificationType = $notificationType;
        $this->notificationData = $notificationData;
        $this->priority = $priority;
        $this->relatedModel = $relatedModel;
        $this->relatedId = $relatedId;
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

            // 1. Créer notification en base
            $notification = $this->createDatabaseNotification($deliverer);

            // 2. Envoyer notification push (si configuré)
            $this->sendPushNotification($deliverer, $notification);

            // 3. Envoyer notification par autres canaux si urgent
            if ($this->priority === 'URGENT') {
                $this->sendUrgentNotification($deliverer, $notification);
            }

            // 4. Log de succès
            app(ActionLogService::class)->log(
                'NOTIFICATION_SENT',
                'Notification',
                $notification->id,
                null,
                'SENT',
                [
                    'deliverer_id' => $this->delivererId,
                    'notification_type' => $this->notificationType,
                    'priority' => $this->priority,
                    'channels_used' => $this->getChannelsUsed()
                ]
            );

            Log::info('Notification livreur envoyée', [
                'deliverer_id' => $this->delivererId,
                'type' => $this->notificationType,
                'notification_id' => $notification->id
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur envoi notification livreur', [
                'deliverer_id' => $this->delivererId,
                'type' => $this->notificationType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Relancer le job si pas encore au maximum de tentatives
            if ($this->attempts() < $this->tries) {
                $this->release(30); // Retry dans 30 secondes
            }

            throw $e;
        }
    }

    /**
     * Créer la notification en base de données
     */
    private function createDatabaseNotification(User $deliverer): Notification
    {
        $notificationContent = $this->formatNotificationContent();

        return Notification::create([
            'user_id' => $deliverer->id,
            'type' => $this->notificationType,
            'title' => $notificationContent['title'],
            'message' => $notificationContent['message'],
            'priority' => $this->priority,
            'data' => $this->notificationData,
            'action_url' => $notificationContent['action_url'] ?? null,
            'related_type' => $this->relatedModel,
            'related_id' => $this->relatedId,
            'expires_at' => $this->getExpirationDate()
        ]);
    }

    /**
     * Formater le contenu selon le type de notification
     */
    private function formatNotificationContent(): array
    {
        return match($this->notificationType) {
            'NEW_PICKUP_AVAILABLE' => [
                'title' => '📦 Nouveau pickup disponible',
                'message' => $this->notificationData['message'] ?? 'Un nouveau colis est disponible pour pickup',
                'action_url' => route('deliverer.pickups.available')
            ],
            
            'PACKAGE_REASSIGNED' => [
                'title' => '🔄 Colis réassigné',
                'message' => $this->notificationData['message'] ?? 'Un colis vous a été réassigné',
                'action_url' => isset($this->notificationData['package_id']) ? 
                    route('deliverer.packages.show', $this->notificationData['package_id']) : null
            ],
            
            'DELIVERY_URGENT' => [
                'title' => '🚨 Livraison URGENTE',
                'message' => $this->notificationData['message'] ?? 'Livraison urgente à effectuer',
                'action_url' => isset($this->notificationData['package_id']) ? 
                    route('deliverer.packages.show', $this->notificationData['package_id']) : null
            ],
            
            'PAYMENT_ASSIGNED' => [
                'title' => '💰 Paiement client assigné',
                'message' => $this->notificationData['message'] ?? 'Un paiement client vous a été assigné',
                'action_url' => route('deliverer.payments.index')
            ],
            
            'WALLET_HIGH_BALANCE' => [
                'title' => '💳 Solde wallet élevé',
                'message' => $this->notificationData['message'] ?? 'Votre wallet nécessite une vidange',
                'action_url' => route('deliverer.wallet.index')
            ],
            
            'COD_MODIFICATION' => [
                'title' => '📝 COD modifié',
                'message' => $this->notificationData['message'] ?? 'Le COD d\'un colis a été modifié',
                'action_url' => isset($this->notificationData['package_id']) ? 
                    route('deliverer.packages.show', $this->notificationData['package_id']) : null
            ],
            
            'SYSTEM_UPDATE' => [
                'title' => '🔄 Mise à jour système',
                'message' => $this->notificationData['message'] ?? 'Mise à jour système disponible',
                'action_url' => null
            ],
            
            'EMERGENCY_CONTACT' => [
                'title' => '🆘 Contact d\'urgence',
                'message' => $this->notificationData['message'] ?? 'Demande de contact d\'urgence',
                'action_url' => route('deliverer.help.index')
            ],
            
            'BATCH_SCAN_RESULT' => [
                'title' => '📊 Résultat scan par lot',
                'message' => $this->notificationData['message'] ?? 'Résultats du scan par lot disponibles',
                'action_url' => route('deliverer.dashboard')
            ],
            
            default => [
                'title' => 'Notification',
                'message' => $this->notificationData['message'] ?? 'Nouvelle notification',
                'action_url' => null
            ]
        };
    }

    /**
     * Envoyer notification push web
     */
    private function sendPushNotification(User $deliverer, Notification $notification): void
    {
        try {
            // Vérifier si les notifications push sont activées
            if (!config('app.push_notifications_enabled', false)) {
                return;
            }

            $pushPayload = [
                'title' => $notification->title,
                'body' => $notification->message,
                'icon' => '/images/logo-notification.png',
                'badge' => '/images/badge.png',
                'data' => [
                    'notification_id' => $notification->id,
                    'action_url' => $notification->action_url,
                    'type' => $this->notificationType,
                    'priority' => $this->priority
                ],
                'actions' => $this->getPushActions(),
                'requireInteraction' => $this->priority === 'URGENT',
                'silent' => false,
                'timestamp' => now()->timestamp * 1000
            ];

            // Service Web Push (Firebase, OneSignal, etc.)
            $this->sendWebPush($deliverer, $pushPayload);

        } catch (\Exception $e) {
            Log::warning('Erreur envoi push notification', [
                'deliverer_id' => $deliverer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer notification urgente par tous les canaux
     */
    private function sendUrgentNotification(User $deliverer, Notification $notification): void
    {
        try {
            // 1. Email d'urgence (si configuré)
            if (config('app.urgent_email_enabled', false) && $deliverer->email) {
                $this->sendUrgentEmail($deliverer, $notification);
            }

            // 2. SMS d'urgence (si configuré et numéro disponible)
            if (config('app.urgent_sms_enabled', false) && $deliverer->phone) {
                $this->sendUrgentSMS($deliverer, $notification);
            }

            // 3. Notification en temps réel via WebSocket/Pusher
            if (config('app.websocket_enabled', false)) {
                $this->sendRealtimeNotification($deliverer, $notification);
            }

        } catch (\Exception $e) {
            Log::warning('Erreur envoi notification urgente', [
                'deliverer_id' => $deliverer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer Web Push
     */
    private function sendWebPush(User $deliverer, array $payload): void
    {
        // Exemple avec OneSignal
        if (config('services.onesignal.enabled', false)) {
            Http::withHeaders([
                'Authorization' => 'Basic ' . config('services.onesignal.rest_api_key'),
                'Content-Type' => 'application/json'
            ])->post('https://onesignal.com/api/v1/notifications', [
                'app_id' => config('services.onesignal.app_id'),
                'filters' => [
                    [
                        'field' => 'tag',
                        'key' => 'user_id',
                        'relation' => '=',
                        'value' => $deliverer->id
                    ]
                ],
                'headings' => ['en' => $payload['title']],
                'contents' => ['en' => $payload['body']],
                'data' => $payload['data'],
                'web_buttons' => $payload['actions'] ?? [],
                'chrome_web_icon' => $payload['icon'],
                'priority' => $this->priority === 'URGENT' ? 10 : 5
            ]);
        }

        // Exemple avec Firebase Cloud Messaging
        if (config('services.fcm.enabled', false)) {
            // Implémentation FCM
        }
    }

    /**
     * Actions pour notifications push
     */
    private function getPushActions(): array
    {
        return match($this->notificationType) {
            'NEW_PICKUP_AVAILABLE' => [
                [
                    'action' => 'view',
                    'title' => 'Voir',
                    'icon' => '/images/view-icon.png'
                ],
                [
                    'action' => 'accept',
                    'title' => 'Accepter',
                    'icon' => '/images/accept-icon.png'
                ]
            ],
            
            'DELIVERY_URGENT' => [
                [
                    'action' => 'view',
                    'title' => 'Voir détails',
                    'icon' => '/images/urgent-icon.png'
                ]
            ],
            
            'PAYMENT_ASSIGNED' => [
                [
                    'action' => 'view',
                    'title' => 'Voir paiement',
                    'icon' => '/images/payment-icon.png'
                ]
            ],
            
            default => [
                [
                    'action' => 'view',
                    'title' => 'Voir',
                    'icon' => '/images/view-icon.png'
                ]
            ]
        };
    }

    /**
     * Envoyer email d'urgence
     */
    private function sendUrgentEmail(User $deliverer, Notification $notification): void
    {
        // Implémentation email d'urgence
        // Mail::to($deliverer->email)->send(new UrgentDelivererNotification($notification));
    }

    /**
     * Envoyer SMS d'urgence
     */
    private function sendUrgentSMS(User $deliverer, Notification $notification): void
    {
        // Implémentation SMS via Twilio, Nexmo, etc.
    }

    /**
     * Envoyer notification temps réel
     */
    private function sendRealtimeNotification(User $deliverer, Notification $notification): void
    {
        // Implémentation WebSocket/Pusher
        // broadcast(new DelivererNotificationEvent($deliverer, $notification));
    }

    /**
     * Obtenir les canaux utilisés
     */
    private function getChannelsUsed(): array
    {
        $channels = ['database'];
        
        if (config('app.push_notifications_enabled', false)) {
            $channels[] = 'push';
        }
        
        if ($this->priority === 'URGENT') {
            if (config('app.urgent_email_enabled', false)) {
                $channels[] = 'email';
            }
            if (config('app.urgent_sms_enabled', false)) {
                $channels[] = 'sms';
            }
            if (config('app.websocket_enabled', false)) {
                $channels[] = 'websocket';
            }
        }
        
        return $channels;
    }

    /**
     * Obtenir la date d'expiration
     */
    private function getExpirationDate(): ?\Carbon\Carbon
    {
        return match($this->notificationType) {
            'NEW_PICKUP_AVAILABLE' => now()->addHours(6), // 6h pour pickup
            'DELIVERY_URGENT' => now()->addHours(2),      // 2h pour urgence
            'PAYMENT_ASSIGNED' => now()->addDays(1),      // 1 jour pour paiement
            'SYSTEM_UPDATE' => now()->addDays(7),         // 1 semaine pour système
            default => now()->addHours(24)               // 24h par défaut
        };
    }

    /**
     * Méthodes statiques pour faciliter l'usage
     */
    public static function newPickupAvailable(int $delivererId, Package $package): void
    {
        self::dispatch(
            $delivererId,
            'NEW_PICKUP_AVAILABLE',
            [
                'package_id' => $package->id,
                'package_code' => $package->package_code,
                'cod_amount' => $package->cod_amount,
                'delegation_from' => $package->delegationFrom->name ?? null,
                'delegation_to' => $package->delegationTo->name ?? null,
                'message' => "Nouveau pickup disponible: {$package->package_code} ({$package->cod_amount} DT)"
            ],
            'NORMAL',
            'App\Models\Package',
            $package->id
        );
    }

    public static function packageReassigned(int $delivererId, Package $package): void
    {
        self::dispatch(
            $delivererId,
            'PACKAGE_REASSIGNED',
            [
                'package_id' => $package->id,
                'package_code' => $package->package_code,
                'message' => "Le colis {$package->package_code} vous a été réassigné"
            ],
            'HIGH',
            'App\Models\Package',
            $package->id
        );
    }

    public static function deliveryUrgent(int $delivererId, Package $package): void
    {
        self::dispatch(
            $delivererId,
            'DELIVERY_URGENT',
            [
                'package_id' => $package->id,
                'package_code' => $package->package_code,
                'attempts' => $package->delivery_attempts,
                'message' => "🚨 URGENT: Colis {$package->package_code} - {$package->delivery_attempts}ème tentative"
            ],
            'URGENT',
            'App\Models\Package',
            $package->id
        );
    }

    public static function walletHighBalance(int $delivererId, float $balance): void
    {
        self::dispatch(
            $delivererId,
            'WALLET_HIGH_BALANCE',
            [
                'balance' => $balance,
                'formatted_balance' => number_format($balance, 3) . ' DT',
                'message' => "Votre wallet ({$balance} DT) nécessite une vidange"
            ],
            'HIGH'
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Échec définitif envoi notification livreur', [
            'deliverer_id' => $this->delivererId,
            'notification_type' => $this->notificationType,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);

        // Log dans ActionLog pour traçabilité
        app(ActionLogService::class)->log(
            'NOTIFICATION_FAILED',
            'Notification',
            null,
            null,
            'FAILED',
            [
                'deliverer_id' => $this->delivererId,
                'notification_type' => $this->notificationType,
                'attempts' => $this->attempts(),
                'error' => $exception->getMessage()
            ]
        );
    }
}