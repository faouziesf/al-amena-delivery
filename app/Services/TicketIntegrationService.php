<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Complaint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketIntegrationService
{
    /**
     * Créer automatiquement un ticket depuis une réclamation
     */
    public function createTicketFromComplaint(Complaint $complaint): Ticket
    {
        return DB::transaction(function () use ($complaint) {
            // Déterminer la priorité basée sur la réclamation
            $priority = $this->mapComplaintPriorityToTicket($complaint);

            // Créer le ticket
            $ticket = Ticket::create([
                'type' => 'COMPLAINT',
                'subject' => "Réclamation #{$complaint->complaint_code} - {$this->getComplaintTypeDisplay($complaint->type)}",
                'description' => $complaint->description,
                'priority' => $priority,
                'client_id' => $complaint->client_id,
                'complaint_id' => $complaint->id,
                'package_id' => $complaint->package_id,
                'assigned_to_id' => $complaint->assigned_commercial_id,
                'status' => $complaint->assigned_commercial_id ? 'IN_PROGRESS' : 'OPEN'
            ]);

            // Message initial automatique
            $initialMessage = $this->generateInitialMessage($complaint);

            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $complaint->client_id,
                'sender_type' => 'CLIENT',
                'message' => $initialMessage,
                'is_internal' => false,
                'metadata' => [
                    'auto_created' => true,
                    'source' => 'complaint_integration',
                    'complaint_id' => $complaint->id
                ]
            ]);

            // Log de l'intégration
            Log::info('Ticket créé automatiquement depuis réclamation', [
                'ticket_id' => $ticket->id,
                'complaint_id' => $complaint->id,
                'client_id' => $complaint->client_id
            ]);

            return $ticket;
        });
    }

    /**
     * Synchroniser le statut entre réclamation et ticket
     */
    public function syncComplaintTicketStatus(Complaint $complaint): void
    {
        if (!$complaint->ticket) {
            return;
        }

        $ticket = $complaint->ticket;

        // Logique de synchronisation basée sur le statut de la réclamation
        switch ($complaint->status) {
            case 'PENDING':
                if ($ticket->status === 'CLOSED') {
                    $ticket->update(['status' => 'IN_PROGRESS']);
                }
                break;

            case 'IN_PROGRESS':
                if (in_array($ticket->status, ['OPEN', 'CLOSED'])) {
                    $ticket->update(['status' => 'IN_PROGRESS']);
                }
                break;

            case 'RESOLVED':
                if ($ticket->status !== 'CLOSED') {
                    $ticket->markAsResolved();
                }
                break;

            case 'CANCELLED':
                if ($ticket->status !== 'CLOSED') {
                    $ticket->close();

                    // Message automatique
                    TicketMessage::create([
                        'ticket_id' => $ticket->id,
                        'sender_id' => 1, // Système
                        'sender_type' => 'SUPERVISOR',
                        'message' => 'Ticket fermé automatiquement suite à l\'annulation de la réclamation associée.',
                        'is_internal' => true,
                        'metadata' => [
                            'auto_sync' => true,
                            'reason' => 'complaint_cancelled'
                        ]
                    ]);
                }
                break;
        }
    }

    /**
     * Obtenir tous les tickets liés aux réclamations d'un client
     */
    public function getClientComplaintTickets($clientId)
    {
        return Ticket::where('client_id', $clientId)
                    ->where('type', 'COMPLAINT')
                    ->whereNotNull('complaint_id')
                    ->with(['complaint', 'messages' => function($q) {
                        $q->orderBy('created_at', 'desc')->limit(1);
                    }])
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Vérifier si une réclamation peut créer un ticket
     */
    public function canComplaintCreateTicket(Complaint $complaint): bool
    {
        // Vérifier si un ticket n'existe pas déjà
        if ($complaint->ticket()->exists()) {
            return false;
        }

        // Vérifier si la réclamation est dans un état permettant la création de ticket
        return in_array($complaint->status, ['PENDING', 'IN_PROGRESS']);
    }

    /**
     * Créer un ticket d'urgence depuis une réclamation critique
     */
    public function createUrgentTicketFromComplaint(Complaint $complaint, string $reason): Ticket
    {
        $ticket = $this->createTicketFromComplaint($complaint);

        // Marquer comme urgent
        $ticket->markAsUrgent("Réclamation critique: " . $reason);

        // Message d'urgence
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $complaint->client_id,
            'sender_type' => 'CLIENT',
            'message' => "🚨 SITUATION URGENTE\n\nRaison: {$reason}\n\nCette réclamation nécessite une attention immédiate.",
            'is_internal' => false,
            'metadata' => [
                'urgent_escalation' => true,
                'escalation_reason' => $reason
            ]
        ]);

        return $ticket;
    }

    /**
     * Obtenir les statistiques d'intégration réclamations-tickets
     */
    public function getIntegrationStats(): array
    {
        $totalComplaints = Complaint::count();
        $complaintsWithTickets = Complaint::has('ticket')->count();

        return [
            'total_complaints' => $totalComplaints,
            'complaints_with_tickets' => $complaintsWithTickets,
            'integration_rate' => $totalComplaints > 0 ? round(($complaintsWithTickets / $totalComplaints) * 100, 2) : 0,
            'tickets_from_complaints' => Ticket::where('type', 'COMPLAINT')->whereNotNull('complaint_id')->count(),
            'urgent_complaint_tickets' => Ticket::where('type', 'COMPLAINT')
                                                ->where('status', 'URGENT')
                                                ->whereNotNull('complaint_id')
                                                ->count()
        ];
    }

    /**
     * Notification automatique pour les commerciaux sur les nouveaux tickets de réclamation
     */
    public function notifyCommercialNewComplaintTicket(Ticket $ticket): void
    {
        if (!$ticket->complaint || !$ticket->assignedTo) {
            return;
        }

        // Ici on pourrait intégrer avec le système de notifications existant
        // Par exemple, créer une notification dans la base de données
        // ou envoyer un email au commercial assigné

        Log::info('Notification commercial nouveau ticket réclamation', [
            'ticket_id' => $ticket->id,
            'commercial_id' => $ticket->assigned_to_id,
            'complaint_id' => $ticket->complaint_id
        ]);
    }

    /**
     * Mapper la priorité de réclamation vers priorité de ticket
     */
    private function mapComplaintPriorityToTicket(Complaint $complaint): string
    {
        // Si la réclamation a une priorité définie
        if (isset($complaint->priority)) {
            return match($complaint->priority) {
                'LOW' => 'LOW',
                'NORMAL' => 'NORMAL',
                'HIGH' => 'HIGH',
                'URGENT' => 'URGENT',
                default => 'NORMAL'
            };
        }

        // Sinon, déterminer basé sur le type de réclamation
        return match($complaint->type) {
            'DAMAGE' => 'HIGH',
            'LOST_PACKAGE' => 'HIGH',
            'DELAY' => 'NORMAL',
            'COD_ISSUE' => 'HIGH',
            'ADDRESS_ERROR' => 'NORMAL',
            'DELIVERY_ISSUE' => 'NORMAL',
            'OTHER' => 'NORMAL',
            default => 'NORMAL'
        };
    }

    /**
     * Générer le message initial du ticket basé sur la réclamation
     */
    private function generateInitialMessage(Complaint $complaint): string
    {
        $message = "Réclamation automatiquement convertie en ticket.\n\n";
        $message .= "📋 **Détails de la réclamation:**\n";
        $message .= "- Code: {$complaint->complaint_code}\n";
        $message .= "- Type: {$this->getComplaintTypeDisplay($complaint->type)}\n";

        if ($complaint->package) {
            $message .= "- Colis: {$complaint->package->package_code}\n";
        }

        $message .= "- Statut: {$complaint->status}\n";
        $message .= "- Créée le: {$complaint->created_at->format('d/m/Y H:i')}\n\n";

        $message .= "📝 **Description:**\n{$complaint->description}\n\n";

        if ($complaint->additional_data && !empty($complaint->additional_data)) {
            $message .= "ℹ️ **Données supplémentaires:**\n";
            foreach ($complaint->additional_data as $key => $value) {
                $message .= "- {$key}: {$value}\n";
            }
        }

        return $message;
    }

    /**
     * Créer un ticket de réclamation directement sans passer par une Complaint
     */
    public function createComplaintTicketDirect(
        int $packageId,
        int $clientId,
        string $type,
        string $description,
        array $attachments = []
    ): Ticket {
        return DB::transaction(function () use ($packageId, $clientId, $type, $description, $attachments) {
            // Déterminer la priorité basée sur le type
            $priority = $this->mapComplaintTypeToTicketPriority($type);

            // Créer le ticket directement
            $ticket = Ticket::create([
                'type' => 'COMPLAINT',
                'subject' => $this->generateComplaintSubject($type),
                'description' => $description,
                'priority' => $priority,
                'client_id' => $clientId,
                'package_id' => $packageId,
                'status' => 'OPEN',
                'is_complaint' => true
            ]);

            // Message initial
            $initialMessage = $this->generateComplaintMessage($type, $description, $attachments);

            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $clientId,
                'sender_type' => 'CLIENT',
                'message' => $initialMessage,
                'is_internal' => false,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
                'metadata' => [
                    'complaint_type' => $type,
                    'direct_creation' => true,
                    'package_id' => $packageId
                ]
            ]);

            // Log de création
            Log::info('Ticket de réclamation créé directement', [
                'ticket_id' => $ticket->id,
                'package_id' => $packageId,
                'client_id' => $clientId,
                'type' => $type
            ]);

            return $ticket;
        });
    }

    /**
     * Mapper le type de réclamation vers priorité de ticket
     */
    private function mapComplaintTypeToTicketPriority(string $type): string
    {
        return match($type) {
            'CHANGE_COD' => 'NORMAL',
            'DELIVERY_DELAY' => 'NORMAL',
            'REQUEST_RETURN' => 'HIGH',
            'RETURN_DELAY' => 'HIGH',
            'RESCHEDULE_TODAY' => 'HIGH',
            'FOURTH_ATTEMPT' => 'HIGH',
            'CUSTOM' => 'NORMAL',
            default => 'NORMAL'
        };
    }

    /**
     * Générer le sujet du ticket basé sur le type de réclamation
     */
    private function generateComplaintSubject(string $type): string
    {
        return match($type) {
            'CHANGE_COD' => 'Demande de modification du montant COD',
            'DELIVERY_DELAY' => 'Réclamation pour retard de livraison',
            'REQUEST_RETURN' => 'Demande de retour de colis',
            'RETURN_DELAY' => 'Réclamation pour retard de retour',
            'RESCHEDULE_TODAY' => 'Demande de report de livraison',
            'FOURTH_ATTEMPT' => 'Demande de 4ème tentative de livraison',
            'CUSTOM' => 'Réclamation personnalisée',
            default => 'Réclamation de colis'
        };
    }

    /**
     * Générer le message de réclamation
     */
    private function generateComplaintMessage(string $type, string $description, array $attachments): string
    {
        $message = "📋 **Nouvelle réclamation**\n\n";
        $message .= "**Type:** " . $this->generateComplaintSubject($type) . "\n";
        $message .= "**Description:**\n{$description}\n\n";

        if (!empty($attachments)) {
            $message .= "📎 **Pièces jointes:** " . count($attachments) . " fichier(s)\n\n";
        }

        $message .= "⏰ **Créée le:** " . now()->format('d/m/Y à H:i') . "\n";

        return $message;
    }

    /**
     * Obtenir l'affichage du type de réclamation
     */
    private function getComplaintTypeDisplay(string $type): string
    {
        return match($type) {
            'DAMAGE' => 'Colis endommagé',
            'LOST_PACKAGE' => 'Colis perdu',
            'DELAY' => 'Retard de livraison',
            'COD_ISSUE' => 'Problème COD',
            'ADDRESS_ERROR' => 'Erreur d\'adresse',
            'DELIVERY_ISSUE' => 'Problème de livraison',
            'OTHER' => 'Autre',
            default => $type
        };
    }
}