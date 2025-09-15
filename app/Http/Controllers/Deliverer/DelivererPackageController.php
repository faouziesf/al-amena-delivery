<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\DeliveryAttempt;
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DelivererPackageController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Colis disponibles pour acceptance
     */
    public function available(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'DELIVERER') {
            abort(403, 'Accès non autorisé.');
        }

        $query = Package::where('status', 'AVAILABLE')
                       ->with(['delegationFrom', 'delegationTo', 'sender']);

        // Filtrer par zones assignées si applicable
        if ($user->delivererProfile && $user->delivererProfile->assigned_delegations) {
            $query->whereIn('delegation_from', $user->delivererProfile->assigned_delegations);
        }

        // Filtres
        if ($request->filled('delegation_from')) {
            $query->where('delegation_from', $request->delegation_from);
        }

        if ($request->filled('delegation_to')) {
            $query->where('delegation_to', $request->delegation_to);
        }

        if ($request->filled('cod_min')) {
            $query->where('cod_amount', '>=', $request->cod_min);
        }

        if ($request->filled('cod_max')) {
            $query->where('cod_amount', '<=', $request->cod_max);
        }

        $packages = $query->orderBy('created_at', 'asc')->paginate(20);

        return view('deliverer.packages.available', compact('packages'));
    }

    /**
     * Colis assignés au livreur
     */
    public function assigned(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'DELIVERER') {
            abort(403, 'Accès non autorisé.');
        }

        $query = Package::where('assigned_deliverer_id', $user->id)
                       ->with(['delegationFrom', 'delegationTo', 'sender']);

        // Filtres par statut
        if ($request->filled('status')) {
            if ($request->status === 'pending_pickup') {
                $query->where('status', 'ACCEPTED');
            } elseif ($request->status === 'in_delivery') {
                $query->where('status', 'PICKED_UP');
            } else {
                $query->where('status', $request->status);
            }
        } else {
            // Par défaut, ne pas montrer les colis terminés
            $query->whereNotIn('status', ['DELIVERED', 'RETURNED', 'CANCELLED']);
        }

        $packages = $query->orderBy('updated_at', 'desc')->paginate(20);

        return view('deliverer.packages.assigned', compact('packages'));
    }

    /**
     * Historique des colis
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'DELIVERER') {
            abort(403, 'Accès non autorisé.');
        }

        $query = Package::where('assigned_deliverer_id', $user->id)
                       ->with(['delegationFrom', 'delegationTo', 'sender']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        $packages = $query->orderBy('updated_at', 'desc')->paginate(20);

        return view('deliverer.packages.history', compact('packages'));
    }

    /**
     * Détails d'un colis
     */
    public function show(Package $package)
    {
        $user = Auth::user();

        // Vérifier les permissions
        if ($package->assigned_deliverer_id !== $user->id && $package->status !== 'AVAILABLE') {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        $package->load([
            'delegationFrom', 
            'delegationTo', 
            'sender',
            'statusHistory.changedBy',
            'deliveryAttempts'
        ]);

        return view('deliverer.packages.show', compact('package'));
    }

    /**
     * Accepter un colis
     */
    public function accept(Package $package)
    {
        $user = Auth::user();
        
        if ($user->role !== 'DELIVERER') {
            abort(403, 'Accès non autorisé.');
        }

        if ($package->status !== 'AVAILABLE') {
            return back()->with('error', 'Ce colis n\'est plus disponible.');
        }

        try {
            DB::beginTransaction();

            $package->update([
                'assigned_deliverer_id' => $user->id,
                'assigned_at' => now()
            ]);

            $package->updateStatus('ACCEPTED', $user, 'Colis accepté par le livreur');

            DB::commit();

            return redirect()->route('deliverer.packages.assigned')
                ->with('success', "Colis #{$package->package_code} accepté avec succès!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Erreur lors de l\'acceptation: ' . $e->getMessage());
        }
    }

    /**
     * Marquer comme récupéré
     */
    public function pickup(Package $package, Request $request)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        if ($package->status !== 'ACCEPTED') {
            return back()->with('error', 'Ce colis ne peut pas être récupéré dans son état actuel.');
        }

        $validated = $request->validate([
            'pickup_notes' => 'nullable|string|max:500',
            'pickup_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            DB::beginTransaction();

            $updateData = ['picked_up_at' => now()];
            
            if ($request->hasFile('pickup_photo')) {
                $path = $request->file('pickup_photo')->store('pickups/' . $package->id, 'public');
                $updateData['pickup_photo_path'] = $path;
            }

            $package->update($updateData);

            $statusMessage = 'Colis récupéré par le livreur';
            if ($validated['pickup_notes']) {
                $statusMessage .= ' - Notes: ' . $validated['pickup_notes'];
            }

            $package->updateStatus('PICKED_UP', $user, $statusMessage);

            DB::commit();

            return back()->with('success', 'Colis marqué comme récupéré!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Erreur lors de la récupération: ' . $e->getMessage());
        }
    }

    /**
     * Marquer comme livré
     */
    public function deliver(Package $package, Request $request)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        if ($package->status !== 'PICKED_UP') {
            return back()->with('error', 'Ce colis ne peut pas être livré dans son état actuel.');
        }

        $validated = $request->validate([
            'delivery_notes' => 'nullable|string|max:500',
            'delivery_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'recipient_signature' => 'nullable|string',
            'cod_collected' => 'required|numeric|min:0',
        ]);

        // Vérifier que le montant COD est correct
        if (abs($validated['cod_collected'] - $package->cod_amount) > 0.001) {
            return back()
                ->withInput()
                ->with('error', "Le montant collecté doit être exactement {$package->cod_amount} DT");
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'delivered_at' => now(),
                'cod_collected_amount' => $validated['cod_collected']
            ];
            
            if ($request->hasFile('delivery_photo')) {
                $path = $request->file('delivery_photo')->store('deliveries/' . $package->id, 'public');
                $updateData['delivery_photo_path'] = $path;
            }

            if ($validated['recipient_signature']) {
                $updateData['recipient_signature'] = $validated['recipient_signature'];
            }

            $package->update($updateData);

            $statusMessage = 'Colis livré avec succès';
            if ($validated['delivery_notes']) {
                $statusMessage .= ' - Notes: ' . $validated['delivery_notes'];
            }

            $package->updateStatus('DELIVERED', $user, $statusMessage);

            // Traiter les transactions financières
            $this->processDeliveryTransactions($package, $user);

            DB::commit();

            return redirect()->route('deliverer.packages.assigned')
                ->with('success', "Colis #{$package->package_code} livré avec succès!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la livraison: ' . $e->getMessage());
        }
    }

    /**
     * Marquer comme retourné
     */
    public function return(Package $package, Request $request)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        if (!in_array($package->status, ['PICKED_UP', 'DELIVERY_FAILED'])) {
            return back()->with('error', 'Ce colis ne peut pas être retourné dans son état actuel.');
        }

        $validated = $request->validate([
            'return_reason' => 'required|string|in:RECIPIENT_REFUSED,ADDRESS_NOT_FOUND,RECIPIENT_UNAVAILABLE,DAMAGED_PACKAGE,OTHER',
            'return_notes' => 'required|string|max:500',
            'return_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'returned_at' => now(),
                'return_reason' => $validated['return_reason']
            ];
            
            if ($request->hasFile('return_photo')) {
                $path = $request->file('return_photo')->store('returns/' . $package->id, 'public');
                $updateData['return_photo_path'] = $path;
            }

            $package->update($updateData);

            $statusMessage = 'Colis retourné - Raison: ' . $validated['return_reason'];
            if ($validated['return_notes']) {
                $statusMessage .= ' - Notes: ' . $validated['return_notes'];
            }

            $package->updateStatus('RETURNED', $user, $statusMessage);

            // Traiter les transactions financières pour le retour
            $this->processReturnTransactions($package, $user);

            DB::commit();

            return redirect()->route('deliverer.packages.assigned')
                ->with('success', "Colis #{$package->package_code} marqué comme retourné.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors du retour: ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer une tentative de livraison
     */
    public function recordAttempt(Package $package, Request $request)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        if ($package->status !== 'PICKED_UP') {
            return back()->with('error', 'Impossible d\'enregistrer une tentative pour ce colis.');
        }

        $validated = $request->validate([
            'attempt_reason' => 'required|string|in:RECIPIENT_UNAVAILABLE,ADDRESS_NOT_FOUND,RECIPIENT_REFUSED,OTHER',
            'attempt_notes' => 'required|string|max:500',
            'next_attempt_date' => 'nullable|date|after:now'
        ]);

        try {
            DB::beginTransaction();

            // Créer la tentative de livraison
            DeliveryAttempt::create([
                'package_id' => $package->id,
                'deliverer_id' => $user->id,
                'attempt_date' => now(),
                'reason' => $validated['attempt_reason'],
                'notes' => $validated['attempt_notes'],
                'next_attempt_planned' => $validated['next_attempt_date']
            ]);

            // Mettre à jour le statut si nécessaire
            $attemptCount = $package->deliveryAttempts()->count();
            $maxAttempts = 3; // Configurable

            if ($attemptCount >= $maxAttempts) {
                $package->updateStatus('DELIVERY_FAILED', $user, 
                    "Échec de livraison après {$attemptCount} tentatives");
            } else {
                $package->updateStatus('DELIVERY_ATTEMPT', $user, 
                    "Tentative de livraison #{$attemptCount} - " . $validated['attempt_reason']);
            }

            DB::commit();

            $message = "Tentative de livraison enregistrée.";
            if ($attemptCount >= $maxAttempts) {
                $message .= " Le colis est maintenant marqué comme échec de livraison.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    /**
     * Actions groupées - Accepter plusieurs colis
     */
    public function bulkAccept(Request $request)
    {
        $validated = $request->validate([
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'exists:packages,id'
        ]);

        $user = Auth::user();
        $accepted = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($validated['package_ids'] as $packageId) {
                $package = Package::find($packageId);
                
                if ($package->status === 'AVAILABLE') {
                    $package->update([
                        'assigned_deliverer_id' => $user->id,
                        'assigned_at' => now()
                    ]);
                    
                    $package->updateStatus('ACCEPTED', $user, 'Colis accepté par le livreur (action groupée)');
                    $accepted++;
                } else {
                    $errors[] = "Colis #{$package->package_code} n'est plus disponible";
                }
            }

            DB::commit();

            $message = "{$accepted} colis accepté(s) avec succès.";
            if (!empty($errors)) {
                $message .= " Erreurs: " . implode(', ', $errors);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'acceptation groupée: ' . $e->getMessage());
        }
    }

    /**
     * API - Nombre de colis disponibles
     */
    public function apiAvailableCount()
    {
        $user = Auth::user();
        
        $query = Package::where('status', 'AVAILABLE');
        
        if ($user->delivererProfile && $user->delivererProfile->assigned_delegations) {
            $query->whereIn('delegation_from', $user->delivererProfile->assigned_delegations);
        }
        
        return response()->json(['count' => $query->count()]);
    }

    /**
     * API - Nombre de colis assignés
     */
    public function apiAssignedCount()
    {
        $count = Package::where('assigned_deliverer_id', Auth::id())
                       ->whereNotIn('status', ['DELIVERED', 'RETURNED', 'CANCELLED'])
                       ->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Traiter les transactions financières pour une livraison
     */
    private function processDeliveryTransactions(Package $package, $deliverer)
    {
        $codAmount = $package->cod_collected_amount;
        $deliveryFee = $package->delivery_fee;
        
        // Créditer le livreur avec le montant COD
        if ($codAmount > 0) {
            $this->financialService->processTransaction([
                'user_id' => $deliverer->id,
                'type' => 'COD_COLLECTION',
                'amount' => $codAmount,
                'package_id' => $package->id,
                'description' => "Collecte COD colis #{$package->package_code}",
                'metadata' => [
                    'package_code' => $package->package_code,
                    'cod_amount' => $codAmount
                ]
            ]);
        }

        // Commission du livreur (à calculer selon les règles business)
        $commission = $this->calculateDelivererCommission($package);
        if ($commission > 0) {
            $this->financialService->processTransaction([
                'user_id' => $deliverer->id,
                'type' => 'DELIVERY_COMMISSION',
                'amount' => $commission,
                'package_id' => $package->id,
                'description' => "Commission livraison #{$package->package_code}",
                'metadata' => [
                    'package_code' => $package->package_code,
                    'commission_rate' => $commission / $deliveryFee
                ]
            ]);
        }
    }

    /**
     * Traiter les transactions financières pour un retour
     */
    private function processReturnTransactions(Package $package, $deliverer)
    {
        // Commission de retour (généralement plus faible)
        $returnCommission = $this->calculateReturnCommission($package);
        if ($returnCommission > 0) {
            $this->financialService->processTransaction([
                'user_id' => $deliverer->id,
                'type' => 'RETURN_COMMISSION',
                'amount' => $returnCommission,
                'package_id' => $package->id,
                'description' => "Commission retour #{$package->package_code}",
                'metadata' => [
                    'package_code' => $package->package_code,
                    'return_reason' => $package->return_reason
                ]
            ]);
        }
    }

    /**
     * Calculer la commission du livreur pour une livraison
     */
    private function calculateDelivererCommission(Package $package)
    {
        // Logique de calcul de commission - exemple: 30% des frais de livraison
        return $package->delivery_fee * 0.30;
    }

    /**
     * Calculer la commission du livreur pour un retour
     */
    private function calculateReturnCommission(Package $package)
    {
        // Logique de calcul de commission pour retour - exemple: 20% des frais de retour
        return $package->return_fee * 0.20;
    }
}