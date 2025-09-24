<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class ClientProfileController extends Controller
{
    /**
     * Afficher le profil client
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->load('clientProfile');

        return view('client.profile.index', compact('user'));
    }

    /**
     * Afficher le formulaire d'édition du profil
     */
    public function edit()
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->load('clientProfile');

        return view('client.profile.edit', compact('user'));
    }

    /**
     * Mettre à jour le profil client
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'shop_name' => 'nullable|string|max:255',
            'fiscal_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9]{7}[A-Z]{3}[0-9]{3}$/',
            ],
            'business_sector' => 'nullable|string|max:255',
            'identity_document' => [
                'nullable',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])
                    ->max('5mb')
            ]
        ], [
            'fiscal_number.regex' => 'Le matricule fiscal doit être au format: 1234567ABC123',
        ]);

        try {
            DB::beginTransaction();

            // Mettre à jour les informations utilisateur
            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
            ]);

            // Gérer le document d'identité
            $identityDocument = null;
            if ($request->hasFile('identity_document')) {
                // Supprimer l'ancien document s'il existe
                if ($user->clientProfile && $user->clientProfile->identity_document) {
                    Storage::disk('public')->delete($user->clientProfile->identity_document);
                }

                // Sauvegarder le nouveau document
                $identityDocument = $request->file('identity_document')->store(
                    'client-documents/' . $user->id,
                    'public'
                );
            }

            // Créer ou mettre à jour le profil client
            $profileData = [
                'shop_name' => $validated['shop_name'],
                'fiscal_number' => $validated['fiscal_number'],
                'business_sector' => $validated['business_sector'],
            ];

            if ($identityDocument) {
                $profileData['identity_document'] = $identityDocument;
            }

            $user->clientProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );

            DB::commit();

            return redirect()->route('client.profile.index')
                ->with('success', 'Profil mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Supprimer le fichier si l'upload a réussi mais la transaction a échoué
            if (isset($identityDocument)) {
                Storage::disk('public')->delete($identityDocument);
            }

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Télécharger le document d'identité
     */
    public function downloadIdentityDocument()
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->load('clientProfile');

        if (!$user->clientProfile || !$user->clientProfile->identity_document) {
            abort(404, 'Document non trouvé.');
        }

        $path = storage_path('app/public/' . $user->clientProfile->identity_document);

        if (!file_exists($path)) {
            abort(404, 'Fichier non trouvé sur le serveur.');
        }

        return response()->download($path);
    }

    /**
     * Supprimer le document d'identité
     */
    public function deleteIdentityDocument()
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->load('clientProfile');

        if (!$user->clientProfile || !$user->clientProfile->identity_document) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun document à supprimer.'
            ], 404);
        }

        try {
            // Supprimer le fichier
            Storage::disk('public')->delete($user->clientProfile->identity_document);

            // Mettre à jour la base de données
            $user->clientProfile->update(['identity_document' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Document supprimé avec succès.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API - Obtenir les informations du profil
     */
    public function apiProfile()
    {
        $user = Auth::user();
        $user->load('clientProfile');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'account_status' => $user->account_status,
                'verified_at' => $user->verified_at,
                'created_at' => $user->created_at,
                'last_login' => $user->last_login,
            ],
            'profile' => $user->clientProfile ? [
                'shop_name' => $user->clientProfile->shop_name,
                'fiscal_number' => $user->clientProfile->fiscal_number,
                'business_sector' => $user->clientProfile->business_sector,
                'has_identity_document' => !empty($user->clientProfile->identity_document),
                'completion_percentage' => $user->clientProfile->getCompletionPercentage(),
                'has_business_info' => $user->clientProfile->hasBusinessInfo(),
                'valid_pricing' => $user->clientProfile->hasValidPricing(),
                'delivery_price' => $user->clientProfile->offer_delivery_price,
                'return_price' => $user->clientProfile->offer_return_price,
            ] : null
        ]);
    }

    /**
     * Vérifier la validité du matricule fiscal
     */
    public function validateFiscalNumber(Request $request)
    {
        $fiscalNumber = $request->input('fiscal_number');

        if (empty($fiscalNumber)) {
            return response()->json([
                'valid' => true,
                'message' => 'Matricule fiscal optionnel'
            ]);
        }

        $isValid = preg_match('/^[0-9]{7}[A-Z]{3}[0-9]{3}$/', $fiscalNumber);

        return response()->json([
            'valid' => $isValid,
            'message' => $isValid
                ? 'Matricule fiscal valide'
                : 'Format invalide. Exemple: 1234567ABC123'
        ]);
    }
}