<?php

namespace App\Listeners;

use App\Models\Notification;

class SendNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event)
    {
       $data = [
            'notify_user_id' => $event->currentUserId,
            'notify_user_type' => $event->notifyUserType ?? 'guest',
            'other_user_id' => $event->notifyToUserId,
            'other_user_type' => $event->otherUserType ?? 'guest',
            'title' => $event->title ?? 'Title',
            'message' => $event->message,
            'notification_type' => $event->notificationType ?? 'push',
            'data' => json_encode($event->data)
       ];
        return Notification::insert($data);
    }
}