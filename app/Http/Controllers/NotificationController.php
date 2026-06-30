<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get the notifications for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications()
    {
        $unreadNotifications = Auth::user()->unreadNotifications; // Get all notifications
        //$data['unreadNotifications']=$unreadNotifications;
        $readNotifications = Auth::user()->notifications()
            ->whereNotNull('read_at') // Only read notifications
            ->whereDate('read_at', now()->toDateString()) // Read today
            ->get();
        $mergedNotifications = $readNotifications->toArray();  // Convert read notifications to array
        $mergedNotifications = array_merge($mergedNotifications, $unreadNotifications->toArray());

        $data['notifications'] = $mergedNotifications;
        return response()->json(['notifications' => $data]);
    }

    /**
     * Mark a notification as read.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['error' => 'Notification not found'], 404);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function sendNotification(Request $request)
    {
        // Validate incoming request using $request->validate()
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'url' => 'nullable|string|url',
        ]);

        // Find the user to notify
        $user = User::find($validatedData['user_id']);

        // Notification data
        $data = [
            'title' => $validatedData['title'],
            'message' => $validatedData['message'],
            'url' => $validatedData['url'] ?? null,
        ];

        // Send the notification
        try {
            $user->notify(new AppNotification($data));
            return resp(1, 'Notification sent successfully.', [], Response::HTTP_OK);
        } catch (\Exception $e) {
            return resp(0, 'Failed to send notification.', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
