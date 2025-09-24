<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientBankAccountController extends Controller
{
    /**
     * Liste des comptes bancaires du client
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $bankAccounts = $user->bankAccounts()
            ->orderBy('is_default', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Formulaire d'ajout de compte bancaire
     */
    public function create()
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        return view('client.bank-accounts.create');
    }

    /**
     * Enregistrer un nouveau compte bancaire
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'iban' => [
                'required',
                'string',
                'regex:/^TN\d{22}$/',
                'unique:client_bank_accounts,iban,NULL,id,client_id,' . $user->id
            ],
            'is_default' => 'boolean'
        ], [
            'iban.regex' => 'Format IBAN invalide. Format attendu: TN suivi de 22 chiffres',
            'iban.unique' => 'Cet IBAN est déjà enregistré pour votre compte.'
        ]);

        try {
            DB::beginTransaction();

            // Nettoyer l'IBAN
            $validated['iban'] = preg_replace('/\s+/', '', strtoupper($validated['iban']));

            // Vérifier le checksum IBAN
            if (!$this->validateIbanChecksum($validated['iban'])) {
                return back()
                    ->withInput()
                    ->withErrors(['iban' => 'IBAN invalide - erreur de contrôle.']);
            }

            $bankAccount = $user->bankAccounts()->create([
                'bank_name' => $validated['bank_name'],
                'account_holder_name' => $validated['account_holder_name'],
                'iban' => $validated['iban'],
                'is_default' => $validated['is_default'] ?? false
            ]);

            // Si c'est le premier compte ou marqué comme défaut
            if ($user->bankAccounts()->count() === 1 || ($validated['is_default'] ?? false)) {
                $bankAccount->setAsDefault();
            }

            DB::commit();

            return redirect()->route('client.bank-accounts.index')
                ->with('success', 'Compte bancaire ajouté avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'ajout: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un compte bancaire
     */
    public function show(ClientBankAccount $bankAccount)
    {
        if ($bankAccount->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce compte bancaire.');
        }

        return view('client.bank-accounts.show', compact('bankAccount'));
    }

    /**
     * Formulaire d'édition d'un compte bancaire
     */
    public function edit(ClientBankAccount $bankAccount)
    {
        if ($bankAccount->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce compte bancaire.');
        }

        return view('client.bank-accounts.edit', compact('bankAccount'));
    }

    /**
     * Mettre à jour un compte bancaire
     */
    public function update(Request $request, ClientBankAccount $bankAccount)
    {
        if ($bankAccount->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce compte bancaire.');
        }

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'iban' => [
                'required',
                'string',
                'regex:/^TN\d{22}$/',
                'unique:client_bank_accounts,iban,' . $bankAccount->id . ',id,client_id,' . Auth::id()
            ],
            'is_default' => 'boolean'
        ], [
            'iban.regex' => 'Format IBAN invalide. Format attendu: TN suivi de 22 chiffres',
            'iban.unique' => 'Cet IBAN est déjà enregistré pour votre compte.'
        ]);

        try {
            DB::beginTransaction();

            // Nettoyer l'IBAN
            $validated['iban'] = preg_replace('/\s+/', '', strtoupper($validated['iban']));

            // Vérifier le checksum IBAN si l'IBAN a changé
            if ($validated['iban'] !== $bankAccount->iban && !$this->validateIbanChecksum($validated['iban'])) {
                return back()
                    ->withInput()
                    ->withErrors(['iban' => 'IBAN invalide - erreur de contrôle.']);
            }

            $bankAccount->update([
                'bank_name' => $validated['bank_name'],
                'account_holder_name' => $validated['account_holder_name'],
                'iban' => $validated['iban']
            ]);

            // Gérer le statut par défaut
            if ($validated['is_default'] ?? false) {
                $bankAccount->setAsDefault();
            }

            DB::commit();

            return redirect()->route('client.bank-accounts.index')
                ->with('success', 'Compte bancaire mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un compte bancaire
     */
    public function destroy(ClientBankAccount $bankAccount)
    {
        if ($bankAccount->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce compte bancaire.');
        }

        if (!$bankAccount->canBeDeleted()) {
            return back()->with('error', 'Impossible de supprimer ce compte bancaire. Il doit rester au moins un compte ou il y a des retraits en cours.');
        }

        try {
            $bankAccount->delete();

            return redirect()->route('client.bank-accounts.index')
                ->with('success', 'Compte bancaire supprimé avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Définir un compte comme défaut
     */
    public function setDefault(ClientBankAccount $bankAccount)
    {
        if ($bankAccount->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce compte bancaire.');
        }

        $bankAccount->setAsDefault();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Compte bancaire défini comme défaut.'
            ]);
        }

        return back()->with('success', 'Compte bancaire défini comme défaut.');
    }

    /**
     * API - Obtenir les comptes bancaires du client
     */
    public function apiIndex()
    {
        $user = Auth::user();

        $bankAccounts = $user->bankAccounts()
            ->orderBy('is_default', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'bank_name' => $account->bank_name,
                    'account_holder_name' => $account->account_holder_name,
                    'masked_iban' => $account->masked_iban,
                    'is_default' => $account->is_default,
                    'display_name' => $account->getDisplayName()
                ];
            });

        return response()->json($bankAccounts);
    }

    /**
     * Validation du checksum IBAN
     */
    private function validateIbanChecksum($iban)
    {
        // Déplacer les 4 premiers caractères à la fin
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        // Remplacer les lettres par des chiffres (A=10, B=11, ..., Z=35)
        $numeric = '';
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (ctype_alpha($char)) {
                $numeric .= (ord(strtoupper($char)) - ord('A') + 10);
            } else {
                $numeric .= $char;
            }
        }

        // Calculer le modulo 97
        return bcmod($numeric, '97') == 1;
    }

    /**
     * Validation en temps réel de l'IBAN
     */
    public function validateIban(Request $request)
    {
        $iban = preg_replace('/\s+/', '', strtoupper($request->input('iban', '')));

        if (empty($iban)) {
            return response()->json([
                'valid' => false,
                'message' => 'IBAN requis'
            ]);
        }

        // Vérifier le format
        if (!preg_match('/^TN\d{22}$/', $iban)) {
            return response()->json([
                'valid' => false,
                'message' => 'Format invalide. Attendu: TN + 22 chiffres'
            ]);
        }

        // Vérifier l'unicité pour ce client
        $exists = ClientBankAccount::where('client_id', Auth::id())
            ->where('iban', $iban)
            ->exists();

        if ($exists) {
            return response()->json([
                'valid' => false,
                'message' => 'Cet IBAN est déjà enregistré'
            ]);
        }

        // Vérifier le checksum
        $validChecksum = $this->validateIbanChecksum($iban);

        return response()->json([
            'valid' => $validChecksum,
            'message' => $validChecksum ? 'IBAN valide' : 'IBAN invalide - erreur de contrôle',
            'formatted' => chunk_split($iban, 4, ' ')
        ]);
    }
}