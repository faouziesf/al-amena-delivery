<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\FixedCharge;
use App\Models\DepreciableAsset;
use App\Services\ActionLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FinancialManagementController extends Controller
{
    protected $actionLogService;

    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
    }

    // ==================== CHARGES FIXES ====================

    /**
     * Liste des charges fixes
     */
    public function indexCharges()
    {
        $charges = FixedCharge::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_monthly' => FixedCharge::active()->sum('monthly_equivalent'),
            'total_charges' => FixedCharge::count(),
            'active_charges' => FixedCharge::active()->count(),
        ];

        return view('supervisor.financial.charges.index', compact('charges', 'stats'));
    }

    /**
     * Formulaire de création de charge fixe
     */
    public function createCharge()
    {
        return view('supervisor.financial.charges.create');
    }

    /**
     * Enregistre une nouvelle charge fixe
     */
    public function storeCharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'periodicity' => 'required|in:DAILY,WEEKLY,MONTHLY,YEARLY',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $charge = FixedCharge::create([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'periodicity' => $request->periodicity,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        $this->actionLogService->logCreated('FixedCharge', $charge->id, [
            'name' => $charge->name,
            'amount' => $charge->amount,
            'periodicity' => $charge->periodicity,
        ]);

        return redirect()->route('supervisor.financial.charges.index')
            ->with('success', 'Charge fixe créée avec succès.');
    }

    /**
     * Affiche une charge fixe
     */
    public function showCharge(FixedCharge $charge)
    {
        $charge->load('creator');
        return view('supervisor.financial.charges.show', compact('charge'));
    }

    /**
     * Formulaire d'édition de charge fixe
     */
    public function editCharge(FixedCharge $charge)
    {
        return view('supervisor.financial.charges.edit', compact('charge'));
    }

    /**
     * Met à jour une charge fixe
     */
    public function updateCharge(Request $request, FixedCharge $charge)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'periodicity' => 'required|in:DAILY,WEEKLY,MONTHLY,YEARLY',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldData = $charge->only(['name', 'amount', 'periodicity', 'is_active']);

        $charge->update([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'periodicity' => $request->periodicity,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $newData = $charge->only(['name', 'amount', 'periodicity', 'is_active']);

        $this->actionLogService->logUpdated('FixedCharge', $charge->id, $oldData, $newData);

        return redirect()->route('supervisor.financial.charges.index')
            ->with('success', 'Charge fixe mise à jour avec succès.');
    }

    /**
     * Supprime une charge fixe
     */
    public function destroyCharge(FixedCharge $charge)
    {
        $chargeData = $charge->only(['name', 'amount', 'periodicity']);
        $charge->delete();

        $this->actionLogService->logDeleted('FixedCharge', $charge->id, $chargeData);

        return redirect()->route('supervisor.financial.charges.index')
            ->with('success', 'Charge fixe supprimée avec succès.');
    }

    // ==================== ACTIFS AMORTISSABLES ====================

    /**
     * Liste des actifs amortissables
     */
    public function indexAssets()
    {
        $assets = DepreciableAsset::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_monthly' => DepreciableAsset::active()->sum('monthly_cost'),
            'total_assets' => DepreciableAsset::count(),
            'active_assets' => DepreciableAsset::active()->count(),
            'total_purchase_value' => DepreciableAsset::active()->sum('purchase_cost'),
        ];

        return view('supervisor.financial.assets.index', compact('assets', 'stats'));
    }

    /**
     * Formulaire de création d'actif amortissable
     */
    public function createAsset()
    {
        return view('supervisor.financial.assets.create');
    }

    /**
     * Enregistre un nouvel actif amortissable
     */
    public function storeAsset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'purchase_cost' => 'required|numeric|min:0',
            'depreciation_years' => 'required|integer|min:1|max:50',
            'purchase_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $asset = DepreciableAsset::create([
            'name' => $request->name,
            'description' => $request->description,
            'purchase_cost' => $request->purchase_cost,
            'depreciation_years' => $request->depreciation_years,
            'purchase_date' => $request->purchase_date,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        $this->actionLogService->logCreated('DepreciableAsset', $asset->id, [
            'name' => $asset->name,
            'purchase_cost' => $asset->purchase_cost,
            'depreciation_years' => $asset->depreciation_years,
        ]);

        return redirect()->route('supervisor.financial.assets.index')
            ->with('success', 'Actif amortissable créé avec succès.');
    }

    /**
     * Affiche un actif amortissable
     */
    public function showAsset(DepreciableAsset $asset)
    {
        $asset->load('creator');
        return view('supervisor.financial.assets.show', compact('asset'));
    }

    /**
     * Formulaire d'édition d'actif amortissable
     */
    public function editAsset(DepreciableAsset $asset)
    {
        return view('supervisor.financial.assets.edit', compact('asset'));
    }

    /**
     * Met à jour un actif amortissable
     */
    public function updateAsset(Request $request, DepreciableAsset $asset)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'purchase_cost' => 'required|numeric|min:0',
            'depreciation_years' => 'required|integer|min:1|max:50',
            'purchase_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldData = $asset->only(['name', 'purchase_cost', 'depreciation_years', 'purchase_date', 'is_active']);

        $asset->update([
            'name' => $request->name,
            'description' => $request->description,
            'purchase_cost' => $request->purchase_cost,
            'depreciation_years' => $request->depreciation_years,
            'purchase_date' => $request->purchase_date,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $newData = $asset->only(['name', 'purchase_cost', 'depreciation_years', 'purchase_date', 'is_active']);

        $this->actionLogService->logUpdated('DepreciableAsset', $asset->id, $oldData, $newData);

        return redirect()->route('supervisor.financial.assets.index')
            ->with('success', 'Actif amortissable mis à jour avec succès.');
    }

    /**
     * Supprime un actif amortissable
     */
    public function destroyAsset(DepreciableAsset $asset)
    {
        $assetData = $asset->only(['name', 'purchase_cost', 'depreciation_years']);
        $asset->delete();

        $this->actionLogService->logDeleted('DepreciableAsset', $asset->id, $assetData);

        return redirect()->route('supervisor.financial.assets.index')
            ->with('success', 'Actif amortissable supprimé avec succès.');
    }

    // ==================== IMPORT/EXPORT ====================

    /**
     * Exporte les charges fixes en CSV
     */
    public function exportCharges()
    {
        $charges = FixedCharge::all();

        $filename = 'charges_fixes_' . date('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        
        // BOM UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-têtes
        fputcsv($file, ['Nom', 'Description', 'Montant', 'Périodicité', 'Équivalent Mensuel', 'Actif']);

        foreach ($charges as $charge) {
            fputcsv($file, [
                $charge->name,
                $charge->description,
                $charge->amount,
                $charge->periodicity,
                $charge->monthly_equivalent,
                $charge->is_active ? 'Oui' : 'Non',
            ]);
        }

        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    /**
     * Template d'import pour les charges fixes
     */
    public function importChargesTemplate()
    {
        $filename = 'template_charges_fixes.csv';
        $filepath = storage_path('app/exports/' . $filename);

        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        
        // BOM UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-têtes
        fputcsv($file, ['Nom', 'Description', 'Montant', 'Périodicité (DAILY/WEEKLY/MONTHLY/YEARLY)', 'Actif (1/0)']);
        
        // Exemple
        fputcsv($file, ['Loyer mensuel', 'Loyer du local', '1500.000', 'MONTHLY', '1']);
        fputcsv($file, ['Électricité', 'Facture électricité', '300.000', 'MONTHLY', '1']);

        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    /**
     * Importe des charges fixes depuis un CSV
     */
    public function importCharges(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        
        $data = array_map('str_getcsv', file($path));
        $headers = array_shift($data); // Retirer les en-têtes

        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            if (count($row) < 5) {
                $errors[] = "Ligne " . ($index + 2) . ": Données incomplètes";
                continue;
            }

            try {
                FixedCharge::create([
                    'name' => $row[0],
                    'description' => $row[1],
                    'amount' => $row[2],
                    'periodicity' => $row[3],
                    'is_active' => $row[4] == '1',
                    'created_by' => auth()->id(),
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Ligne " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->route('supervisor.financial.charges.index')
                ->with('warning', "$imported charge(s) importée(s) avec " . count($errors) . " erreur(s).")
                ->with('errors', $errors);
        }

        return redirect()->route('supervisor.financial.charges.index')
            ->with('success', "$imported charge(s) fixe(s) importée(s) avec succès.");
    }
}
