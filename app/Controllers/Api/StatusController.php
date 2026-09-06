<?php

namespace App\Controllers\Api;

use App\Models\NotificationModel;
use App\Models\SettingModel;
use App\Models\UserFriendModel;
use App\Models\UserModel;
use App\Models\UserStatusCommentModel;
use App\Models\UserStatusModel;
use App\Services\FcmService;
use App\Services\ReverbService;

class StatusController extends ApiController
{
    protected UserStatusModel        $statusModel;
    protected UserStatusCommentModel $commentModel;
    protected UserFriendModel        $friendModel;
    protected UserModel              $userModel;
    protected SettingModel           $settingModel;
    protected NotificationModel      $notifModel;
    protected FcmService             $fcmService;
    protected ReverbService          $reverbService;

    public function __construct()
    {
        $this->statusModel   = new UserStatusModel();
        $this->commentModel  = new UserStatusCommentModel();
        $this->friendModel   = new UserFriendModel();
        $this->userModel     = new UserModel();
        $this->settingModel  = new SettingModel();
        $this->notifModel    = new NotificationModel();
        $this->fcmService    = new FcmService();
        $this->reverbService = new ReverbService();
    }

    private function resolveAvatarUrl(int $userId, ?string $avatarImage = null): string
    {
        if (empty($avatarImage)) {
            $avatarImage = $this->settingModel->get($userId, 'avatar_image');
        }
        if ($avatarImage && file_exists(FCPATH . 'uploads/avatars/' . $avatarImage)) {
            return '/uploads/avatars/' . $avatarImage;
        }
        return '';
    }

    /**
     * GET /api/status/feed
     * Ambil feed status teman yang accepted + status sendiri
     */
    public function feed()
    {
        $userId   = $this->uid();
        $statuses = $this->statusModel->getFriendStatuses($userId);

        // Normalize avatar & media url
        foreach ($statuses as &$s) {
            $s['is_mine']           = ((int)$s['user_id'] === $userId);
            $s['author_avatar_url'] = $this->resolveAvatarUrl((int)$s['user_id'], $s['author_avatar_image'] ?? null);
            if (!empty($s['media_url']) && !str_starts_with($s['media_url'], 'http')) {
                $s['media_url'] = '/' . ltrim($s['media_url'], '/');
            }
        }

        return $this->ok([
            'statuses' => $statuses,
            'my_id'    => $userId,
        ]);
    }

    /**
     * POST /api/status/create
     * Buat status baru (teks atau foto)
     * Form-data or JSON:
     * - type: 'text'|'image'
     * - caption: string
     * - background_color: '#2563EB'
     * - image: file (jika type=image)
     */
    public function create()
    {
        $userId    = $this->uid();
        $type      = $this->request->getVar('type') ?: 'text';
        $caption   = trim((string)($this->request->getVar('caption') ?? ''));
        $bgColor   = trim((string)($this->request->getVar('background_color') ?? '#2563EB'));
        $mediaUrl  = null;

        if ($type === 'image') {
            $file = $this->request->getFile('image');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $dir = FCPATH . 'uploads/statuses';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $newName = $file->getRandomName();
                $file->move($dir, $newName);
                $mediaUrl = 'uploads/statuses/' . $newName;
            } elseif ($this->request->getVar('media_url')) {
                $mediaUrl = trim((string)$this->request->getVar('media_url'));
            } else {
                return $this->fail('Foto status wajib diunggah.');
            }
        } else {
            if (empty($caption)) {
                return $this->fail('Teks status tidak boleh kosong.');
            }
        }

        $status = $this->statusModel->createStatus($userId, $type, $mediaUrl, $caption, $bgColor);

        return $this->ok([
            'message' => 'Status berhasil dibuat!',
            'status'  => $status,
        ]);
    }

    /**
     * POST /api/status/comment
     * Beri komentar pada status teman
     * Body: { status_id: 123, comment: "Keren!" }
     */
    public function comment()
    {
        $userId   = $this->uid();
        $statusId = (int)$this->request->getVar('status_id');
        $comment  = trim((string)$this->request->getVar('comment'));

        if ($statusId <= 0 || empty($comment)) {
            return $this->fail('Status dan komentar wajib diisi.');
        }

        // Cek apakah status valid dan user berhak melihat/mengomentari (teman atau pemilik)
        $status = $this->statusModel->getStatusIfAllowed($statusId, $userId);
        if (!$status) {
            return $this->fail('Status tidak ditemukan atau Anda tidak memiliki akses untuk melihat status ini.');
        }

        $authorId = (int)$status['user_id'];
        $createdComment = $this->commentModel->addComment($statusId, $userId, $comment);

        // Notifikasi ke pembuat status jika bukan diri sendiri
        if ($authorId !== $userId) {
            $myInfo = $this->userModel->find($userId);
            $myName = $myInfo['name'] ?: ($myInfo['username'] ?: 'Teman');

            try {
                $this->notifModel->insert([
                    'user_id'    => $authorId,
                    'title'      => "💬 Komentar Status dari {$myName}",
                    'message'    => $comment,
                    'type'       => 'status_comment',
                    'action_url' => '/chat?status_id=' . $statusId,
                ]);
            } catch (\Throwable $e) {}

            try {
                if ($this->fcmService->isConfigured()) {
                    $this->fcmService->sendToUser(
                        $authorId,
                        "💬 {$myName} mengomentari status Anda",
                        $comment,
                        [
                            'type'         => 'status_comment',
                            'status_id'    => (string)$statusId,
                            'sender_id'    => (string)$userId,
                            'sender_name'  => (string)$myName,
                            'action_url'   => '/chat?status_id=' . $statusId,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ]
                    );
                }
            } catch (\Throwable $e) {}
        }

        return $this->ok([
            'message' => 'Komentar berhasil dikirim!',
            'comment' => $createdComment,
        ]);
    }

    /**
     * GET /api/status/{id}/comments
     * Ambil riwayat komentar status
     */
    public function comments(int $statusId)
    {
        $userId = $this->uid();
        $status = $this->statusModel->getStatusIfAllowed($statusId, $userId);
        if (!$status) {
            return $this->fail('Status tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $comments = $this->commentModel->getCommentsForStatus($statusId);
        foreach ($comments as &$c) {
            $c['user_avatar_url'] = $this->resolveAvatarUrl((int)$c['user_id'], $c['user_avatar_image'] ?? null);
        }

        return $this->ok([
            'comments' => $comments,
        ]);
    }

    /**
     * POST /api/status/delete/{id}
     * Hapus status milik sendiri
     */
    public function delete(int $statusId)
    {
        $userId = $this->uid();
        $status = $this->statusModel->find($statusId);
        if (!$status || (int)$status['user_id'] !== $userId) {
            return $this->fail('Akses ditolak atau status tidak ditemukan.');
        }

        if (!empty($status['media_url']) && file_exists(FCPATH . $status['media_url'])) {
            @unlink(FCPATH . $status['media_url']);
        }

        $this->statusModel->delete($statusId);
        return $this->ok(['message' => 'Status berhasil dihapus.']);
    }
}
