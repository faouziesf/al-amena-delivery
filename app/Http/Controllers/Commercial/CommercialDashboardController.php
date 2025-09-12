<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Services\CommercialService;
use App\Models\User;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
use App\Models\Delegation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommercialDashboardController extends Controller
{
    protected $commercialService;

    public function __construct(CommercialService $commercialService)
    {
        $this->commercialService = $commercialService;
    }

    public function index()
    {
        $user = Auth::user();
        $stats = $this->commercialService->getDashboardStats();
        $complaintsStats = $this->commercialService->getComplaintsSummary();
        $recentActivity = $this->commercialService->getRecentActivity();

        return view('commercial.dashboard', compact('user', 'stats', 'complaintsStats', 'recentActivity'));
    }

    // ==================== GESTION CLIENTS ====================

    public function clients()
    {
        $clients = User::with(['clientProfile', 'wallet'])
            ->where('role', 'CLIENT')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('commercial.clients.index', compact('clients'));
    }

    public function createClient()
    {
        $delegations = Delegation::active()->orderBy('name')->get();
        return view('commercial.clients.create', compact('delegations'));
    }

    public function storeClient(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'required|string',
            'address' => 'required|string',
            'delivery_price' => 'required|numeric|min:0',
            'return_price' => 'required|numeric|min:0',
            'shop_name' => 'nullable|string|max:255',
            'fiscal_number' => 'nullable|string|max:255',
            'business_sector' => 'nullable|string|max:255',
        ]);

        try {
            $client = $this->commercialService->createClientAccount(
                $request->all(),
                Auth::user()
            );

            return redirect()->route('commercial.clients')
                ->with('success', 'Compte client créé avec succès. ID: ' . $client->id);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function validateClient(User $client, Request $request)
    {
        if ($client->account_status === 'ACTIVE') {
            return back()->with('info', 'Ce compte est déjà validé.');
        }

        $this->commercialService->validateClientAccount($client, Auth::user(), [
            'validation_notes' => $request->input('notes')
        ]);

        return back()->with('success', 'Compte client validé avec succès.');
    }

    public function manageClientWallet(User $client)
    {
        $wallet = $client->wallet;
        $recentTransactions = $client->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('commercial.clients.wallet', compact('client', 'wallet', 'recentTransactions'));
    }

    public function updateClientWallet(User $client, Request $request)
    {
        $request->validate([
            'action' => 'required|in:add,deduct',
            'amount' => 'required|numeric|min:0.001',
            'description' => 'required|string|max:255',
        ]);

        try {
            if ($request->action === 'add') {
                $result = $this->commercialService->addFundsToWallet(
                    $client,
                    $request->amount,
                    $request->description,
                    Auth::user()
                );
            } else {
                $result = $this->commercialService->deductFundsFromWallet(
                    $client,
                    $request->amount,
                    $request->description,
                    Auth::user()
                );
            }

            return back()->with('success', 'Wallet mis à jour avec succès. Nouveau solde: ' . $result['wallet_balance'] . ' DT');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

    // ==================== GESTION RÉCLAMATIONS ====================

    public function complaints()
    {
        $pendingComplaints = $this->commercialService->getPendingComplaints(Auth::user());
        $complaintsStats = $this->commercialService->getComplaintsSummary();

        return view('commercial.complaints.index', compact('pendingComplaints', 'complaintsStats'));
    }

    public function showComplaint(Complaint $complaint)
    {
        $complaint->load(['package', 'client', 'assignedCommercial']);
        return view('commercial.complaints.show', compact('complaint'));
    }

    public function processComplaint(Complaint $complaint, Request $request)
    {
        $request->validate([
            'action' => 'required|in:resolve_with_cod_change,resolve_with_reschedule,resolve_with_return,resolve_simple,reject',
            'new_cod_amount' => 'required_if:action,resolve_with_cod_change|nullable|numeric|min:0',
            'reschedule_date' => 'required_if:action,resolve_with_reschedule|nullable|date|after:today',
            'resolution_notes' => 'required_unless:action,resolve_with_cod_change|string',
            'rejection_reason' => 'required_if:action,reject|string',
        ]);

        try {
            $this->commercialService->processComplaint(
                $complaint,
                $request->action,
                $request->all(),
                Auth::user()
            );

            return redirect()->route('commercial.complaints')
                ->with('success', 'Réclamation traitée avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du traitement: ' . $e->getMessage()]);
        }
    }

    public function modifyPackageCod(Package $package, Request $request)
    {
        $request->validate([
            'new_cod_amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'emergency' => 'boolean',
        ]);

        try {
            $modification = $this->commercialService->modifyCodAmount(
                $package,
                $request->new_cod_amount,
                $request->reason,
                Auth::user(),
                null,
                $request->boolean('emergency')
            );

            return back()->with('success', 'COD modifié avec succès. Ancien: ' . $modification->old_amount . ' DT, Nouveau: ' . $modification->new_amount . ' DT');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la modification: ' . $e->getMessage()]);
        }
    }

    // ==================== GESTION RETRAITS ====================

    public function withdrawals()
    {
        $pendingWithdrawals = $this->commercialService->getPendingWithdrawals();
        return view('commercial.withdrawals.index', compact('pendingWithdrawals'));
    }

    public function processWithdrawal(WithdrawalRequest $withdrawal, Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve_bank_transfer,approve_cash_delivery,reject',
            'notes' => 'nullable|string',
            'rejection_reason' => 'required_if:action,reject|string',
        ]);

        try {
            $this->commercialService->processWithdrawalRequest(
                $withdrawal,
                $request->action,
                $request->all(),
                Auth::user()
            );

            return back()->with('success', 'Demande de retrait traitée avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du traitement: ' . $e->getMessage()]);
        }
    }

    public function assignWithdrawalToDeliverer(WithdrawalRequest $withdrawal, Request $request)
    {
        $request->validate([
            'deliverer_id' => 'required|exists:users,id',
        ]);

        try {
            $deliverer = User::findOrFail($request->deliverer_id);
            $this->commercialService->assignWithdrawalToDeliverer($withdrawal, $deliverer);

            return back()->with('success', 'Retrait assigné au livreur ' . $deliverer->name . ' avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'assignation: ' . $e->getMessage()]);
        }
    }

    // ==================== GESTION LIVREURS ====================

    public function deliverers()
    {
        $deliverers = $this->commercialService->getDeliverersWithWallets();
        return view('commercial.deliverers.index', compact('deliverers'));
    }

    public function emptyDelivererWallet(User $deliverer, Request $request)
    {
        $request->validate([
            'physical_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $emptying = $this->commercialService->emptyDelivererWallet(
                $deliverer,
                Auth::user(),
                $request->physical_amount
            );

            $message = 'Wallet du livreur ' . $deliverer->name . ' vidé avec succès.';
            if ($emptying->discrepancy_amount != 0) {
                $message .= ' Différence détectée: ' . $emptying->discrepancy_amount . ' DT';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du vidage: ' . $e->getMessage()]);
        }
    }

    // ==================== SUIVI GLOBAL COLIS ====================

    public function packages()
    {
        $packages = Package::with(['sender', 'assignedDeliverer', 'delegationFrom', 'delegationTo'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        $packageStats = [
            'total' => Package::count(),
            'in_progress' => Package::inProgress()->count(),
            'delivered_today' => Package::delivered()->whereDate('updated_at', today())->count(),
            'with_complaints' => Package::withPendingComplaints()->count(),
        ];

        return view('commercial.packages.index', compact('packages', 'packageStats'));
    }

    public function showPackage(Package $package)
    {
        $package->load([
            'sender', 'assignedDeliverer', 'delegationFrom', 'delegationTo',
            'complaints', 'statusHistory', 'codModifications'
        ]);

        return view('commercial.packages.show', compact('package'));
    }

    // ==================== API ENDPOINTS MANQUANTS ====================

    public function api_getDashboardStats()
    {
        try {
            $stats = $this->commercialService->getDashboardStats();
            return response()->json($stats);
        } catch (\Exception $e) {
            \Log::error('Erreur API dashboard stats:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors du chargement des statistiques'], 500);
        }
    }

    public function api_getComplaintsCount()
    {
        try {
            $count = \App\Models\Complaint::pending()->count();
            $urgent = \App\Models\Complaint::pending()->where('priority', 'URGENT')->count();
            
            return response()->json([
                'count' => $count,
                'urgent' => $urgent
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur API complaints count:', ['error' => $e->getMessage()]);
            return response()->json(['count' => 0, 'urgent' => 0]);
        }
    }

    public function api_getWithdrawalsCount()
    {
        try {
            $count = \App\Models\WithdrawalRequest::pending()->count();
            
            return response()->json([
                'count' => $count
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur API withdrawals count:', ['error' => $e->getMessage()]);
            return response()->json(['count' => 0]);
        }
    }

    public function api_searchClients(Request $request)
    {
        try {
            $search = $request->input('q', '');
            
            if (strlen($search) < 2) {
                return response()->json([]);
            }
            
            $clients = \App\Models\User::where('role', 'CLIENT')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                ->limit(10)
                ->get(['id', 'name', 'email', 'phone']);

            return response()->json($clients);
        } catch (\Exception $e) {
            \Log::error('Erreur API search clients:', ['error' => $e->getMessage()]);
            return response()->json([]);
        }
    }

    public function api_searchDeliverers(Request $request)
    {
        try {
            $search = $request->input('q', '');
            
            if (strlen($search) < 2) {
                return response()->json([]);
            }
            
            $deliverers = \App\Models\User::where('role', 'DELIVERER')
                ->where('account_status', 'ACTIVE')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                ->limit(10)
                ->get(['id', 'name', 'phone']);

            return response()->json($deliverers);
        } catch (\Exception $e) {
            \Log::error('Erreur API search deliverers:', ['error' => $e->getMessage()]);
            return response()->json([]);
        }
    }

}