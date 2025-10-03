<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketIntegrationService
{
    /**
     * Créer un ticket de réclamation directement
     */
    public function createComplaintTicketDirect($packageId, $clientId, $type, $description, $attachments = [])
    {
        try {
            DB::beginTransaction();

            $package = Package::findOrFail($packageId);
            $client = User::findOrFail($clientId);

            // Générer un numéro de ticket unique
            $ticketNumber = $this->generateTicketNumber();

            // Créer le ticket de réclamation
            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'client_id' => $clientId,
                'package_id' => $packageId,
                'subject' => $this->getSubjectByType($type),
                'description' => $description,
                'priority' => 'NORMAL',
                'status' => 'OPEN',
                'is_complaint' => true,
                'complaint_type' => $type,
                'category' => 'COMPLAINT',
                'source' => 'CLIENT_PORTAL',
                'metadata' => [
                    'package_code' => $package->package_code,
                    'client_name' => $client->name,
                    'client_phone' => $client->phone,
                    'complaint_type' => $type,
                    'original_description' => $description,
                    'attachments' => $attachments,
                    'created_via' => 'complaint_system',
                    'package_status_at_creation' => $package->status,
                    'package_current_cod' => $package->cod_amount
                ],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Ajouter une entrée dans l'historique du ticket
            $ticket->addToHistory('CREATED', 'Ticket créé suite à une réclamation client', $clientId, 'CLIENT');

            // Créer le premier message avec la description de la réclamation
            $this->createInitialTicketMessage($ticket, $description, $clientId, $attachments);

            // Si des pièces jointes sont fournies, les traiter
            if (!empty($attachments)) {
                $this->processTicketAttachments($ticket, $attachments);
            }

            DB::commit();

            return $ticket;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Erreur lors de la création du ticket: " . $e->getMessage());
        }
    }

    /**
     * Générer un numéro de ticket unique
     */
    private function generateTicketNumber()
    {
        $prefix = 'TKT';
        $date = now()->format('Ymd');

        // Obtenir le dernier numéro du jour
        $lastTicket = Ticket::where('ticket_number', 'LIKE', $prefix . $date . '%')
                           ->orderBy('ticket_number', 'desc')
                           ->first();

        if ($lastTicket) {
            $lastNumber = intval(substr($lastTicket->ticket_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtenir le sujet selon le type de réclamation
     */
    private function getSubjectByType($type)
    {
        $subjects = [
            'CHANGE_COD' => 'Demande de modification du montant COD',
            'DELIVERY_DELAY' => 'Réclamation - Retard de livraison',
            'REQUEST_RETURN' => 'Demande de retour anticipé',
            'RETURN_DELAY' => 'Réclamation - Retard de retour',
            'RESCHEDULE_TODAY' => 'Demande de report de livraison',
            'FOURTH_ATTEMPT' => 'Demande de 4ème tentative de livraison',
            'CUSTOM' => 'Réclamation personnalisée'
        ];

        return $subjects[$type] ?? 'Réclamation générale';
    }

    /**
     * Traiter les pièces jointes du ticket
     */
    private function processTicketAttachments($ticket, $attachments)
    {
        foreach ($attachments as $attachment) {
            $ticket->attachments()->create([
                'filename' => $attachment['name'],
                'original_filename' => $attachment['name'],
                'path' => $attachment['path'],
                'url' => $attachment['url'],
                'size' => $attachment['size'],
                'mime_type' => $attachment['type'],
                'uploaded_by' => $ticket->client_id,
                'uploaded_at' => now()
            ]);
        }
    }

    /**
     * Convertir une réclamation existante en ticket
     */
    public function convertComplaintToTicket($complaint)
    {
        try {
            DB::beginTransaction();

            // Créer le ticket basé sur la réclamation
            $ticket = $this->createComplaintTicketDirect(
                $complaint->package_id,
                $complaint->client_id,
                $complaint->type,
                $complaint->description,
                $complaint->attachments->map(function($attachment) {
                    return [
                        'name' => $attachment->filename,
                        'path' => $attachment->path,
                        'url' => asset('storage/' . $attachment->path),
                        'size' => $attachment->size,
                        'type' => $attachment->mime_type
                    ];
                })->toArray()
            );

            // Copier les métadonnées de la réclamation
            $metadata = $ticket->metadata ?? [];
            $metadata['converted_from_complaint'] = true;
            $metadata['original_complaint_id'] = $complaint->id;
            $metadata['complaint_created_at'] = $complaint->created_at;
            $metadata['complaint_status'] = $complaint->status;

            if ($complaint->urgent) {
                $ticket->markAsUrgent('Réclamation originale marquée comme urgente');
            }

            $ticket->update(['metadata' => $metadata]);

            // Marquer la réclamation comme convertie
            $complaint->update([
                'status' => 'CONVERTED_TO_TICKET',
                'converted_ticket_id' => $ticket->id,
                'converted_at' => now()
            ]);

            DB::commit();

            return $ticket;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Erreur lors de la conversion: " . $e->getMessage());
        }
    }

    /**
     * Assigner automatiquement un ticket à un commercial
     */
    public function autoAssignTicket($ticket)
    {
        // Logique d'assignation automatique
        $commercial = User::where('role', 'COMMERCIAL')
                         ->where('account_status', 'ACTIVE')
                         ->withCount(['assignedTickets' => function($query) {
                             $query->whereIn('status', ['OPEN', 'IN_PROGRESS']);
                         }])
                         ->orderBy('assigned_tickets_count', 'asc')
                         ->first();

        if ($commercial) {
            $ticket->assignTo($commercial->id);

            // Créer notification
            $commercial->notifications()->create([
                'type' => 'TICKET_ASSIGNED',
                'title' => 'Nouveau ticket assigné' . ($ticket->isUrgent() ? ' - URGENT' : ''),
                'message' => "Ticket {$ticket->ticket_number} vous a été assigné",
                'priority' => $ticket->isUrgent() ? 'HIGH' : 'NORMAL',
                'data' => [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'complaint_type' => $ticket->complaint_type,
                    'is_urgent' => $ticket->isUrgent()
                ]
            ]);

            return $commercial;
        }

        return null;
    }

    /**
     * Synchroniser le statut entre ticket et réclamation
     */
    public function syncTicketComplaintStatus($ticket, $newStatus, $updatedBy = null)
    {
        if (!$ticket->is_complaint) {
            return;
        }

        // Si le ticket a été créé à partir d'une réclamation
        $originalComplaintId = $ticket->metadata['original_complaint_id'] ?? null;
        if ($originalComplaintId) {
            $complaint = \App\Models\Complaint::find($originalComplaintId);
            if ($complaint) {
                $complaintStatus = $this->mapTicketStatusToComplaint($newStatus);
                $complaint->update([
                    'status' => $complaintStatus,
                    'last_activity_at' => now()
                ]);
            }
        }
    }

    /**
     * Mapper le statut du ticket vers le statut de réclamation
     */
    private function mapTicketStatusToComplaint($ticketStatus)
    {
        $mapping = [
            'OPEN' => 'PENDING',
            'IN_PROGRESS' => 'IN_PROGRESS',
            'RESOLVED' => 'RESOLVED',
            'CLOSED' => 'CLOSED',
            'CANCELLED' => 'CANCELLED'
        ];

        return $mapping[$ticketStatus] ?? 'IN_PROGRESS';
    }

    /**
     * Obtenir les statistiques des tickets de réclamation
     */
    public function getComplaintTicketStats($clientId = null, $commercialId = null)
    {
        $query = Ticket::where('is_complaint', true);

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($commercialId) {
            $query->where('assigned_to', $commercialId);
        }

        return [
            'total' => $query->count(),
            'open' => $query->clone()->where('status', 'OPEN')->count(),
            'in_progress' => $query->clone()->where('status', 'IN_PROGRESS')->count(),
            'resolved' => $query->clone()->where('status', 'RESOLVED')->count(),
            'closed' => $query->clone()->where('status', 'CLOSED')->count(),
            'urgent' => $query->clone()->where('priority', 'URGENT')->count(),
            'avg_resolution_time' => $query->clone()
                ->whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                ->value('avg_hours')
        ];
    }

    /**
     * Créer le premier message du ticket avec la description de la réclamation
     */
    private function createInitialTicketMessage($ticket, $description, $clientId, $attachments = [])
    {
        \App\Models\TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $clientId,
            'sender_type' => 'CLIENT',
            'message' => $description,
            'attachments' => $attachments,
            'is_internal' => false,
            'metadata' => [
                'is_initial_message' => true,
                'created_from_complaint' => true
            ]
        ]);
    }
}