<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecNotification;
use Illuminate\Http\Request;

class SecNotificationController extends Controller
{
    /** List notifications for the authenticated user. */
    public function index(Request $request)
    {
        $notifications = SecNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        $unreadCount = SecNotification::where('user_id', $request->user()->id)
            ->unread()
            ->count();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /** Mark a single notification as read. */
    public function markRead(Request $request, SecNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $notification->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }

    /** Mark all notifications as read. */
    public function markAllRead(Request $request)
    {
        SecNotification::where('user_id', $request->user()->id)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications marquées comme lues.']);
    }

    /** Delete a single notification. */
    public function destroy(Request $request, SecNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }
        $notification->delete();
        return response()->json(['message' => 'Notification supprimée.']);
    }

    /** Delete all notifications for the authenticated user. */
    public function destroyAll(Request $request)
    {
        SecNotification::where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Toutes les notifications supprimées.']);
    }
}
