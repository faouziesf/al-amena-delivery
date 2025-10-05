<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\TopupRequest;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TopupRequestController extends Controller
{
    /**
     * Afficher la liste des demandes de recharge
     */
    public function index(Request $request)
    {
        $query = TopupRequest::with(['user'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $topupRequests = $query->paginate(20);

        // Statistiques
        $stats = [
            'pending' => TopupRequest::where('status', 'pending')->count(),
            'approved' => TopupRequest::where('status', 'approved')->whereDate('created_at', today())->count(),
            'rejected' => TopupRequest::where('status', 'rejected')->whereDate('created_at', today())->count(),
            'total_amount_pending' => TopupRequest::where('status', 'pending')->sum('amount'),
            'total_amount_today' => TopupRequest::where('status', 'approved')->whereDate('created_at', today())->sum('amount'),
        ];

        return view('commercial.topup-requests.index', compact('topupRequests', 'stats'));
    }

    /**
     * Afficher les détails d'une demande
     */
    public function show($id)
    {
        $topupRequest = TopupRequest::with(['user', 'processedBy'])->findOrFail($id);
        
        // Historique des transactions du client
        $recentTransactions = Transaction::where('user_id', $topupRequest->user_id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('commercial.topup-requests.show', compact('topupRequest', 'recentTransactions'));
    }

    /**
     * Approuver une demande de recharge
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $topupRequest = TopupRequest::where('status', 'pending')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Mettre à jour la demande
            $topupRequest->status = 'approved';
            $topupRequest->processed_by = Auth::id();
            $topupRequest->processed_at = now();
            $topupRequest->notes = $request->notes;
            $topupRequest->save();

            // Créditer le compte du client
            $user = $topupRequest->user;
            $user->balance += $topupRequest->amount;
            $user->save();

            // Créer une transaction
            Transaction::create([
                'user_id' => $user->id,
                'transaction_id' => 'TOP-' . strtoupper(uniqid()),
                'amount' => $topupRequest->amount,
                'type' => 'credit',
                'description' => 'Recharge approuvée - Ref: ' . $topupRequest->reference,
                'status' => 'completed',
                'balance_after' => $user->balance,
            ]);

            DB::commit();

            // Notification au client (à implémenter)
            // event(new TopupApproved($topupRequest));

            return redirect()
                ->route('commercial.topup-requests.index')
                ->with('success', 'Demande de recharge approuvée avec succès. Le compte du client a été crédité de ' . number_format($topupRequest->amount, 2) . ' DT');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'approbation: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une demande de recharge
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $topupRequest = TopupRequest::where('status', 'pending')->findOrFail($id);

        $topupRequest->status = 'rejected';
        $topupRequest->processed_by = Auth::id();
        $topupRequest->processed_at = now();
        $topupRequest->notes = $request->rejection_reason;
        $topupRequest->save();

        // Notification au client (à implémenter)
        // event(new TopupRejected($topupRequest));

        return redirect()
            ->route('commercial.topup-requests.index')
            ->with('success', 'Demande de recharge rejetée');
    }

    /**
     * Exporter les demandes en CSV
     */
    public function export(Request $request)
    {
        $query = TopupRequest::with(['user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $topupRequests = $query->get();

        $filename = 'demandes_recharge_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($topupRequests) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes
            fputcsv($file, [
                'Référence',
                'Client',
                'Email',
                'Montant (DT)',
                'Méthode',
                'Statut',
                'Date demande',
                'Date traitement',
                'Traité par',
                'Notes'
            ], ';');

            // Données
            foreach ($topupRequests as $request) {
                fputcsv($file, [
                    $request->reference,
                    $request->user->name ?? 'N/A',
                    $request->user->email ?? 'N/A',
                    number_format($request->amount, 2, ',', ' '),
                    $request->payment_method,
                    $request->status,
                    $request->created_at->format('d/m/Y H:i'),
                    $request->processed_at ? $request->processed_at->format('d/m/Y H:i') : 'N/A',
                    $request->processedBy->name ?? 'N/A',
                    $request->notes ?? ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
