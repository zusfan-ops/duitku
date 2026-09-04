<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Services\FcmService;

class NotificationController extends BaseController
{
    protected NotificationModel $notifModel;
    protected UserModel         $userModel;
    protected FcmService        $fcmService;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
        $this->userModel  = new UserModel();
        $this->fcmService = new FcmService();
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
            'fcmConfigured' => $this->fcmService->isConfigured(),
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

        $notifId = $this->notifModel->getInsertID();

        // Kirim Push Notification FCM ke seluruh perangkat HP
        $fcmNotice = '';
        if ($this->fcmService->isConfigured()) {
            $fcmResult = $this->fcmService->sendToTopic('duitku_broadcasts', $title, $message, [
                'type'       => $type,
                'action_url' => $actionUrl ?: '',
                'notif_id'   => (string)$notifId,
            ]);

            if (!empty($fcmResult['success'])) {
                $fcmNotice = ' 🔔 Push notification FCM berhasil dikirim ke seluruh HP pengguna!';
            } else {
                $errMsg = $fcmResult['message'] ?? (json_encode($fcmResult['response'] ?? ''));
                $fcmNotice = ' (Catatan: FCM gagal dikirim: ' . esc($errMsg) . ')';
            }
        } else {
            $fcmNotice = ' (Info: Service Account Firebase belum dipasang, notifikasi tersimpan di database lokal).';
        }

        return redirect()->to('/admin/notifications')->with('success', 'Notifikasi berhasil dipublikasikan ke aplikasi DuitKu!' . $fcmNotice);
    }

    public function saveFcmConfig()
    {
        $json = trim($this->request->getPost('service_account_json') ?? '');
        $file = $this->request->getFile('service_account_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $json = file_get_contents($file->getTempName());
        }

        if (empty($json)) {
            return redirect()->back()->with('error', 'File atau teks JSON Service Account tidak boleh kosong.');
        }

        if ($this->fcmService->saveServiceAccount($json)) {
            return redirect()->back()->with('success', 'Kredensial Firebase Service Account berhasil disimpan! Push notification FCM siap digunakan.');
        }

        return redirect()->back()->with('error', 'Format JSON Service Account tidak valid. Pastikan file JSON yang diunduh dari Firebase Console benar.');
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

    public function testFcm()
    {
        $result = $this->fcmService->testConnection();
        return $this->response->setJSON($result);
    }
}
