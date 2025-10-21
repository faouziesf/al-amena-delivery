<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelivererNotificationController extends Controller
{
    /**
     * Liste des notifications du livreur
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('read')) {
            if ($request->read === 'unread') {
                $query->whereNull('read_at');
            } else {
                $query->whereNotNull('read_at');
            }
        }

        $notifications = $query->paginate(20);

        // Statistiques
        $stats = [
            'total' => Notification::where('user_id', $user->id)->count(),
            'unread' => Notification::where('user_id', $user->id)->whereNull('read_at')->count(),
            'high_priority' => Notification::where('user_id', $user->id)
                ->whereIn('priority', ['HIGH', 'URGENT'])
                ->whereNull('read_at')
                ->count(),
            'today' => Notification::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count()
        ];

        return view('deliverer.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        $notification->update(['read_at' => now()]);

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
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

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
            abort(403, 'Accès non autorisé.');
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
        $count = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $count,
            'urgent_count' => Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->whereIn('priority', ['HIGH', 'URGENT'])
                ->count()
        ]);
    }

    /**
     * API - Notifications récentes
     */
    public function apiRecent()
    {
        $notifications = Notification::where('user_id', Auth::id())
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
                    'priority_display' => $this->getPriorityLabel($notification->priority),
                    'priority_color' => $this->getPriorityColor($notification->priority),
                    'read_at' => $notification->read_at,
                    'created_at_human' => $notification->created_at->diffForHumans(),
                    'created_at' => $notification->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json($notifications);
    }

    /**
     * API - Marquer comme lu via API
     */
    public function apiMarkRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Obtenir le label de priorité
     */
    private function getPriorityLabel($priority)
    {
        return match($priority) {
            'LOW' => 'Faible',
            'NORMAL' => 'Normal',
            'HIGH' => 'Haute',
            'URGENT' => 'Urgent',
            default => $priority
        };
    }

    /**
     * Obtenir la couleur de priorité
     */
    private function getPriorityColor($priority)
    {
        return match($priority) {
            'LOW' => 'bg-gray-100 text-gray-700',
            'NORMAL' => 'bg-blue-100 text-blue-700',
            'HIGH' => 'bg-orange-100 text-orange-700',
            'URGENT' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700'
        };
    }
}
