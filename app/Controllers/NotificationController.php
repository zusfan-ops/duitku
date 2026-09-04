<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    protected NotificationModel $notifModel;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
    }

    public function index()
    {
        $userId = (int)(session()->get('user_id') ?? 0);
        $notifications = $this->notifModel->getForUser($userId, 40);

        $data = [
            'pageTitle'     => 'Pemberitahuan & Pengumuman',
            'notifications' => $notifications,
            'unreadCount'   => $this->notifModel->getUnreadCount($userId),
        ];

        return view('notifications/index', $data);
    }

    public function markAsRead(int $id)
    {
        $userId = (int)(session()->get('user_id') ?? 0);
        if ($userId > 0) {
            $this->notifModel->markAsRead($id, $userId);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'success']);
        }

        return redirect()->to('/notifications');
    }

    public function markAllAsRead()
    {
        $userId = (int)(session()->get('user_id') ?? 0);
        if ($userId > 0) {
            $notifications = $this->notifModel->getForUser($userId, 100);

            foreach ($notifications as $n) {
                $this->notifModel->markAsRead((int)$n['id'], $userId);
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'success']);
        }

        return redirect()->to('/notifications')->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
