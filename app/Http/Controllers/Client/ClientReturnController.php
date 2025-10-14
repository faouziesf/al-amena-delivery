<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\ReturnPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientReturnController extends Controller
{
    /**
     * Afficher la liste des retours en attente de traitement
     */
    public function pending()
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        // Récupérer tous les colis retournés du client
        $returnedPackages = Package::where('sender_id', $user->id)
            ->where('status', 'RETURNED')
            ->with(['delegationTo', 'returnPackage'])
            ->orderBy('returned_to_client_at', 'desc')
            ->paginate(15);

        // Calculer le temps restant avant confirmation automatique (48h)
        foreach ($returnedPackages as $package) {
            if ($package->returned_to_client_at) {
                $hoursElapsed = now()->diffInHours($package->returned_to_client_at);
                $package->hours_remaining = max(0, 48 - $hoursElapsed);
                $package->auto_confirm_at = $package->returned_to_client_at->addHours(48);
            }
        }

        return view('client.returns.pending', compact('returnedPackages'));
    }

    /**
     * Afficher les détails d'un colis retourné
     */
    public function show($id)
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $package = Package::where('sender_id', $user->id)
            ->where('id', $id)
            ->with(['delegationTo', 'returnPackage', 'complaints', 'statusHistory'])
            ->firstOrFail();

        // Calculer le temps restant si le colis est retourné
        if ($package->status === 'RETURNED' && $package->returned_to_client_at) {
            $hoursElapsed = now()->diffInHours($package->returned_to_client_at);
            $package->hours_remaining = max(0, 48 - $hoursElapsed);
            $package->auto_confirm_at = $package->returned_to_client_at->addHours(48);
        }

        return view('client.returns.show', compact('package'));
    }

    /**
     * Afficher les détails d'un colis retour associé
     */
    public function showReturnPackage($returnPackageId)
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $returnPackage = ReturnPackage::findOrFail($returnPackageId);

        // Vérifier que le colis original appartient au client
        $originalPackage = Package::where('id', $returnPackage->original_package_id)
            ->where('sender_id', $user->id)
            ->firstOrFail();

        return view('client.returns.return-package-details', compact('returnPackage', 'originalPackage'));
    }

    /**
     * Réclamer un problème pour un colis retourné
     */
    public function reportProblem(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $package = Package::where('sender_id', $user->id)
            ->where('id', $id)
            ->where('status', 'RETURNED')
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // Créer automatiquement un ticket de réclamation
            $complaint = Complaint::create([
                'package_id' => $package->id,
                'client_id' => $user->id,
                'type' => 'NON_RETOURNE',
                'description' => 'Colis non retourné',
                'message' => 'Problème de colis',
                'status' => 'PENDING',
                'priority' => 'MEDIUM',
                'created_at' => now(),
            ]);

            // Changer le statut du colis à PROBLEM
            $package->update([
                'status' => 'PROBLEM',
                'updated_at' => now(),
            ]);

            // Enregistrer dans l'historique
            $package->statusHistory()->create([
                'status' => 'PROBLEM',
                'changed_by' => $user->id,
                'notes' => 'Problème signalé par le client - Colis non retourné',
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('client.returns.pending')
                ->with('success', 'Problème signalé avec succès. Notre équipe va traiter votre réclamation dans les plus brefs délais.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la création de la réclamation', [
                'package_id' => $id,
                'client_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Une erreur est survenue lors du signalement du problème.']);
        }
    }

    /**
     * Valider la réception d'un colis retourné
     */
    public function validateReception($id)
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $package = Package::where('sender_id', $user->id)
            ->where('id', $id)
            ->where('status', 'RETURNED')
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // Changer le statut du colis à RETURN_CONFIRMED
            $package->update([
                'status' => 'RETURN_CONFIRMED',
                'updated_at' => now(),
            ]);

            // Enregistrer dans l'historique
            $package->statusHistory()->create([
                'status' => 'RETURN_CONFIRMED',
                'changed_by' => $user->id,
                'notes' => 'Retour confirmé manuellement par le client',
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('client.returns.pending')
                ->with('success', 'Réception du colis confirmée avec succès. Merci d\'avoir validé le retour.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la validation de réception', [
                'package_id' => $id,
                'client_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Une erreur est survenue lors de la validation de la réception.']);
        }
    }

    /**
     * Obtenir le nombre de retours en attente (pour le badge de notification)
     */
    public function getPendingCount()
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            return response()->json(['count' => 0]);
        }

        $count = Package::where('sender_id', $user->id)
            ->where('status', 'RETURNED')
            ->count();

        return response()->json(['count' => $count]);
    }
}
