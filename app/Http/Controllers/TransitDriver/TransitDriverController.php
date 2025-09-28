<?php

namespace App\Http\Controllers\TransitDriver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Package;
use App\Models\TransitRoute;
use App\Models\TransitBox;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitDriverController extends Controller
{
    /**
     * Afficher l'application mobile
     */
    public function index()
    {
        return view('transit-driver.app');
    }

    /**
     * API - Authentification du livreur de transit
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Authentification pour les livreurs de transit
        $user = User::where('email', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects'
            ], 401);
        }

        // Accepter les DELIVERER pour l'app transit
        if ($user->role !== 'DELIVERER') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé - Rôle requis: Livreur'
            ], 403);
        }

        // Vérifier le statut
        if ($user->account_status !== 'ACTIVE') {
            return response()->json([
                'success' => false,
                'message' => 'Compte inactif. Contactez votre superviseur.'
            ], 403);
        }

        // Créer un token pour l'API
        $token = $user->createToken('transit-driver-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->email,
                'role' => $user->role
            ],
            'token' => $token
        ]);
    }

    /**
     * API - Récupérer la tournée du jour
     */
    public function getTodayRoute(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Rechercher la tournée assignée pour aujourd'hui
        $route = TransitRoute::where('driver_id', $user->id)
                             ->where('date', now()->format('Y-m-d'))
                             ->where('status', 'ASSIGNED')
                             ->with('boxes')
                             ->first();

        if (!$route) {
            return response()->json([
                'success' => true,
                'route' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'route' => [
                'id' => $route->id,
                'from' => $route->origin_depot,
                'to' => $route->destination_depot,
                'date' => $route->date->format('Y-m-d'),
                'boxes_count' => $route->boxes->count(),
                'status' => $route->status
            ]
        ]);
    }

    /**
     * API - Démarrer la tournée
     */
    public function startRoute(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Trouver la tournée assignée pour aujourd'hui
        $route = TransitRoute::where('driver_id', $user->id)
                             ->where('date', now()->format('Y-m-d'))
                             ->where('status', 'ASSIGNED')
                             ->first();

        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune tournée assignée pour aujourd\'hui'
            ], 400);
        }

        // Démarrer la tournée
        if (!$route->start()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de démarrer cette tournée'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tournée démarrée avec succès',
            'route' => [
                'id' => $route->id,
                'status' => $route->status,
                'started_at' => $route->started_at->format('H:i')
            ]
        ]);
    }

    /**
     * API - Scanner pour charger une boîte
     */
    public function scanToLoad(Request $request): JsonResponse
    {
        $request->validate([
            'box_code' => 'required|string'
        ]);

        $user = Auth::user();
        $boxCode = $request->box_code;

        // Trouver la tournée active du livreur
        $activeRoute = TransitRoute::where('driver_id', $user->id)
                                   ->where('status', 'IN_PROGRESS')
                                   ->first();

        if (!$activeRoute) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune tournée active. Veuillez d\'abord démarrer votre tournée.'
            ], 400);
        }

        // Vérifier que la boîte existe et appartient à cette tournée
        $box = TransitBox::where('code', $boxCode)
                         ->where('route_id', $activeRoute->id)
                         ->first();

        if (!$box) {
            return response()->json([
                'success' => false,
                'message' => 'ERREUR: Cette boîte n\'est pas assignée à votre tournée.',
                'error_type' => 'WRONG_ROUTE'
            ], 400);
        }

        // Vérifier que la boîte peut être chargée
        if (!$box->canBeLoaded()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette boîte ne peut pas être chargée (statut: ' . $box->status . ')',
                'error_type' => 'INVALID_STATUS'
            ], 400);
        }

        // Charger la boîte
        if (!$box->loadIntoTruck()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de la boîte'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Boîte pour {$box->destination_governorate} ajoutée au manifeste",
            'box' => $box->getFormattedInfo()
        ]);
    }

    /**
     * API - Scanner pour décharger une boîte
     */
    public function scanToUnload(Request $request): JsonResponse
    {
        $request->validate([
            'box_code' => 'required|string'
        ]);

        $user = Auth::user();
        $boxCode = $request->box_code;

        // Trouver la tournée active du livreur
        $activeRoute = TransitRoute::where('driver_id', $user->id)
                                   ->where('status', 'IN_PROGRESS')
                                   ->first();

        if (!$activeRoute) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune tournée active'
            ], 400);
        }

        // Vérifier que la boîte existe et appartient à cette tournée
        $box = TransitBox::where('code', $boxCode)
                         ->where('route_id', $activeRoute->id)
                         ->first();

        if (!$box) {
            return response()->json([
                'success' => false,
                'message' => 'ERREUR: Cette boîte n\'est pas dans votre manifeste.',
                'error_type' => 'NOT_IN_MANIFEST'
            ], 400);
        }

        // Vérifier que la boîte peut être déchargée
        if (!$box->canBeUnloaded()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette boîte ne peut pas être déchargée (statut: ' . $box->status . ')',
                'error_type' => 'INVALID_STATUS'
            ], 400);
        }

        // Décharger la boîte
        if (!$box->unloadFromTruck()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du déchargement de la boîte'
            ], 500);
        }

        // Mettre à jour le statut des colis (si implémenté)
        $this->updatePackagesStatus($box->code, 'DELIVERED');

        return response()->json([
            'success' => true,
            'message' => "Boîte pour {$box->destination_governorate} livrée au dépôt",
            'box' => $box->getFormattedInfo()
        ]);
    }

    /**
     * API - Obtenir le manifeste actuel
     */
    public function getCurrentManifest(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Récupérer la tournée active avec ses boîtes
        $activeRoute = TransitRoute::where('driver_id', $user->id)
                                   ->where('status', 'IN_PROGRESS')
                                   ->with(['boxes' => function ($query) {
                                       $query->where('status', 'LOADED')
                                             ->orderBy('destination_governorate');
                                   }])
                                   ->first();

        if (!$activeRoute) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune tournée active'
            ], 400);
        }

        // Formater les boîtes chargées
        $loadedBoxes = $activeRoute->boxes->map(function ($box) {
            return $box->getFormattedInfo();
        });

        return response()->json([
            'success' => true,
            'manifest' => $loadedBoxes,
            'route_info' => [
                'id' => $activeRoute->id,
                'from' => $activeRoute->origin_depot,
                'to' => $activeRoute->destination_depot,
                'total_boxes' => $activeRoute->boxes()->count(),
                'loaded_boxes' => $loadedBoxes->count(),
                'pending_boxes' => $activeRoute->boxes()->where('status', 'PENDING')->count()
            ]
        ]);
    }

    /**
     * API - Terminer la tournée
     */
    public function finishRoute(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Récupérer la tournée active
        $activeRoute = TransitRoute::where('driver_id', $user->id)
                                   ->where('status', 'IN_PROGRESS')
                                   ->with('boxes')
                                   ->first();

        if (!$activeRoute) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune tournée active trouvée'
            ], 400);
        }

        // Vérifier qu'il n'y a plus de boîtes chargées
        $remainingBoxes = $activeRoute->boxes()->where('status', 'LOADED')->count();

        if ($remainingBoxes > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Déchargez toutes les boîtes avant de terminer la tournée',
                'remaining_boxes' => $remainingBoxes
            ], 400);
        }

        // Terminer la tournée
        if (!$activeRoute->complete()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de terminer cette tournée'
            ], 400);
        }

        // Calculer la durée de la tournée
        $duration = null;
        if ($activeRoute->started_at && $activeRoute->completed_at) {
            $duration = $activeRoute->started_at->diffForHumans($activeRoute->completed_at, true);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tournée terminée avec succès',
            'summary' => [
                'total_boxes' => $activeRoute->boxes->count(),
                'delivered_boxes' => $activeRoute->boxes()->where('status', 'DELIVERED')->count(),
                'duration' => $duration,
                'completed_at' => $activeRoute->completed_at->format('H:i')
            ]
        ]);
    }

    /**
     * API - Historique des tournées
     */
    public function getRouteHistory(Request $request): JsonResponse
    {
        $user = Auth::user();

        $routes = TransitRoute::where('driver_id', $user->id)
                              ->with('boxes')
                              ->orderBy('date', 'desc')
                              ->limit(20)
                              ->get()
                              ->map(function ($route) {
                                  $duration = null;
                                  if ($route->started_at && $route->completed_at) {
                                      $duration = $route->started_at->diffForHumans($route->completed_at, true);
                                  }

                                  return [
                                      'id' => $route->id,
                                      'from' => $route->origin_depot,
                                      'to' => $route->destination_depot,
                                      'date' => $route->date->format('d/m/Y'),
                                      'boxes_count' => $route->boxes->count(),
                                      'delivered_boxes' => $route->boxes->where('status', 'DELIVERED')->count(),
                                      'status' => $route->status,
                                      'duration' => $duration,
                                      'started_at' => $route->started_at?->format('H:i'),
                                      'completed_at' => $route->completed_at?->format('H:i')
                                  ];
                              });

        return response()->json([
            'success' => true,
            'history' => $routes
        ]);
    }

    /**
     * Mettre à jour le statut des colis dans une boîte
     */
    private function updatePackagesStatus(string $boxCode, string $status): void
    {
        // Récupérer la boîte pour obtenir les IDs des colis
        $box = TransitBox::where('code', $boxCode)->first();

        if ($box && $box->package_ids) {
            // Mettre à jour le statut de tous les colis dans cette boîte
            Package::whereIn('id', $box->package_ids)
                   ->update(['status' => $status, 'updated_at' => now()]);
        }
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request): JsonResponse
    {
        // Supprimer le token actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnecté avec succès'
        ]);
    }

    /**
     * Dashboard sécurisé (version web)
     */
    public function dashboard()
    {
        return view('transit-driver.dashboard');
    }

    /**
     * Rapports et statistiques
     */
    public function reports()
    {
        $user = Auth::user();

        // Statistiques du livreur de transit
        $stats = [
            'total_routes' => TransitRoute::where('driver_id', $user->id)->count(),
            'completed_routes' => TransitRoute::where('driver_id', $user->id)->where('status', 'COMPLETED')->count(),
            'total_boxes' => TransitBox::whereHas('route', function($query) use ($user) {
                $query->where('driver_id', $user->id);
            })->count(),
            'delivered_boxes' => TransitBox::whereHas('route', function($query) use ($user) {
                $query->where('driver_id', $user->id);
            })->where('status', 'DELIVERED')->count(),
        ];

        return view('transit-driver.reports', compact('stats'));
    }
}