<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\WithdrawalRequest;
use App\Models\TopupRequest;
use Illuminate\Support\Facades\Auth;

class DelivererReceiptController extends Controller
{
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