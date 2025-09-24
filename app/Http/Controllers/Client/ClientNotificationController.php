<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientNotificationController extends Controller
{
    /**
     * Liste des notifications du client
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $query = $user->notifications()->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('read')) {
            $query->where('read', $request->read === 'true');
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $notifications = $query->paginate(20);

        // Statistiques
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->where('read', false)->count(),
            'high_priority' => $user->notifications()->where('priority', 'HIGH')->where('read', false)->count(),
            'today' => $user->notifications()->whereDate('created_at', today())->count()
        ];

        return view('client.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette notification.');
        }

        $notification->update([
            'read' => true,
            'read_at' => now()
        ]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marquée comme lue');
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        Auth::user()->notifications()
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now()
            ]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues');
    }

    /**
     * Supprimer une notification
     */
    public function delete(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette notification.');
        }

        $notification->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification supprimée');
    }

    /**
     * API - Nombre de notifications non lues
     */
    public function apiUnreadCount()
    {
        return response()->json([
            'count' => Auth::user()->notifications()->where('read', false)->count()
        ]);
    }

    /**
     * API - Notifications récentes
     */
    public function apiRecent()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'priority' => $notification->priority,
                    'read' => $notification->read,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'url' => $this->getNotificationUrl($notification)
                ];
            });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * API - Marquer comme lu via API
     */
    public function apiMarkRead(Request $request)
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ]);

        $updated = Auth::user()->notifications()
            ->whereIn('id', $validated['notification_ids'])
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'updated_count' => $updated
        ]);
    }

    /**
     * API - Polling des notifications
     */
    public function apiPollNotifications()
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->where('read', false)
            ->where('created_at', '>', now()->subMinutes(5))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'priority' => $notification->priority,
                    'read' => $notification->read,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'url' => $this->getNotificationUrl($notification)
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'total_unread' => $user->notifications()->where('read', false)->count()
        ]);
    }

    /**
     * Suppression en masse de notifications
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ]);

        $deleted = Auth::user()->notifications()
            ->whereIn('id', $validated['notification_ids'])
            ->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'deleted_count' => $deleted
            ]);
        }

        return back()->with('success', "$deleted notifications supprimées");
    }

    /**
     * Paramètres de notifications
     */
    public function settings()
    {
        $user = Auth::user();
        return view('client.notifications.settings', compact('user'));
    }

    /**
     * Mise à jour des paramètres de notifications
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'package_updates' => 'boolean',
            'complaint_updates' => 'boolean',
            'wallet_updates' => 'boolean'
        ]);

        $user = Auth::user();
        $user->clientProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'notification_settings' => $validated
            ]
        );

        return back()->with('success', 'Paramètres de notifications mis à jour');
    }

    /**
     * Mise à jour des préférences
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'notification_frequency' => 'in:immediate,hourly,daily',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i'
        ]);

        $user = Auth::user();
        $user->clientProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'notification_preferences' => $validated
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Obtenir l'URL appropriée pour une notification
     */
    private function getNotificationUrl(Notification $notification)
    {
        $data = $notification->data ?? [];

        switch ($notification->type) {
            case 'PACKAGE_STATUS_UPDATED':
                if (isset($data['package_id'])) {
                    return route('client.packages.show', $data['package_id']);
                }
                break;

            case 'PICKUP_REQUEST_ASSIGNED':
            case 'PICKUP_REQUEST_COMPLETED':
                if (isset($data['pickup_request_id'])) {
                    return route('client.pickup-requests.show', $data['pickup_request_id']);
                }
                break;

            case 'COMPLAINT_RESPONSE':
                if (isset($data['complaint_id'])) {
                    return route('client.complaints.show', $data['complaint_id']);
                }
                break;

            case 'WITHDRAWAL_APPROVED':
            case 'WITHDRAWAL_REJECTED':
                return route('client.withdrawals');

            case 'TOPUP_APPROVED':
            case 'TOPUP_REJECTED':
                return route('client.wallet.topup.requests');

            case 'WALLET_CREDITED':
            case 'WALLET_DEBITED':
                return route('client.wallet.index');

            default:
                return route('client.dashboard');
        }

        return route('client.dashboard');
    }
}