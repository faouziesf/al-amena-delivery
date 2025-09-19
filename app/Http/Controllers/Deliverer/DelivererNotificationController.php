<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelivererNotificationController extends Controller
{
    /**
     * Liste des notifications
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications()->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('unread_only')) {
            $query->where('read', false);
        }

        $notifications = $query->paginate(20)->appends($request->query());

        $stats = [
            'total_unread' => Auth::user()->notifications()->where('read', false)->count(),
            'urgent_unread' => Auth::user()->notifications()->where('read', false)->where('priority', 'URGENT')->count(),
            'today_count' => Auth::user()->notifications()->whereDate('created_at', today())->count()
        ];

        return view('deliverer.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Marquer toutes comme lues
     */
    public function markAllRead()
    {
        try {
            $count = Auth::user()->notifications()
                          ->where('read', false)
                          ->update([
                              'read' => true,
                              'read_at' => now()
                          ]);

            return response()->json([
                'success' => true,
                'message' => "{$count} notifications marquées comme lues"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['success' => false], 403);
        }

        try {
            $notification->update([
                'read' => true,
                'read_at' => now()
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Nombre de notifications non lues
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => Auth::user()->notifications()->where('read', false)->count(),
            'urgent_count' => Auth::user()->notifications()->where('read', false)->where('priority', 'URGENT')->count()
        ]);
    }
}