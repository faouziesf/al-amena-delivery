<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\WithdrawalRequest;
use App\Models\TopupRequest;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelivererReceiptController extends Controller
{
    /**
     * Reçu de livraison de colis
     */
    public function packageReceipt(Package $package)
    {
        if ($package->assigned_deliverer_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à voir ce reçu.');
        }

        if (!in_array($package->status, ['DELIVERED', 'PAID'])) {
            abort(404, 'Aucun reçu disponible pour ce colis.');
        }

        // Récupérer la transaction liée
        $transaction = FinancialTransaction::where('package_id', $package->id)
            ->where('type', 'COD_COLLECTION')
            ->first();

        return view('deliverer.receipts.package', compact('package', 'transaction'));
    }

    /**
     * Reçu de paiement COD groupé
     */
    public function paymentReceipt(Request $request)
    {
        $paymentId = $request->route('payment');

        // Si c'est un ID de groupe de paiements COD
        $packages = Package::where('assigned_deliverer_id', Auth::id())
            ->where('status', 'PAID')
            ->whereNotNull('delivered_at')
            ->whereDate('delivered_at', today())
            ->with(['sender', 'recipient'])
            ->get();

        if ($packages->isEmpty()) {
            abort(404, 'Aucun paiement COD trouvé pour aujourd\'hui.');
        }

        // Créer un objet payment virtuel
        $payment = (object) [
            'id' => $paymentId,
            'reference' => 'PAY-' . date('Ymd') . '-' . Auth::id(),
            'total_amount' => $packages->sum('cod_amount'),
            'created_at' => now(),
            'payment_method' => 'cash'
        ];

        return view('deliverer.receipts.payment', compact('payment', 'packages'));
    }

    /**
     * Reçu de recharge client
     */
    public function topupReceipt(TopupRequest $topup)
    {
        if ($topup->processed_by_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à voir ce reçu.');
        }

        return view('deliverer.receipts.topup', compact('topup'));
    }

    /**
     * Vérifier un reçu (API)
     */
    public function verifyReceipt($trackingNumber)
    {
        $package = Package::where('tracking_number', $trackingNumber)
            ->where('assigned_deliverer_id', Auth::id())
            ->first();

        if (!$package) {
            return response()->json(['valid' => false, 'message' => 'Reçu non trouvé']);
        }

        return response()->json([
            'valid' => true,
            'package' => [
                'tracking_number' => $package->tracking_number,
                'status' => $package->status,
                'delivered_at' => $package->delivered_at,
                'recipient_name' => $package->recipient_name
            ]
        ]);
    }

    /**
     * Vérifier un paiement (API)
     */
    public function verifyPayment($paymentId)
    {
        $transaction = FinancialTransaction::where('reference', $paymentId)
            ->where('user_id', Auth::id())
            ->where('type', 'COD_COLLECTION')
            ->first();

        if (!$transaction) {
            return response()->json(['valid' => false, 'message' => 'Paiement non trouvé']);
        }

        return response()->json([
            'valid' => true,
            'payment' => [
                'reference' => $transaction->reference,
                'amount' => $transaction->amount,
                'created_at' => $transaction->created_at
            ]
        ]);
    }

    /**
     * Vérifier une recharge (API)
     */
    public function verifyTopup($topupId)
    {
        $topup = TopupRequest::where('request_code', $topupId)
            ->where('processed_by_id', Auth::id())
            ->first();

        if (!$topup) {
            return response()->json(['valid' => false, 'message' => 'Recharge non trouvée']);
        }

        return response()->json([
            'valid' => true,
            'topup' => [
                'request_code' => $topup->request_code,
                'amount' => $topup->amount,
                'client_name' => $topup->client->name,
                'processed_at' => $topup->processed_at
            ]
        ]);
    }

    /**
     * Vérification publique de reçu
     */
    public function publicVerifyReceipt($trackingNumber)
    {
        $package = Package::where('tracking_number', $trackingNumber)
            ->whereIn('status', ['DELIVERED', 'PAID'])
            ->first();

        if (!$package) {
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid' => true,
            'package' => [
                'tracking_number' => $package->tracking_number,
                'status' => $package->status,
                'delivered_at' => $package->delivered_at
            ]
        ]);
    }

    /**
     * Vérification publique de paiement
     */
    public function publicVerifyPayment($paymentId)
    {
        $transaction = FinancialTransaction::where('reference', $paymentId)
            ->where('type', 'COD_COLLECTION')
            ->first();

        if (!$transaction) {
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid' => true,
            'payment' => [
                'reference' => $transaction->reference,
                'amount' => $transaction->amount,
                'date' => $transaction->created_at->format('Y-m-d')
            ]
        ]);
    }

    /**
     * Vérification publique de recharge
     */
    public function publicVerifyTopup($topupId)
    {
        $topup = TopupRequest::where('request_code', $topupId)
            ->where('status', 'VALIDATED')
            ->first();

        if (!$topup) {
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid' => true,
            'topup' => [
                'request_code' => $topup->request_code,
                'amount' => $topup->amount,
                'date' => $topup->processed_at->format('Y-m-d')
            ]
        ]);
    }

    /**
     * Télécharger reçu avec token sécurisé
     */
    public function downloadWithToken($receiptType, $receiptId, $token)
    {
        try {
            switch ($receiptType) {
                case 'package':
                    return $this->downloadPackageReceipt($receiptId, $token);
                case 'payment':
                    return $this->downloadPaymentReceipt($receiptId, $token);
                case 'topup':
                    return $this->downloadTopupReceipt($receiptId, $token);
                default:
                    abort(404);
            }
        } catch (\Exception $e) {
            abort(404);
        }
    }

    private function downloadPackageReceipt($packageId, $token)
    {
        $package = Package::findOrFail($packageId);
        
        if ($package->assigned_deliverer_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier token
        if (!hash_equals(sha1($package->id . $package->created_at), $token)) {
            abort(404);
        }

        return view('deliverer.receipts.package', compact('package'));
    }

    private function downloadPaymentReceipt($paymentId, $token)
    {
        $payment = WithdrawalRequest::findOrFail($paymentId);
        
        if ($payment->assigned_deliverer_id !== Auth::id()) {
            abort(403);
        }

        if (!hash_equals(sha1($payment->id . $payment->created_at), $token)) {
            abort(404);
        }

        return view('deliverer.receipts.payment', compact('payment'));
    }

    private function downloadTopupReceipt($topupId, $token)
    {
        $topup = TopupRequest::findOrFail($topupId);
        
        if ($topup->processed_by_id !== Auth::id()) {
            abort(403);
        }

        if (!hash_equals(sha1($topup->id . $topup->created_at), $token)) {
            abort(404);
        }

        return view('deliverer.receipts.topup', compact('topup'));
    }
}