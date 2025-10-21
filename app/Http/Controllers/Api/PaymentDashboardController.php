<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentDashboardController extends Controller
{
    /**
     * Données pour le tableau de bord Commercial
     */
    public function commercialDashboard()
    {
        try {
            $user = Auth::user();

            // Appliquer le filtrage par délégations pour Chef Dépôt
            $baseQuery = WithdrawalRequest::with([
                'client.wallet',
                'assignedDepotManager' => function($q) {
                    $q->select('id', 'name', 'depot_name');
                },
                'assignedPackage' => function($q) {
                    $q->select('id', 'package_code', 'payment_withdrawal_id');
                }
            ]);

            // Filtrage selon le rôle
            if ($user->role === 'DEPOT_MANAGER' && $user->assigned_gouvernorats) {
                $assignedGouvernorats = is_array($user->assigned_gouvernorats)
                    ? $user->assigned_gouvernorats
                    : json_decode($user->assigned_gouvernorats, true) ?? [];

                if (!empty($assignedGouvernorats)) {
                    // Les chefs de dépôt voient seulement les paiements espèces des gouvernorats qu'ils gèrent
                    $baseQuery->where('method', 'CASH_DELIVERY')
                              ->where(function($query) use ($assignedGouvernorats) {
                                  // Méthode 1: Via saved_addresses (adresse par défaut)
                                  $query->whereIn('client_id', function($subQuery) use ($assignedGouvernorats) {
                                      $subQuery->select('user_id')
                                               ->from('saved_addresses')
                                               ->where('type', 'CLIENT')
                                               ->where('is_default', true)
                                               ->whereIn('delegation_id', $assignedGouvernorats);
                                  })
                                  // Méthode 2: Via saved_addresses (n'importe quelle adresse si pas de défaut)
                                  ->orWhereIn('client_id', function($subQuery) use ($assignedGouvernorats) {
                                      $subQuery->select('user_id')
                                               ->from('saved_addresses')
                                               ->where('type', 'CLIENT')
                                               ->whereIn('delegation_id', $assignedGouvernorats)
                                               ->whereNotExists(function($innerQuery) {
                                                   $innerQuery->from('saved_addresses as sa2')
                                                            ->whereRaw('sa2.user_id = saved_addresses.user_id')
                                                            ->where('sa2.type', 'CLIENT')
                                                            ->where('sa2.is_default', true);
                                               });
                                  })
                                  // Méthode 3: Via packages récents du client
                                  ->orWhereIn('client_id', function($subQuery) use ($assignedGouvernorats) {
                                      $subQuery->select('sender_id')
                                               ->from('packages')
                                               ->whereIn('delegation_to', $assignedGouvernorats)
                                               ->whereNotExists(function($innerQuery) {
                                                   $innerQuery->from('saved_addresses as sa3')
                                                            ->whereRaw('sa3.user_id = packages.sender_id')
                                                            ->where('sa3.type', 'CLIENT');
                                               });
                                  });
                              });
                }
            }
            // Les commerciaux voient toutes les demandes (aucun filtrage)

            // Onglet 1: Demandes en attente
            $pending = (clone $baseQuery)
                ->where('status', 'PENDING')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'request_code' => $withdrawal->request_code,
                        'client_name' => $withdrawal->client->name,
                        'client_phone' => $withdrawal->client->phone,
                        'amount' => number_format($withdrawal->amount, 3),
                        'amount_raw' => $withdrawal->amount,
                        'method' => $withdrawal->method,
                        'method_display' => $withdrawal->method_display,
                        'created_at' => $withdrawal->created_at->format('d/m/Y H:i'),
                        'wallet_balance' => number_format($withdrawal->client->wallet->balance ?? 0, 3),
                    ];
                });

            // Onglet 2: File d'attente (Approuvées à traiter)
            $queue = (clone $baseQuery)
                ->where('status', 'APPROVED')
                ->orderBy('processed_at', 'asc')
                ->get()
                ->map(function ($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'request_code' => $withdrawal->request_code,
                        'client_name' => $withdrawal->client->name,
                        'client_phone' => $withdrawal->client->phone,
                        'amount' => number_format($withdrawal->amount, 3),
                        'amount_raw' => $withdrawal->amount,
                        'method' => $withdrawal->method,
                        'method_display' => $withdrawal->method_display,
                        'bank_details' => $withdrawal->bank_details,
                        'approved_at' => $withdrawal->processed_at?->format('d/m/Y H:i'),
                        'needs_action' => true,
                    ];
                });

            // Onglet 3: Historique (Toutes les demandes terminées)
            $history = (clone $baseQuery)
                ->whereIn('status', ['PROCESSED', 'DELIVERED', 'COMPLETED', 'REJECTED', 'READY_FOR_DELIVERY', 'IN_PROGRESS'])
                ->orderBy('updated_at', 'desc')
                ->limit(100) // Augmenter la limite pour voir plus d'historique
                ->get()
                ->map(function ($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'request_code' => $withdrawal->request_code,
                        'client_name' => $withdrawal->client->name,
                        'client_phone' => $withdrawal->client->phone,
                        'amount' => number_format($withdrawal->amount, 3),
                        'amount_raw' => $withdrawal->amount,
                        'method' => $withdrawal->method,
                        'method_display' => $withdrawal->method_display,
                        'status' => $withdrawal->status,
                        'status_display' => $withdrawal->status_display,
                        'status_color' => $this->getStatusColor($withdrawal->status),
                        'updated_at' => $withdrawal->updated_at->format('d/m/Y H:i'),
                        'virement_reference' => $withdrawal->processing_notes,
                        'depot_manager' => $withdrawal->assignedDepotManager ? [
                            'name' => $withdrawal->assignedDepotManager->name,
                            'depot_name' => $withdrawal->assignedDepotManager->depot_name ?? 'Dépôt'
                        ] : null,
                        'package_code' => $withdrawal->assignedPackage ? $withdrawal->assignedPackage->package_code : null,
                        'can_view_details' => true, // Permettre l'accès aux détails
                    ];
                });

            // Statistiques rapides
            $stats = [
                'pending_count' => $pending->count(),
                'pending_amount' => $pending->sum('amount_raw'),
                'queue_count' => $queue->count(),
                'queue_amount' => $queue->sum('amount_raw'),
                'bank_transfers_queue' => $queue->where('method', 'BANK_TRANSFER')->count(),
                'cash_deliveries_queue' => $queue->where('method', 'CASH_DELIVERY')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'pending' => $pending,
                    'queue' => $queue,
                    'history' => $history,
                    'stats' => $stats,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement du tableau de bord: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approuver une demande de paiement
     */
    public function approve(Request $request, WithdrawalRequest $withdrawal)
    {
        try {
            if ($withdrawal->status !== 'PENDING') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette demande ne peut plus être approuvée.'
                ], 400);
            }

            $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            DB::transaction(function () use ($withdrawal, $request) {
                $withdrawal->approve(Auth::user(), $request->notes);
            });

            return response()->json([
                'success' => true,
                'message' => 'Demande approuvée avec succès.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refuser une demande de paiement
     */
    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        try {
            if ($withdrawal->status !== 'PENDING') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette demande ne peut plus être refusée.'
                ], 400);
            }

            $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            DB::transaction(function () use ($withdrawal, $request) {
                $withdrawal->reject(Auth::user(), $request->reason);
            });

            return response()->json([
                'success' => true,
                'message' => 'Demande refusée avec succès.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du refus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traiter un virement bancaire avec référence unique
     */
    public function processBankTransfer(Request $request, WithdrawalRequest $withdrawal)
    {
        try {
            if ($withdrawal->status !== 'APPROVED' || $withdrawal->method !== 'BANK_TRANSFER') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les virements bancaires approuvés peuvent être traités.'
                ], 400);
            }

            $request->validate([
                'virement_reference' => 'required|string|min:3|max:100|unique:withdrawal_requests,processing_notes',
            ], [
                'virement_reference.required' => 'La référence du virement est obligatoire.',
                'virement_reference.min' => 'La référence doit contenir au moins 3 caractères.',
                'virement_reference.unique' => 'Cette référence a déjà été utilisée pour un autre virement.',
            ]);

            DB::transaction(function () use ($withdrawal, $request) {
                $withdrawal->markAsProcessed($request->virement_reference);
            });

            return response()->json([
                'success' => true,
                'message' => 'Virement traité avec succès. Référence: ' . $request->virement_reference
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assigner un paiement espèce au dépôt selon le gouvernorat du client
     */
    public function assignToDepot(Request $request, WithdrawalRequest $withdrawal)
    {
        try {
            if ($withdrawal->status !== 'APPROVED' || $withdrawal->method !== 'CASH_DELIVERY') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les paiements en espèces approuvés peuvent être assignés.'
                ], 400);
            }

            // Obtenir le gouvernorat du client
            $clientDelegationId = $this->getClientDelegationId($withdrawal->client);

            if (!$clientDelegationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de déterminer le gouvernorat du client. Veuillez vérifier son adresse.'
                ], 400);
            }

            // Trouver le chef de dépôt responsable de ce gouvernorat
            $depotManager = User::where('role', 'DEPOT_MANAGER')
                                ->where('account_status', 'ACTIVE')
                                ->where('is_depot_manager', true)
                                ->whereJsonContains('assigned_gouvernorats', $clientDelegationId)
                                ->first();

            if (!$depotManager) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun chef de dépôt trouvé pour le gouvernorat du client (ID: ' . $clientDelegationId . ').'
                ], 400);
            }

            DB::transaction(function () use ($withdrawal, $depotManager) {
                // Marquer comme prêt pour préparation par le dépôt spécifique
                $withdrawal->update([
                    'status' => 'READY_FOR_DELIVERY',
                    'assigned_depot_manager_id' => $depotManager->id,
                    'delivery_receipt_code' => 'PAY_' . strtoupper(Str::random(6)) . '_' . $withdrawal->id
                ]);

                // Log de l'action
                app(\App\Services\ActionLogService::class)->log(
                    'PAYMENT_ASSIGNED_TO_DEPOT',
                    'WithdrawalRequest',
                    $withdrawal->id,
                    'APPROVED',
                    'READY_FOR_DELIVERY',
                    [
                        'assigned_by' => Auth::id(),
                        'depot_manager_id' => $depotManager->id,
                        'depot_manager_name' => $depotManager->name,
                        'client_delegation_id' => $this->getClientDelegationId($withdrawal->client)
                    ]
                );
            });

            return response()->json([
                'success' => true,
                'message' => "Paiement assigné au dépôt {$depotManager->depot_name} ({$depotManager->name}). Code: {$withdrawal->delivery_receipt_code}",
                'depot_manager' => [
                    'name' => $depotManager->name,
                    'depot_name' => $depotManager->depot_name
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Données pour l'interface Chef de Dépôt
     */
    public function depotDashboard()
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'DEPOT_MANAGER') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux chefs de dépôt.'
                ], 403);
            }

            // Récupérer les gouvernorats assignés
            $assignedGouvernorats = is_array($user->assigned_gouvernorats)
                ? $user->assigned_gouvernorats
                : json_decode($user->assigned_gouvernorats, true) ?? [];

            // Afficher TOUS les paiements en espèce avec filtrage optionnel par gouvernorat
            $paymentsQuery = WithdrawalRequest::with(['client', 'assignedDepotManager', 'assignedPackage'])
                ->where('method', 'CASH_DELIVERY');

            // Si le chef depot a des gouvernorats assignés ET qu'il y a des demandes avec ces gouvernorats,
            // prioriser l'affichage de ces demandes en premier, puis afficher toutes les autres
            if (!empty($assignedGouvernorats)) {
                // Récupérer d'abord les paiements de son gouvernorat
                $priorityPayments = WithdrawalRequest::with(['client', 'assignedDepotManager', 'assignedPackage'])
                    ->where('method', 'CASH_DELIVERY')
                    ->where(function($query) use ($assignedGouvernorats) {
                        // Méthode 1: Via saved_addresses (adresse par défaut)
                        $query->whereIn('client_id', function($subQuery) use ($assignedGouvernorats) {
                            $subQuery->select('user_id')
                                     ->from('saved_addresses')
                                     ->where('type', 'CLIENT')
                                     ->where('is_default', true)
                                     ->whereIn('delegation_id', $assignedGouvernorats);
                        })
                        // Méthode 2: Via saved_addresses (n'importe quelle adresse si pas de défaut)
                        ->orWhereIn('client_id', function($subQuery) use ($assignedGouvernorats) {
                            $subQuery->select('user_id')
                                     ->from('saved_addresses')
                                     ->where('type', 'CLIENT')
                                     ->whereIn('delegation_id', $assignedGouvernorats)
                                     ->whereNotExists(function($innerQuery) {
                                         $innerQuery->from('saved_addresses as sa2')
                                                  ->whereRaw('sa2.user_id = saved_addresses.user_id')
                                                  ->where('sa2.type', 'CLIENT')
                                                  ->where('sa2.is_default', true);
                                     });
                        })
                        // Méthode 3: Via packages récents du client
                        ->orWhereIn('client_id', function($subQuery) use ($assignedGouvernorats) {
                            $subQuery->select('sender_id')
                                     ->from('packages')
                                     ->whereIn('delegation_to', $assignedGouvernorats)
                                     ->whereNotExists(function($innerQuery) {
                                         $innerQuery->from('saved_addresses as sa3')
                                                  ->whereRaw('sa3.user_id = packages.sender_id')
                                                  ->where('sa3.type', 'CLIENT');
                                     });
                        });
                    })
                    ->orderBy('updated_at', 'desc')
                    ->get();

                // Récupérer tous les autres paiements en espèce
                $otherPayments = WithdrawalRequest::with(['client', 'assignedDepotManager', 'assignedPackage'])
                    ->where('method', 'CASH_DELIVERY')
                    ->whereNotIn('id', $priorityPayments->pluck('id'))
                    ->orderBy('updated_at', 'desc')
                    ->limit(50) // Limiter les autres pour éviter la surcharge
                    ->get();

                // Combiner les résultats : priorité d'abord, puis les autres
                $paymentsToPrep = $priorityPayments->concat($otherPayments);
            } else {
                // Si pas de gouvernorats assignés, afficher tous les paiements en espèce
                $paymentsToPrep = WithdrawalRequest::with(['client', 'assignedDepotManager', 'assignedPackage'])
                    ->where('method', 'CASH_DELIVERY')
                    ->orderBy('updated_at', 'desc')
                    ->limit(100) // Limiter pour éviter la surcharge
                    ->get();
            }

            $paymentsToPrep = $paymentsToPrep->map(function ($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'request_code' => $withdrawal->request_code,
                        'delivery_code' => $withdrawal->delivery_receipt_code,
                        'client_name' => $withdrawal->client->name,
                        'client_phone' => $withdrawal->client->phone,
                        'client_address' => $withdrawal->client->address,
                        'amount' => number_format($withdrawal->amount, 3),
                        'amount_raw' => $withdrawal->amount,
                        'status' => $withdrawal->status,
                        'status_display' => $withdrawal->status_display,
                        'status_color' => $this->getStatusColor($withdrawal->status),
                        'ready_since' => $withdrawal->updated_at->format('d/m/Y H:i'),
                        'created_at' => $withdrawal->created_at->format('d/m/Y H:i'),
                        'can_create_package' => in_array($withdrawal->status, ['PENDING', 'APPROVED', 'READY_FOR_DELIVERY', 'DELIVERED', 'COMPLETED']),
                        'package_code' => $withdrawal->assignedPackage ? $withdrawal->assignedPackage->package_code : null,
                        'assigned_depot' => $withdrawal->assignedDepotManager ? [
                            'name' => $withdrawal->assignedDepotManager->name,
                            'depot_name' => $withdrawal->assignedDepotManager->depot_name
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $paymentsToPrep
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un colis de paiement (pour Chef de Dépôt)
     */
    public function createPaymentPackage(Request $request, WithdrawalRequest $withdrawal)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'DEPOT_MANAGER') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux chefs de dépôt.'
                ], 403);
            }

            // Vérifier que le paiement est dans un statut valide pour créer un colis
            $validStatuses = ['READY_FOR_DELIVERY', 'APPROVED', 'PENDING'];
            $validMethods = ['CASH_DELIVERY', 'CASH', 'COD'];
            
            if (!in_array($withdrawal->status, $validStatuses) || !in_array($withdrawal->method, $validMethods)) {
                return response()->json([
                    'success' => false,
                    'message' => "Ce paiement ne peut pas être transformé en colis. Statut actuel: {$withdrawal->status}, Méthode: {$withdrawal->method}"
                ], 400);
            }

            DB::transaction(function () use ($withdrawal, $user) {
                $packageCode = 'PAY_' . strtoupper(Str::random(8));

                // Récupérer la délégation du client
                $clientDelegation = null;
                if ($withdrawal->client->delegation_id) {
                    $clientDelegation = $withdrawal->client->delegation_id;
                } elseif ($withdrawal->client->assigned_delegation) {
                    $clientDelegation = is_numeric($withdrawal->client->assigned_delegation) 
                        ? $withdrawal->client->assigned_delegation 
                        : 1;
                } else {
                    // Essayer de trouver depuis les colis récents
                    $recentPackage = Package::where('sender_id', $withdrawal->client->id)
                        ->whereNotNull('delegation_to')
                        ->orderBy('created_at', 'desc')
                        ->first();
                    $clientDelegation = $recentPackage ? $recentPackage->delegation_to : 1;
                }

                // Récupérer les informations de livraison du client
                $deliveryAddress = $withdrawal->delivery_address ?? $withdrawal->client->address ?? '';
                $deliveryPhone = $withdrawal->delivery_phone ?? $withdrawal->client->phone ?? '';
                $deliveryCity = $withdrawal->delivery_city ?? $withdrawal->client->city ?? 'Non spécifié';
                
                // Créer le colis de paiement selon la structure réelle
                $package = Package::create([
                    'package_code' => $packageCode,
                    'package_type' => Package::TYPE_PAYMENT, // Type PAYMENT
                    'sender_id' => $user->id, // Le Chef de Dépôt devient l'expéditeur
                    'sender_data' => [
                        'name' => $user->depot_name ?? $user->name,
                        'phone' => $user->phone ?? '+21650127192',
                        'address' => 'Dépôt ' . ($user->depot_name ?? 'Principal'),
                    ],
                    'delegation_from' => $user->delegation_id ?? 1,
                    'recipient_data' => [
                        'name' => $withdrawal->client->name,
                        'phone' => $deliveryPhone,
                        'address' => $deliveryAddress,
                        'city' => $deliveryCity,
                    ],
                    'delegation_to' => (int) $clientDelegation,
                    'content_description' => "Enveloppe de Paiement #{$withdrawal->request_code}",
                    'notes' => "Montant: {$withdrawal->amount} DT - Paiement généré automatiquement",
                    'cod_amount' => 0, // ✅ COD = 0 (c'est juste une enveloppe, pas de COD)
                    'delivery_fee' => 0,
                    'return_fee' => 0,
                    'status' => 'AVAILABLE',
                    'requires_signature' => true,
                    'special_instructions' => "ENVELOPPE DE PAIEMENT - Montant: {$withdrawal->amount} DT - Signature obligatoire",
                    'payment_method' => null, // Pas de COD
                    'payment_withdrawal_id' => $withdrawal->id, // Lier au paiement
                ]);

                // Mettre à jour le statut du paiement
                $withdrawal->update([
                    'status' => 'IN_PROGRESS',
                    'assigned_package_id' => $package->id,
                ]);

                // Log de l'action
                app(\App\Services\ActionLogService::class)->log(
                    'PAYMENT_PACKAGE_CREATED',
                    'WithdrawalRequest',
                    $withdrawal->id,
                    'READY_FOR_DELIVERY',
                    'IN_PROGRESS',
                    [
                        'package_id' => $package->id,
                        'package_code' => $packageCode,
                        'created_by' => $user->id
                    ]
                );
            });

            return response()->json([
                'success' => true,
                'message' => 'Colis de paiement créé avec succès.',
                'package_code' => $withdrawal->fresh()->assignedPackage->package_code ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du colis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir l'ID de délégation (gouvernorat) du client
     */
    private function getClientDelegationId(User $client)
    {
        // Essayer d'obtenir à partir de l'adresse par défaut du client
        $defaultAddress = $client->savedAddresses()
                                ->where('type', 'CLIENT')
                                ->where('is_default', true)
                                ->first();

        if ($defaultAddress && $defaultAddress->delegation_id) {
            return $defaultAddress->delegation_id;
        }

        // Si pas d'adresse par défaut, prendre la première adresse client
        $firstAddress = $client->savedAddresses()
                              ->where('type', 'CLIENT')
                              ->orderBy('usage_count', 'desc')
                              ->orderBy('last_used_at', 'desc')
                              ->first();

        if ($firstAddress && $firstAddress->delegation_id) {
            return $firstAddress->delegation_id;
        }

        // Si le client n'a aucune adresse sauvegardée, essayer via ses colis récents
        $recentPackage = $client->sentPackages()
                               ->orderBy('created_at', 'desc')
                               ->first();

        if ($recentPackage && $recentPackage->delegation_to) {
            return $recentPackage->delegation_to;
        }

        return null;
    }

    /**
     * Afficher les détails d'un paiement
     */
    public function showDetails(WithdrawalRequest $withdrawal)
    {
        $user = Auth::user();

        if ($user->role !== 'DEPOT_MANAGER') {
            abort(403, 'Accès réservé aux chefs de dépôt.');
        }

        // Charger les relations
        $withdrawal->load(['client.wallet', 'assignedPackage.assignedDeliverer']);

        return view('depot-manager.payments.payment-details', compact('withdrawal'));
    }

    /**
     * Obtenir la couleur d'affichage d'un statut
     */
    private function getStatusColor($status)
    {
        return match($status) {
            'PENDING' => 'yellow',
            'APPROVED' => 'blue',
            'PROCESSED' => 'green',
            'READY_FOR_DELIVERY' => 'purple',
            'IN_PROGRESS' => 'indigo',
            'DELIVERED', 'COMPLETED' => 'green',
            'REJECTED' => 'red',
            'CANCELLED' => 'gray',
            default => 'gray'
        };
    }
}