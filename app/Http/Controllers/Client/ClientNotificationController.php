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

            case 'COMPLAINT_RESPONSE':
                if (isset($data['complaint_id'])) {
                    return route('client.complaints.show', $data['complaint_id']);
                }
                break;

            case 'WITHDRAWAL_APPROVED':
            case 'WITHDRAWAL_REJECTED':
                return route('client.withdrawals');

            case 'WALLET_CREDITED':
            case 'WALLET_DEBITED':
                return route('client.wallet.index');

            default:
                return route('client.dashboard');
        }

        return route('client.dashboard');
    }
}