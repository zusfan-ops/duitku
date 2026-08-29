<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use App\Models\UserModel;

class NotificationController extends BaseController
{
    protected NotificationModel $notifModel;
    protected UserModel         $userModel;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
        $this->userModel  = new UserModel();
    }

    public function index()
    {
        $this->notifModel->ensureTable();
        $notifications = $this->notifModel->orderBy('is_pinned', 'DESC')
                                          ->orderBy('created_at', 'DESC')
                                          ->findAll();

        $users = $this->userModel->select('id, name, email, phone')->orderBy('name', 'ASC')->findAll();

        $data = [
            'pageTitle'     => 'Kirim & Kelola Notifikasi Apps',
            'activeMenu'    => 'notifications',
            'notifications' => $notifications,
            'users'         => $users,
        ];

        return view('admin/notifications/index', $data);
    }

    public function store()
    {
        $title     = trim($this->request->getPost('title') ?? '');
        $message   = trim($this->request->getPost('message') ?? '');
        $type      = $this->request->getPost('type') ?? 'info';
        $target    = $this->request->getPost('target') ?? 'all';
        $userId    = $this->request->getPost('user_id') ?: null;
        $actionUrl = trim($this->request->getPost('action_url') ?? '');
        $isPinned  = $this->request->getPost('is_pinned') ? 1 : 0;

        if (empty($title) || empty($message)) {
            return redirect()->back()->withInput()->with('error', 'Judul dan isi pesan notifikasi wajib diisi.');
        }

        if ($target === 'user' && empty($userId)) {
            return redirect()->back()->withInput()->with('error', 'Target pengguna tertentu wajib dipilih.');
        }

        $inserted = $this->notifModel->insert([
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'target'     => $target,
            'user_id'    => $target === 'user' ? (int)$userId : null,
            'action_url' => $actionUrl ?: null,
            'is_pinned'  => $isPinned,
        ]);

        if (!$inserted) {
            return redirect()->back()->withInput()->with('error', 'Gagal mengirim notifikasi.');
        }

        return redirect()->to('/admin/notifications')->with('success', 'Notifikasi berhasil dikirim ke aplikasi DuitKu!');
    }

    public function togglePin(int $id)
    {
        $notif = $this->notifModel->find($id);
        if ($notif) {
            $newPinned = $notif['is_pinned'] ? 0 : 1;
            $this->notifModel->update($id, ['is_pinned' => $newPinned]);
            return redirect()->to('/admin/notifications')->with('success', 'Status pin notifikasi berhasil diubah.');
        }
        return redirect()->to('/admin/notifications')->with('error', 'Notifikasi tidak ditemukan.');
    }

    public function delete(int $id)
    {
        $this->notifModel->delete($id);
        return redirect()->to('/admin/notifications')->with('success', 'Notifikasi berhasil dihapus.');
    }
}
