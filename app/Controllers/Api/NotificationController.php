<?php

namespace App\Controllers\Api;

use App\Models\NotificationModel;

class NotificationController extends ApiController
{
    protected NotificationModel $notifModel;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
    }

    public function index()
    {
        $userId = $this->uid();
        $limit  = (int) ($this->request->getGet('limit') ?? 30);
        $notifs = $this->notifModel->getForUser($userId, $limit);
        $unread = $this->notifModel->getUnreadCount($userId);

        $payload = array_map(function ($n) {
            return [
                'id'         => (int) $n['id'],
                'title'      => $n['title'],
                'message'    => $n['message'],
                'type'       => $n['type'],
                'action_url' => $n['action_url'],
                'is_pinned'  => (bool) ($n['is_pinned'] ?? false),
                'is_read'    => (bool) ($n['is_read'] ?? false),
                'created_at' => $n['created_at'],
            ];
        }, $notifs);

        return $this->ok([
            'notifications' => $payload,
            'unread_count'  => $unread,
        ]);
    }

    public function markAsRead(int $id)
    {
        $userId = $this->uid();
        $this->notifModel->markAsRead($id, $userId);

        return $this->ok([
            'success'      => true,
            'unread_count' => $this->notifModel->getUnreadCount($userId),
        ]);
    }

    public function markAllAsRead()
    {
        $userId = $this->uid();
        $notifs = $this->notifModel->getForUser($userId, 100);
        foreach ($notifs as $n) {
            $this->notifModel->markAsRead((int) $n['id'], $userId);
        }

        return $this->ok([
            'success'      => true,
            'unread_count' => 0,
        ]);
    }
}
