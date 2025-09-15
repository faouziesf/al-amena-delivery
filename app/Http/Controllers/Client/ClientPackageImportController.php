<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ImportBatch;
use App\Models\Delegation;
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ClientPackageImportController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Afficher le formulaire d'import CSV
     */
    public function showImportForm()
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->load(['wallet', 'clientProfile']);

        if (!$user->isActive() || !$user->clientProfile) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Votre compte doit être validé avant d\'importer des colis.');
        }

        // Récupérer les derniers imports
        $recentImports = ImportBatch::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('client.packages.import-csv', compact('user', 'recentImports'));
    }

    /**
     * Traiter l'import CSV
     */
    public function processImportCsv(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // 5MB max
            'has_header' => 'boolean',
            'encoding' => 'in:UTF-8,ISO-8859-1',
            'delimiter' => 'in:comma,semicolon,tab'
        ]);

        try {
            $file = $request->file('csv_file');
            $filename = $file->getClientOriginalName();
            $filePath = $file->store('imports/csv', 'local');

            // Analyser le fichier CSV
            $csvData = $this->parseCsvFile($filePath, $validated);
            
            if (empty($csvData['rows'])) {
                Storage::delete($filePath);
                return back()->with('error', 'Le fichier CSV est vide ou mal formaté.');
            }

            // Valider les données
            $validationResult = $this->validateCsvData($csvData['rows'], $user);
            
            if ($validationResult['total_errors'] > 0) {
                Storage::delete($filePath);
                return back()
                    ->with('error', "Erreurs dans le fichier CSV : {$validationResult['total_errors']} lignes invalides")
                    ->with('csv_errors', $validationResult['errors']);
            }

            // Créer le batch d'import
            $batch = ImportBatch::createForUser(
                $user->id, 
                $filename, 
                count($csvData['rows'])
            );
            $batch->update(['file_path' => $filePath]);

            // Traiter l'import en arrière-plan ou directement
            $this->processImportBatch($batch, $csvData['rows'], $user);

            return redirect()->route('client.packages.import.status', $batch->id)
                ->with('success', "Import lancé avec succès. Batch #{$batch->batch_code}");

        } catch (\Exception $e) {
            if (isset($filePath)) {
                Storage::delete($filePath);
            }
            
            return back()
                ->with('error', "Erreur lors de l'import : " . $e->getMessage());
        }
    }

    /**
     * Afficher le statut d'un import
     */
    public function showImportStatus($batchId)
    {
        $user = Auth::user();
        $batch = ImportBatch::where('user_id', $user->id)->findOrFail($batchId);
        
        $packages = Package::where('import_batch_id', $batch->id)
            ->with(['delegationFrom', 'delegationTo'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('client.packages.import-status', compact('batch', 'packages'));
    }

    /**
     * Télécharger le template CSV
     */
    public function downloadTemplate()
    {
        $headers = [
            'Nom Fournisseur',
            'Téléphone Fournisseur', 
            'Délégation Pickup (nom)',
            'Adresse Pickup',
            'Nom Destinataire',
            'Téléphone Destinataire',
            'Délégation Destination (nom)',
            'Adresse Destination',
            'Description Contenu',
            'Montant COD',
            'Poids (optionnel)',
            'Valeur Déclarée (optionnel)',
            'Notes (optionnel)',
            'Fragile (oui/non)',
            'Signature Requise (oui/non)'
        ];

        $exampleRow = [
            'Fournisseur Test',
            '12345678',
            'Tunis',
            '123 Rue Exemple, Tunis',
            'Client Test',
            '87654321',
            'Sfax',
            '456 Avenue Test, Sfax',
            'Vêtements',
            '50.000',
            '2.500',
            '100.000',
            'Livrer avant 17h',
            'non',
            'oui'
        ];

        $content = implode(';', $headers) . "\n";
        $content .= implode(';', $exampleRow) . "\n";

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="template_import_colis.csv"');
    }

    /**
     * Parser le fichier CSV
     */
    private function parseCsvFile($filePath, $options)
    {
        $fullPath = Storage::path($filePath);
        $content = file_get_contents($fullPath);
        
        // Gérer l'encodage
        if ($options['encoding'] === 'ISO-8859-1') {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }

        // Déterminer le délimiteur
        $delimiter = match($options['delimiter']) {
            'semicolon' => ';',
            'tab' => "\t",
            default => ','
        };

        $lines = array_filter(explode("\n", $content));
        $rows = [];
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $data = str_getcsv($line, $delimiter);
            
            // Ignorer l'en-tête si spécifié
            if ($index === 0 && $options['has_header']) {
                continue;
            }
            
            $rows[] = array_map('trim', $data);
        }

        return ['rows' => $rows];
    }

    /**
     * Valider les données CSV
     */
    private function validateCsvData($rows, $user)
    {
        $errors = [];
        $delegations = Delegation::where('active', true)->pluck('id', 'name')->toArray();
        $totalEscrowNeeded = 0;
        
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;
            $rowErrors = [];
            
            // Vérifier le nombre de colonnes minimum
            if (count($row) < 10) {
                $rowErrors[] = "Nombre de colonnes insuffisant (minimum 10)";
            } else {
                // Validation des champs obligatoires
                if (empty($row[0])) $rowErrors[] = "Nom fournisseur manquant";
                if (empty($row[1])) $rowErrors[] = "Téléphone fournisseur manquant";
                if (empty($row[2])) $rowErrors[] = "Délégation pickup manquante";
                if (empty($row[3])) $rowErrors[] = "Adresse pickup manquante";
                if (empty($row[4])) $rowErrors[] = "Nom destinataire manquant";
                if (empty($row[5])) $rowErrors[] = "Téléphone destinataire manquant";
                if (empty($row[6])) $rowErrors[] = "Délégation destination manquante";
                if (empty($row[7])) $rowErrors[] = "Adresse destination manquante";
                if (empty($row[8])) $rowErrors[] = "Description contenu manquante";
                if (empty($row[9])) $rowErrors[] = "Montant COD manquant";

                // Validation des délégations
                if (!empty($row[2]) && !isset($delegations[$row[2]])) {
                    $rowErrors[] = "Délégation pickup '{$row[2]}' non trouvée";
                }
                if (!empty($row[6]) && !isset($delegations[$row[6]])) {
                    $rowErrors[] = "Délégation destination '{$row[6]}' non trouvée";
                }
                if (!empty($row[2]) && !empty($row[6]) && $row[2] === $row[6]) {
                    $rowErrors[] = "Délégation pickup et destination identiques";
                }

                // Validation du montant COD
                if (!empty($row[9])) {
                    $codAmount = str_replace(',', '.', $row[9]);
                    if (!is_numeric($codAmount) || $codAmount < 0 || $codAmount > 9999.999) {
                        $rowErrors[] = "Montant COD invalide (doit être entre 0 et 9999.999)";
                    } else {
                        // Calculer l'escrow nécessaire
                        $deliveryFee = $user->clientProfile->offer_delivery_price;
                        $returnFee = $user->clientProfile->offer_return_price;
                        $escrow = $codAmount >= $deliveryFee ? $returnFee : $deliveryFee;
                        $totalEscrowNeeded += $escrow;
                    }
                }

                // Validation du poids (optionnel)
                if (!empty($row[10])) {
                    $weight = str_replace(',', '.', $row[10]);
                    if (!is_numeric($weight) || $weight < 0 || $weight > 999.999) {
                        $rowErrors[] = "Poids invalide (doit être entre 0 et 999.999)";
                    }
                }

                // Validation de la valeur déclarée (optionnel)
                if (!empty($row[11])) {
                    $value = str_replace(',', '.', $row[11]);
                    if (!is_numeric($value) || $value < 0 || $value > 99999.999) {
                        $rowErrors[] = "Valeur déclarée invalide (doit être entre 0 et 99999.999)";
                    }
                }
            }
            
            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'errors' => $rowErrors
                ];
            }
        }

        // Vérifier le solde suffisant
        if ($totalEscrowNeeded > $user->wallet->balance) {
            $errors[] = [
                'row' => 'Général',
                'errors' => ["Solde insuffisant. Requis: {$totalEscrowNeeded} DT, Disponible: {$user->wallet->balance} DT"]
            ];
        }

        return [
            'errors' => $errors,
            'total_errors' => count($errors),
            'total_escrow_needed' => $totalEscrowNeeded
        ];
    }

    /**
     * Traiter le batch d'import
     */
    private function processImportBatch($batch, $rows, $user)
    {
        DB::beginTransaction();
        
        try {
            $batch->markAsStarted();
            $delegations = Delegation::where('active', true)->pluck('id', 'name')->toArray();
            
            foreach ($rows as $index => $row) {
                try {
                    $packageData = $this->preparePackageData($row, $delegations, $user);
                    $package = $this->createPackageFromImport($packageData, $user, $batch->id);
                    
                    $batch->incrementProcessed(true);
                    
                } catch (\Exception $e) {
                    $batch->addError($index + 1, $e->getMessage(), $row);
                    $batch->incrementProcessed(false);
                }
            }
            
            $summary = [
                'total_processed' => $batch->processed_rows,
                'successful' => $batch->successful_rows,
                'failed' => $batch->failed_rows,
                'success_rate' => $batch->getSuccessRateAttribute()
            ];
            
            $batch->markAsCompleted($summary);
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $batch->markAsFailed([$e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Préparer les données du package à partir de la ligne CSV
     */
    private function preparePackageData($row, $delegations, $user)
    {
        return [
            'supplier_name' => $row[0],
            'supplier_phone' => $row[1],
            'pickup_delegation_id' => $delegations[$row[2]],
            'pickup_address' => $row[3],
            'recipient_name' => $row[4],
            'recipient_phone' => $row[5],
            'delegation_to' => $delegations[$row[6]],
            'recipient_address' => $row[7],
            'content_description' => $row[8],
            'cod_amount' => (float) str_replace(',', '.', $row[9]),
            'package_weight' => !empty($row[10]) ? (float) str_replace(',', '.', $row[10]) : null,
            'package_value' => !empty($row[11]) ? (float) str_replace(',', '.', $row[11]) : null,
            'notes' => $row[12] ?? '',
            'is_fragile' => !empty($row[13]) && strtolower($row[13]) === 'oui',
            'requires_signature' => !empty($row[14]) && strtolower($row[14]) === 'oui',
        ];
    }

    /**
     * Créer un package à partir des données d'import
     */
    private function createPackageFromImport($validated, $user, $batchId)
    {
        $user->ensureWallet();
        $clientProfile = $user->clientProfile;
        
        $packageData = [
            'sender_id' => $user->id,
            'sender_data' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'address' => $user->address
            ],
            'supplier_data' => [
                'name' => $validated['supplier_name'],
                'phone' => $validated['supplier_phone'],
            ],
            'delegation_from' => $validated['pickup_delegation_id'],
            'pickup_delegation_id' => $validated['pickup_delegation_id'],
            'pickup_address' => $validated['pickup_address'],
            'pickup_phone' => $validated['supplier_phone'],
            'delegation_to' => $validated['delegation_to'],
            'recipient_data' => [
                'name' => $validated['recipient_name'],
                'phone' => $validated['recipient_phone'],
                'address' => $validated['recipient_address']
            ],
            'content_description' => $validated['content_description'],
            'notes' => $validated['notes'],
            'cod_amount' => $validated['cod_amount'],
            'delivery_fee' => $clientProfile->offer_delivery_price,
            'return_fee' => $clientProfile->offer_return_price,
            'status' => 'CREATED',
            'import_batch_id' => $batchId,
            'package_weight' => $validated['package_weight'],
            'package_value' => $validated['package_value'],
            'is_fragile' => $validated['is_fragile'],
            'requires_signature' => $validated['requires_signature'],
        ];

        $package = new Package($packageData);

        // Calcul et déduction de l'escrow
        $escrowAmount = $validated['cod_amount'] >= $clientProfile->offer_delivery_price 
            ? $clientProfile->offer_return_price 
            : $clientProfile->offer_delivery_price;
        
        if (!$user->wallet->hasSufficientBalance($escrowAmount)) {
            throw new \Exception("Solde insuffisant pour le colis. Montant requis: {$escrowAmount} DT");
        }

        $package->amount_in_escrow = $escrowAmount;
        $package->save();

        // Transaction financière
        $this->financialService->processTransaction([
            'user_id' => $user->id,
            'type' => 'PACKAGE_CREATION_DEBIT',
            'amount' => -$escrowAmount,
            'package_id' => $package->id,
            'description' => "Import CSV - Colis #{$package->package_code}",
            'metadata' => [
                'package_code' => $package->package_code,
                'import_type' => 'csv_batch',
                'batch_id' => $batchId
            ]
        ]);

        $package->updateStatus('AVAILABLE', $user, 'Colis créé par import CSV et disponible pour pickup');

        return $package;
    }
}