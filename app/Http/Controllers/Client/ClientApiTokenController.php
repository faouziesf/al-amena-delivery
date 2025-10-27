<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\ApiLog;
use Illuminate\Http\Request;

class ClientApiTokenController extends Controller
{
    /**
     * Afficher la page de gestion du token API
     */
    public function index()
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est vérifié
        if ($user->status !== 'VERIFIED') {
            return redirect()->route('client.dashboard')
                ->with('error', 'Votre compte doit être vérifié pour accéder à l\'API');
        }
        
        // Récupérer le token existant
        $apiToken = ApiToken::where('user_id', $user->id)->first();
        
        // Récupérer les statistiques d'utilisation
        $stats = null;
        if ($apiToken) {
            $stats = ApiLog::getStatsForUser($user->id);
        }
        
        return view('client.settings.api', [
            'apiToken' => $apiToken,
            'stats' => $stats,
        ]);
    }

    /**
     * Générer un nouveau token
     */
    public function generate(Request $request)
    {
        $user = auth()->user();
        
        if ($user->status !== 'VERIFIED') {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte doit être vérifié pour générer un token API'
            ], 403);
        }
        
        // Vérifier si un token existe déjà
        $existingToken = ApiToken::where('user_id', $user->id)->first();
        
        if ($existingToken) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà un token API. Utilisez la fonction "Régénérer" si vous souhaitez le remplacer'
            ], 400);
        }
        
        // Générer le nouveau token
        $apiToken = ApiToken::generate($user->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Token API généré avec succès',
            'token' => $apiToken->token,
        ]);
    }

    /**
     * Régénérer le token existant
     */
    public function regenerate(Request $request)
    {
        $user = auth()->user();
        
        if ($user->status !== 'VERIFIED') {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte doit être vérifié'
            ], 403);
        }
        
        // Générer un nouveau token (l'ancien sera supprimé)
        $apiToken = ApiToken::generate($user->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Token API régénéré avec succès. L\'ancien token a été invalidé',
            'token' => $apiToken->token,
        ]);
    }

    /**
     * Supprimer le token
     */
    public function delete(Request $request)
    {
        $user = auth()->user();
        
        $deleted = ApiToken::where('user_id', $user->id)->delete();
        
        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Token API supprimé avec succès'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Aucun token à supprimer'
        ], 404);
    }

    /**
     * Afficher l'historique des requêtes API
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        
        $perPage = $request->input('per_page', 50);
        
        $logs = ApiLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return view('client.api.history', [
            'logs' => $logs,
        ]);
    }
}
