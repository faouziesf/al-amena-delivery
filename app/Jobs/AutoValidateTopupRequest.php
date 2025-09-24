<?php

namespace App\Jobs;

use App\Models\TopupRequest;
use App\Services\FinancialAutomationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class AutoValidateTopupRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * ID de la demande de topup
     */
    protected int $topupRequestId;

    /**
     * Délai avant traitement (en minutes)
     */
    protected int $delayMinutes;

    /**
     * Nombre maximum de tentatives
     */
    public int $tries = 2;

    /**
     * Timeout en secondes
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(int $topupRequestId, int $delayMinutes = 120)
    {
        $this->topupRequestId = $topupRequestId;
        $this->delayMinutes = $delayMinutes;

        // Queue spécialisée pour les topups
        $this->onQueue('topup-validation');

        // Délai avant exécution
        $this->delay(now()->addMinutes($delayMinutes));
    }

    /**
     * Execute the job.
     */
    public function handle(FinancialAutomationService $financialService): void
    {
        try {
            $topupRequest = TopupRequest::find($this->topupRequestId);

            if (!$topupRequest) {
                Log::warning("Demande topup introuvable pour auto-validation", [
                    'topup_request_id' => $this->topupRequestId
                ]);
                return;
            }

            // Vérifier que la demande est toujours en attente
            if (!$topupRequest->isPending()) {
                Log::info("Demande topup déjà traitée, arrêt auto-validation", [
                    'topup_request_id' => $this->topupRequestId,
                    'current_status' => $topupRequest->status
                ]);
                return;
            }

            Log::info("Début auto-validation demande topup", [
                'topup_request_id' => $this->topupRequestId,
                'request_code' => $topupRequest->request_code,
                'amount' => $topupRequest->amount,
                'method' => $topupRequest->method,
                'delay_minutes' => $this->delayMinutes
            ]);

            // Utiliser le service pour déterminer l'éligibilité
            $isEligible = $this->isTopupEligibleForAutoValidation($topupRequest, $financialService);

            if ($isEligible) {
                // Auto-valider
                $this->autoValidateTopup($topupRequest, $financialService);

                Log::info("Auto-validation topup réussie", [
                    'topup_request_id' => $this->topupRequestId,
                    'request_code' => $topupRequest->request_code,
                    'amount' => $topupRequest->amount
                ]);

            } else {
                Log::info("Demande topup non éligible pour auto-validation", [
                    'topup_request_id' => $this->topupRequestId,
                    'request_code' => $topupRequest->request_code,
                    'amount' => $topupRequest->amount,
                    'reason' => 'Critères d\'éligibilité non respectés'
                ]);

                // Marquer pour traitement manuel
                $this->flagForManualReview($topupRequest);
            }

        } catch (Exception $e) {
            Log::error("Erreur lors de l'auto-validation topup", [
                'topup_request_id' => $this->topupRequestId,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            throw $e; // Re-lancer pour déclencher retry si configuré
        }
    }

    /**
     * Vérifier l'éligibilité à l'auto-validation
     */
    protected function isTopupEligibleForAutoValidation(TopupRequest $topupRequest, FinancialAutomationService $financialService): bool
    {
        // Utiliser la méthode protégée du service via reflection (pas idéal, mais fonctionnel)
        // Ou dupliquer la logique ici (plus propre)

        $rules = [
            // Montant raisonnable
            $topupRequest->amount <= 500,
            $topupRequest->amount >= 10,

            // Méthode bancaire uniquement
            in_array($topupRequest->method, ['BANK_TRANSFER', 'BANK_DEPOSIT']),

            // Identifiant bancaire présent et unique
            !empty($topupRequest->bank_transfer_id) &&
            TopupRequest::isBankTransferIdUnique($topupRequest->bank_transfer_id, $topupRequest->id),

            // Client existant avec wallet
            $topupRequest->client && $topupRequest->client->wallet,

            // Format de l'identifiant correct
            $this->isValidBankTransferId($topupRequest->bank_transfer_id),

            // Délai minimum respecté
            $topupRequest->created_at <= now()->subMinutes($this->delayMinutes),

            // Limite quotidienne respectée
            $this->isWithinDailyLimit($topupRequest),

            // Pas de suspicious activity
            !$this->hasSuspiciousActivity($topupRequest)
        ];

        return !in_array(false, $rules, true);
    }

    /**
     * Valider le format de l'identifiant bancaire
     */
    protected function isValidBankTransferId(string $bankTransferId): bool
    {
        $patterns = [
            '/^TX[0-9]{8,12}$/i',
            '/^REF[0-9]{6,10}$/i',
            '/^VIR[0-9]{8,12}$/i',
            '/^[0-9]{8,15}$/i',
            '/^[A-Z0-9]{6,20}$/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $bankTransferId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifier la limite quotidienne
     */
    protected function isWithinDailyLimit(TopupRequest $topupRequest): bool
    {
        $todayCount = TopupRequest::where('client_id', $topupRequest->client_id)
            ->whereDate('created_at', today())
            ->count();

        $todayAmount = TopupRequest::where('client_id', $topupRequest->client_id)
            ->whereDate('created_at', today())
            ->sum('amount');

        return $todayCount <= 3 && $todayAmount <= 1000;
    }

    /**
     * Détecter une activité suspecte
     */
    protected function hasSuspiciousActivity(TopupRequest $topupRequest): bool
    {
        // Vérifications de sécurité
        $checks = [
            // Trop de demandes récentes
            TopupRequest::where('client_id', $topupRequest->client_id)
                ->where('created_at', '>=', now()->subHours(6))
                ->count() > 5,

            // Montants suspects (très ronds ou patterns répétitifs)
            in_array($topupRequest->amount, [100, 200, 300, 500]) &&
            TopupRequest::where('client_id', $topupRequest->client_id)
                ->where('amount', $topupRequest->amount)
                ->where('created_at', '>=', now()->subDays(7))
                ->count() > 2,

            // Client créé récemment
            $topupRequest->client->created_at >= now()->subDays(1),

            // Identifiant bancaire trop simple ou pattern suspect
            preg_match('/^(123|000|111|999|abc)/i', $topupRequest->bank_transfer_id)
        ];

        return in_array(true, $checks, true);
    }

    /**
     * Auto-valider la demande
     */
    protected function autoValidateTopup(TopupRequest $topupRequest, FinancialAutomationService $financialService)
    {
        // Utiliser la méthode du service
        $reflection = new \ReflectionClass($financialService);
        $method = $reflection->getMethod('autoValidateTopup');
        $method->setAccessible(true);

        $method->invoke($financialService, $topupRequest);
    }

    /**
     * Marquer pour révision manuelle
     */
    protected function flagForManualReview(TopupRequest $topupRequest)
    {
        // Ajouter metadata pour indiquer qu'elle nécessite une révision manuelle
        $metadata = $topupRequest->metadata ?? [];
        $metadata['auto_validation_attempted'] = true;
        $metadata['auto_validation_attempted_at'] = now()->toISOString();
        $metadata['requires_manual_review'] = true;
        $metadata['auto_validation_delay_minutes'] = $this->delayMinutes;

        $topupRequest->update(['metadata' => $metadata]);
    }

    /**
     * Gestion des échecs
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Échec définitif auto-validation topup", [
            'topup_request_id' => $this->topupRequestId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Marquer la demande comme nécessitant une révision manuelle
        try {
            $topupRequest = TopupRequest::find($this->topupRequestId);
            if ($topupRequest && $topupRequest->isPending()) {
                $this->flagForManualReview($topupRequest);
            }
        } catch (Exception $e) {
            Log::error("Erreur lors du marquage pour révision manuelle", [
                'topup_request_id' => $this->topupRequestId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Tags pour identifier le job
     */
    public function tags(): array
    {
        return [
            'topup',
            'auto-validation',
            "topup-{$this->topupRequestId}",
        ];
    }
}