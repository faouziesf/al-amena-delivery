<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DelivererHelpController extends Controller
{
    public function index()
    {
        return view('deliverer.help.index');
    }

    public function qrScanner()
    {
        return view('deliverer.help.qr-scanner');
    }

    public function codProcess()
    {
        return view('deliverer.help.cod-process');
    }

    public function contactSupport(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'priority' => 'required|in:LOW,NORMAL,HIGH,URGENT'
        ]);

        try {
            // TODO: Envoyer email au support
            // Mail::to('support@alamena.com')->send(new SupportMessage($validated, auth()->user()));

            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé au support technique.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi.'
            ], 500);
        }
    }
}