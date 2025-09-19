<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DelivererLocationController extends Controller
{
    /**
     * Mettre à jour la position du livreur
     */
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric'
        ]);

        try {
            $locationData = [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'accuracy' => $validated['accuracy'] ?? null,
                'speed' => $validated['speed'] ?? null,
                'heading' => $validated['heading'] ?? null,
                'updated_at' => now()->toISOString(),
                'deliverer_id' => Auth::id()
            ];

            // Stocker dans le cache pour accès rapide
            Cache::put("deliverer_location_" . Auth::id(), $locationData, now()->addMinutes(30));

            // Stocker dans la base (optionnel, pour historique)
            // DelivererLocation::create($locationData);

            return response()->json([
                'success' => true,
                'message' => 'Position mise à jour'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    /**
     * Obtenir la position actuelle
     */
    public function currentLocation()
    {
        $location = Cache::get("deliverer_location_" . Auth::id());

        return response()->json([
            'success' => true,
            'location' => $location
        ]);
    }

    /**
     * Historique des positions (si implémenté)
     */
    public function locationHistory(Request $request)
    {
        // TODO: Implémenter si table DelivererLocation existe
        return response()->json([
            'success' => true,
            'history' => []
        ]);
    }
}