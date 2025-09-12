<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = Auth::user()->notifications()->notExpired();

        // Filtres
        if ($request->filled('unread_only') && $request->unread_only) {
            $query->unread();
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->orderBy('priority', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->paginate(30);

        $stats = $this->notificationService->getNotificationStats(Auth::user());

        return view('commercial.notifications.index', compact('notifications', 'stats'));
    }

    public function markAsRead(Request $request, $notificationId = null)
    {
        try {
            if ($notificationId) {
                $count = $this->notificationService->markAsRead(Auth::user(), $notificationId);
                $message = $count > 0 ? 'Notification marquée comme lue.' : 'Notification introuvable.';
            } else {
                $count = $this->notificationService->markAllAsRead(Auth::user());
                $message = "{$count} notifications marquées comme lues.";
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message, 'marked_count' => $count]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public function markAsUnread(Request $request, $notificationId)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($notificationId);
            $notification->markAsUnread();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Notification marquée comme non lue.']);
            }

            return back()->with('success', 'Notification marquée comme non lue.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request, $notificationId)
    {
        try {
            $count = $this->notificationService->deleteNotification(Auth::user(), $notificationId);
            $message = $count > 0 ? 'Notification supprimée.' : 'Notification introuvable.';

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_read,mark_unread,delete',
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'exists:notifications,id',
        ]);

        try {
            $notifications = Auth::user()->notifications()
                                ->whereIn('id', $request->notification_ids)
                                ->get();

            $count = 0;
            foreach ($notifications as $notification) {
                switch ($request->action) {
                    case 'mark_read':
                        $notification->markAsRead();
                        $count++;
                        break;
                    case 'mark_unread':
                        $notification->markAsUnread();
                        $count++;
                        break;
                    case 'delete':
                        $notification->delete();
                        $count++;
                        break;
                }
            }

            $messages = [
                'mark_read' => "{$count} notifications marquées comme lues.",
                'mark_unread' => "{$count} notifications marquées comme non lues.",
                'delete' => "{$count} notifications supprimées.",
            ];

            return back()->with('success', $messages[$request->action]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'action groupée: ' . $e->getMessage()]);
        }
    }

    public function deleteOld(Request $request)
    {
        $request->validate([
            'older_than_days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $count = $this->notificationService->deleteReadNotifications(
                Auth::user(), 
                $request->older_than_days
            );

            return back()->with('success', 
                "{$count} anciennes notifications supprimées (plus de {$request->older_than_days} jours)."
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    // ==================== API ENDPOINTS ====================

    public function apiUnreadCount()
    {
        return response()->json([
            'unread_count' => $this->notificationService->getUnreadCount(Auth::user()),
            'urgent_count' => $this->notificationService->getUrgentCount(Auth::user()),
        ]);
    }

    public function apiRecent()
    {
        $notifications = $this->notificationService->getUserNotifications(
            Auth::user(), 
            true, // unread only
            10    // limit
        );

        $formattedNotifications = $notifications->map(function ($notification) {
            return $this->notificationService->formatNotificationForApi($notification);
        });

        return response()->json($formattedNotifications);
    }

    public function apiAll(Request $request)
    {
        $unreadOnly = $request->boolean('unread_only', false);
        $limit = min($request->integer('limit', 20), 50);

        $notifications = $this->notificationService->getUserNotifications(
            Auth::user(),
            $unreadOnly,
            $limit
        );

        $formattedNotifications = $notifications->map(function ($notification) {
            return $this->notificationService->formatNotificationForApi($notification);
        });

        return response()->json([
            'notifications' => $formattedNotifications,
            'stats' => $this->notificationService->getNotificationStats(Auth::user()),
        ]);
    }

    public function apiMarkRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'nullable|array',
            'notification_ids.*' => 'exists:notifications,id',
            'mark_all' => 'nullable|boolean',
        ]);

        try {
            if ($request->boolean('mark_all')) {
                $count = $this->notificationService->markAllAsRead(Auth::user());
            } else {
                $count = 0;
                foreach ($request->input('notification_ids', []) as $id) {
                    $count += $this->notificationService->markAsRead(Auth::user(), $id);
                }
            }

            return response()->json([
                'success' => true,
                'marked_count' => $count,
                'new_unread_count' => $this->notificationService->getUnreadCount(Auth::user()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiStats()
    {
        return response()->json(
            $this->notificationService->getNotificationStats(Auth::user())
        );
    }

    public function apiByType(Request $request, $type)
    {
        $notifications = Auth::user()->notifications()
                             ->where('type', $type)
                             ->notExpired()
                             ->orderBy('created_at', 'desc')
                             ->limit(20)
                             ->get()
                             ->map(function ($notification) {
                                 return $this->notificationService->formatNotificationForApi($notification);
                             });

        return response()->json($notifications);
    }

    // Méthode pour créer des notifications de test (développement seulement)
    public function createTestNotification(Request $request)
    {
        if (!app()->environment(['local', 'testing'])) {
            abort(404);
        }

        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'priority' => 'required|in:LOW,NORMAL,HIGH,URGENT',
        ]);

        try {
            $notification = Notification::create([
                'user_id' => Auth::id(),
                'type' => $request->type,
                'title' => $request->title,
                'message' => $request->message,
                'priority' => $request->priority,
                'data' => ['test' => true, 'created_by' => 'dev_tool']
            ]);

            return response()->json([
                'success' => true,
                'notification' => $this->notificationService->formatNotificationForApi($notification)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}