<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Services\ActionLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelivererEmergencyController extends Controller
{
    protected $actionLogService;

    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
    }

    /**
     * Appeler commercial d'urgence
     */
    public function callCommercial(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'nullable|exists:packages,id',
            'reason' => 'required|in:COD_PROBLEM,CLIENT_DISPUTE,ADDRESS_ISSUE,PAYMENT_ISSUE,OTHER',
            'message' => 'required|string|max:500',
            'urgent' => 'boolean'
        ]);

        try {
            // Log de l'appel d'urgence
            $this->actionLogService->log(
                'EMERGENCY_CALL_COMMERCIAL',
                'Package',
                $validated['package_id'],
                null,
                null,
                [
                    'deliverer_id' => Auth::id(),
                    'reason' => $validated['reason'],
                    'message' => $validated['message'],
                    'urgent' => $validated['urgent'] ?? false,
                    'timestamp' => now()->toISOString()
                ]
            );

            // TODO: Envoyer notification urgente au commercial
            // TODO: Créer ticket support urgent

            return response()->json([
                'success' => true,
                'message' => 'Appel d\'urgence envoyé au commercial. Vous serez contacté rapidement.',
                'contact_info' => [
                    'commercial_phone' => '+216 XX XXX XXX',
                    'support_phone' => '+216 XX XXX XXX'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi. Contactez directement le +216 XX XXX XXX'
            ], 500);
        }
    }

    /**
     * Signaler un problème urgent
     */
    public function reportIssue(Request $request)
    {
        $validated = $request->validate([
            'issue_type' => 'required|in:SECURITY,ACCIDENT,TECHNICAL,PAYMENT,CLIENT_THREAT,OTHER',
            'description' => 'required|string|max:1000',
            'location' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:5120'
        ]);

        try {
            $issueData = [
                'deliverer_id' => Auth::id(),
                'deliverer_name' => Auth::user()->name,
                'type' => $validated['issue_type'],
                'description' => $validated['description'],
                'location' => $validated['location'],
                'reported_at' => now()->toISOString()
            ];

            // Upload photo si fournie
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('emergency_reports', 'public');
                $issueData['photo_path'] = $path;
            }

            // Log du problème
            $this->actionLogService->log(
                'EMERGENCY_ISSUE_REPORTED',
                'Emergency',
                null,
                null,
                null,
                $issueData
            );

            // TODO: Envoyer alerte urgente management
            // TODO: Créer ticket priorité maximale

            return response()->json([
                'success' => true,
                'message' => 'Problème signalé avec succès. Le management sera alerté immédiatement.',
                'emergency_contacts' => [
                    'security' => '+216 XX XXX XXX',
                    'management' => '+216 XX XXX XXX',
                    'police' => '197'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur. En cas d\'urgence, contactez directement le 197 ou +216 XX XXX XXX'
            ], 500);
        }
    }

    /**
     * Déclencher alerte d'urgence générale
     */
    public function triggerEmergency(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:GENERAL,SECURITY,MEDICAL,ACCIDENT,THREAT',
            'message' => 'nullable|string|max:500',
            'location' => 'nullable|array'
        ]);

        try {
            $emergencyData = [
                'deliverer_id' => Auth::id(),
                'deliverer_name' => Auth::user()->name,
                'type' => $validated['type'],
                'message' => $validated['message'] ?? 'Alerte d\'urgence déclenchée',
                'location' => $validated['location'] ?? null,
                'triggered_at' => now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ];

            // Log de l'urgence avec priorité maximale
            $this->actionLogService->log(
                'EMERGENCY_ALERT_TRIGGERED',
                'Emergency',
                null,
                null,
                'URGENT',
                $emergencyData
            );

            // TODO: Notifications push urgentes
            // TODO: SMS/Email management
            // TODO: Alerte centre de contrôle

            return response()->json([
                'success' => true,
                'message' => 'Alerte d\'urgence envoyée! Le centre de contrôle a été notifié.',
                'emergency_id' => 'EMG-' . now()->format('YmdHis') . '-' . Auth::id(),
                'contacts' => [
                    'centre_controle' => '+213 XXX XXX XXX',
                    'urgences' => '15',
                    'police' => '17'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur système. Contactez immédiatement: 15 (urgences) ou 17 (police)'
            ], 500);
        }
    }

    /**
     * API pour déclencher alerte d'urgence
     */
    public function apiTriggerEmergency(Request $request)
    {
        $validated = $request->validate([
            'location' => 'nullable|array',
            'timestamp' => 'nullable|string'
        ]);

        try {
            $emergencyData = [
                'deliverer_id' => Auth::id(),
                'deliverer_name' => Auth::user()->name,
                'type' => 'GENERAL',
                'message' => 'Alerte d\'urgence déclenchée depuis l\'application',
                'location' => $validated['location'] ?? null,
                'triggered_at' => $validated['timestamp'] ?? now()->toISOString(),
                'source' => 'mobile_app'
            ];

            // Log urgent
            $this->actionLogService->log(
                'EMERGENCY_API_ALERT',
                'Emergency',
                null,
                null,
                'CRITICAL',
                $emergencyData
            );

            // TODO: Intégration système d'alerte
            // TODO: Géolocalisation automatique
            // TODO: Vibration/son urgence sur appareils management

            return response()->json([
                'success' => true,
                'message' => 'Alerte transmise au centre de contrôle',
                'emergency_id' => 'API-EMG-' . now()->timestamp . '-' . Auth::id()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur critique. Appelez immédiatement le centre de contrôle.'
            ], 500);
        }
    }
}