<?php

namespace App\Controllers\Api;

use App\Models\ChatConversationSettingModel;
use App\Models\DirectChatModel;
use App\Models\MarketplaceChatModel;
use App\Models\NotificationModel;
use App\Models\UserFriendModel;
use App\Models\UserModel;
use App\Services\FcmService;

class ChatController extends ApiController
{
    protected UserFriendModel      $friendModel;
    protected DirectChatModel      $directChatModel;
    protected MarketplaceChatModel $marketChatModel;
    protected UserModel            $userModel;
    protected NotificationModel    $notifModel;
    protected FcmService           $fcmService;
    protected ChatConversationSettingModel $convSettingModel;

    public function __construct()
    {
        $this->friendModel     = new UserFriendModel();
        $this->directChatModel = new DirectChatModel();
        $this->marketChatModel = new MarketplaceChatModel();
        $this->userModel       = new UserModel();
        $this->notifModel      = new NotificationModel();
        $this->fcmService      = new FcmService();
        $this->convSettingModel = new ChatConversationSettingModel();
    }

    /**
     * GET /api/friends
     * Daftar teman yang sudah accepted
     */
    public function friends()
    {
        $userId  = $this->uid();
        $friends = $this->friendModel->getFriends($userId);
        return $this->ok(['friends' => $friends]);
    }

    /**
     * GET /api/friends/requests
     * Daftar permintaan masuk dan keluar
     */
    public function requests()
    {
        $userId   = $this->uid();
        $incoming = $this->friendModel->getIncomingRequests($userId);
        $outgoing = $this->friendModel->getOutgoingRequests($userId);

        return $this->ok([
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'pending_count' => count($incoming),
        ]);
    }

    /**
     * POST /api/friends/request
     * Kirim permintaan pertemanan berdasarkan username
     * Body: { username: "..." }
     */
    public function sendFriendRequest()
    {
        $userId   = $this->uid();
        $username = trim((string)$this->request->getVar('username'));

        if (empty($username)) {
            return $this->fail('Username tidak boleh kosong.');
        }

        $res = $this->friendModel->sendRequest($userId, $username);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        $sender = $this->userModel->find($userId);
        $senderName = $sender['name'] ?? 'Pengguna';
        $friendId = (int)($res['friend']['id'] ?? 0);

        if ($friendId > 0) {
            $isAccepted = ($res['status'] ?? '') === 'accepted';
            $notifTitle = $isAccepted ? "🤝 Teman Baru!" : "👋 Permintaan Pertemanan";
            $notifMsg   = $isAccepted 
                ? "{$senderName} (@{$sender['username']}) sekarang berteman dengan Anda."
                : "{$senderName} (@{$sender['username']}) ingin menambahkan Anda sebagai teman.";

            // 1. In-app notification
            try {
                $this->notifModel->insert([
                    'user_id'    => $friendId,
                    'title'      => $notifTitle,
                    'message'    => $notifMsg,
                    'type'       => 'friend_request',
                    'action_url' => '/chat?tab=friends',
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Friend request notif error: ' . $e->getMessage());
            }

            // 2. FCM Push notification
            try {
                if ($this->fcmService->isConfigured()) {
                    $this->fcmService->sendToTopic(
                        "user_{$friendId}",
                        $notifTitle,
                        $notifMsg,
                        [
                            'type'         => 'friend_request',
                            'sender_id'    => (string)$userId,
                            'sender_name'  => (string)$senderName,
                            'action_url'   => '/chat?tab=friends',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ]
                    );
                }
            } catch (\Throwable $e) {
                log_message('error', 'Friend request FCM error: ' . $e->getMessage());
            }
        }

        return $this->ok([
            'message' => $res['message'],
            'status'  => $res['status'] ?? 'pending',
            'friend'  => $res['friend'] ?? null,
        ]);
    }

    /**
     * POST /api/friends/respond
     * Respon permintaan pertemanan (accept / reject)
     * Body: { request_id: 123, action: "accept"|"reject" }
     */
    public function respondFriendRequest()
    {
        $userId    = $this->uid();
        $requestId = (int)$this->request->getVar('request_id');
        $action    = trim((string)$this->request->getVar('action'));

        if ($requestId <= 0 || !in_array($action, ['accept', 'reject'], true)) {
            return $this->fail('Parameter permintaan tidak valid.');
        }

        $res = $this->friendModel->respondRequest($requestId, $userId, $action);
        if (!$res['success']) {
            return $this->fail($res['message']);
        }

        // Jika di-accept, kirim notifikasi balik ke pengirim request
        if ($action === 'accept' && !empty($res['friend'])) {
            $senderId = (int)$res['friend']['id'];
            $myInfo   = $this->userModel->find($userId);
            $myName   = $myInfo['name'] ?? 'Teman';

            try {
                $this->notifModel->insert([
                    'user_id'    => $senderId,
                    'title'      => "🤝 Permintaan Diterima!",
                    'message'    => "{$myName} telah menerima permintaan pertemanan Anda.",
                    'type'       => 'friend_accepted',
                    'action_url' => '/chat?direct_user=' . $userId,
                ]);

                if ($this->fcmService->isConfigured()) {
                    $this->fcmService->sendToTopic(
                        "user_{$senderId}",
                        "🤝 Permintaan Diterima!",
                        "{$myName} menerima permintaan pertemanan Anda. Ketuk untuk mulai chat!",
                        [
                            'type'         => 'friend_accepted',
                            'friend_id'    => (string)$userId,
                            'friend_name'  => (string)$myName,
                            'action_url'   => '/chat?direct_user=' . $userId,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ]
                    );
                }
            } catch (\Throwable $e) {
                log_message('error', 'Accept friend notif error: ' . $e->getMessage());
            }
        }

        return $this->ok([
            'message' => $res['message'],
            'status'  => $res['status'],
            'friend'  => $res['friend'] ?? null,
        ]);
    }

    /**
     * GET /api/friends/search?q=budi
     * Cari pengguna berdasarkan username
     */
    public function searchUsers()
    {
        $userId = $this->uid();
        $query  = trim((string)$this->request->getVar('q'));

        $users = $this->friendModel->searchUsers($query, $userId);
        return $this->ok(['users' => $users]);
    }

    /**
     * GET /api/chat/direct/messages?friend_id=123&after_id=0
     * Ambil riwayat chat langsung dengan teman
     */
    public function directMessages()
    {
        $userId   = $this->uid();
        $friendId = (int)$this->request->getVar('friend_id');
        $afterId  = (int)($this->request->getVar('after_id') ?? 0);

        if ($friendId <= 0) {
            return $this->fail('ID Teman tidak valid.');
        }

        // Tandai pesan sebagai dibaca
        $this->directChatModel->markAsRead($friendId, $userId);

        $messages = $this->directChatModel->getMessages($userId, $friendId, $afterId);
        $friend   = $this->userModel->find($friendId);

        return $this->ok([
            'messages' => $messages,
            'friend'   => [
                'id'       => (int)$friendId,
                'name'     => $friend['name'] ?? '',
                'username' => $friend['username'] ?? '',
                'avatar'   => $friend['avatar'] ?? '',
                'phone'    => $friend['phone'] ?? '',
            ],
            'my_id'    => $userId,
        ]);
    }

    /**
     * POST /api/chat/direct/send
     * Kirim pesan langsung WhatsApp ke teman
     * Body: { friend_id: 123, message: "Halo..." }
     */
    public function sendDirectMessage()
    {
        $userId   = $this->uid();
        $friendId = (int)$this->request->getVar('friend_id');
        $message  = trim((string)$this->request->getVar('message'));

        if ($friendId <= 0) {
            return $this->fail('ID Teman tidak valid.');
        }
        if (empty($message)) {
            return $this->fail('Pesan tidak boleh kosong.');
        }

        // Pastikan keduanya berteman sebelum bisa chat
        if (!$this->friendModel->isFriend($userId, $friendId)) {
            return $this->fail('Anda harus berteman terlebih dahulu untuk mengirim pesan langsung.');
        }

        $chat = $this->directChatModel->sendMessage($userId, $friendId, $message);
        $sender = $this->userModel->find($userId);
        $senderName = $sender['name'] ?? 'Teman';

        // 1. In-app notifikasi
        try {
            $this->notifModel->insert([
                'user_id'    => $friendId,
                'title'      => "💬 {$senderName}",
                'message'    => $message,
                'type'       => 'direct_chat',
                'action_url' => '/chat?direct_user=' . $userId,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Direct chat in-app notif error: ' . $e->getMessage());
        }

        // 2. FCM Push notification
        try {
            if ($this->fcmService->isConfigured()) {
                $this->fcmService->sendToTopic(
                    "user_{$friendId}",
                    "💬 {$senderName}",
                    $message,
                    [
                        'type'         => 'direct_chat',
                        'sender_id'    => (string)$userId,
                        'sender_name'  => (string)$senderName,
                        'message'      => (string)$message,
                        'action_url'   => '/chat?direct_user=' . $userId,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ]
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'Direct chat FCM error: ' . $e->getMessage());
        }

        return $this->ok([
            'message' => 'Pesan berhasil dikirim!',
            'chat'    => $chat,
        ]);
    }

    /**
     * GET /api/chat/all-conversations
     * Mengembalikan seluruh percakapan (Direct Friends Chat + Marketplace Chat)
     */
    public function allConversations()
    {
        $userId = $this->uid();

        // Ambil pengaturan percakapan user (pin, archive, cleared)
        $convSettings = $this->convSettingModel->getSettingsForUser($userId);

        // 1. Direct chats
        $directConvs = $this->directChatModel->getConversations($userId);

        // Ambil semua teman yang disetujui
        $allFriends = $this->friendModel->getFriends($userId);
        $existingPartnerIds = array_column($directConvs, 'partner_id');

        foreach ($allFriends as $f) {
            $fId = (int)$f['friend_id'];
            if (!in_array($fId, $existingPartnerIds, true)) {
                $settingKey = 'direct_' . $fId . '_0';
                $s = $convSettings[$settingKey] ?? null;
                if ($s && !empty($s['cleared_at'])) {
                    continue;
                }
                $directConvs[] = [
                    'partner_id'        => $fId,
                    'partner_name'      => $f['name'] ?: ($f['username'] ?: 'Teman'),
                    'partner_username'  => $f['username'] ?: '',
                    'partner_avatar'    => $f['avatar'] ?: '',
                    'partner_phone'     => $f['phone'] ?: '',
                    'last_message'      => 'Ketuk untuk mulai mengobrol',
                    'last_sender_id'    => 0,
                    'last_message_time' => $f['friends_since'] ?? date('Y-m-d H:i:s'),
                    'unread_count'      => 0,
                ];
            }
        }

        $normalizedDirect = [];
        foreach ($directConvs as $c) {
            $fId = (int)$c['partner_id'];
            $settingKey = 'direct_' . $fId . '_0';
            $s = $convSettings[$settingKey] ?? null;

            if ($s && !empty($s['cleared_at'])) {
                if (empty($c['last_message_time']) || strtotime($c['last_message_time']) <= strtotime($s['cleared_at'])) {
                    continue;
                }
            }

            $normalizedDirect[] = [
                'type'              => 'direct',
                'partner_id'        => $fId,
                'target_id'         => $fId,
                'target_sub_id'     => 0,
                'partner_name'      => $c['partner_name'] ?: ($c['partner_username'] ?: 'Teman'),
                'partner_username'  => $c['partner_username'] ?: '',
                'partner_avatar'    => $c['partner_avatar'] ?: '',
                'partner_phone'     => $c['partner_phone'] ?: '',
                'last_message'      => $c['last_message'],
                'last_sender_id'    => (int)$c['last_sender_id'],
                'last_message_time' => $c['last_message_time'],
                'unread_count'      => (int)$c['unread_count'],
                'is_pinned'         => !empty($s['is_pinned']),
                'pinned_at'         => $s['pinned_at'] ?? null,
                'is_archived'       => !empty($s['is_archived']),
                'archived_at'       => $s['archived_at'] ?? null,
            ];
        }

        // 2. Marketplace chats
        $marketConvs = $this->marketChatModel->getConversationsForUser($userId);
        $normalizedMarket = [];
        foreach ($marketConvs as $c) {
            $lid = (int)$c['listing_id'];
            $bid = (int)$c['buyer_id'];
            $settingKey = 'marketplace_' . $lid . '_' . $bid;
            $s = $convSettings[$settingKey] ?? null;

            if ($s && !empty($s['cleared_at'])) {
                if (empty($c['last_message_time']) || strtotime($c['last_message_time']) <= strtotime($s['cleared_at'])) {
                    continue;
                }
            }

            $isSeller = ((int)$c['seller_id'] === $userId);
            $normalizedMarket[] = [
                'type'              => 'marketplace',
                'listing_id'        => $lid,
                'buyer_id'          => $bid,
                'seller_id'         => (int)$c['seller_id'],
                'target_id'         => $lid,
                'target_sub_id'     => $bid,
                'listing_title'     => $c['listing_title'] ?: 'Produk Marketplace',
                'listing_price'     => (float)($c['listing_price'] ?? 0),
                'listing_image'     => $c['listing_image'] ?: '',
                'partner_name'      => $isSeller ? ($c['buyer_name'] ?: 'Calon Pembeli') : ($c['seller_name'] ?: 'Penjual'),
                'partner_phone'     => $isSeller ? ($c['buyer_phone'] ?: '') : ($c['seller_phone'] ?: ''),
                'last_message'      => $c['last_message'],
                'last_sender_id'    => (int)$c['last_sender_id'],
                'last_message_time' => $c['last_message_time'],
                'unread_count'      => (int)$c['unread_count'],
                'is_pinned'         => !empty($s['is_pinned']),
                'pinned_at'         => $s['pinned_at'] ?? null,
                'is_archived'       => !empty($s['is_archived']),
                'archived_at'       => $s['archived_at'] ?? null,
            ];
        }

        // Gabung dan urutkan
        $merged = array_merge($normalizedDirect, $normalizedMarket);
        usort($merged, function ($a, $b) {
            $pinA = !empty($a['is_pinned']) ? 1 : 0;
            $pinB = !empty($b['is_pinned']) ? 1 : 0;
            if ($pinA !== $pinB) {
                return $pinB <=> $pinA;
            }
            if ($pinA === 1) {
                $pA = !empty($a['pinned_at']) ? strtotime($a['pinned_at']) : 0;
                $pB = !empty($b['pinned_at']) ? strtotime($b['pinned_at']) : 0;
                if ($pA !== $pB) return $pB <=> $pA;
            }
            $tA = !empty($a['last_message_time']) ? strtotime($a['last_message_time']) : 0;
            $tB = !empty($b['last_message_time']) ? strtotime($b['last_message_time']) : 0;
            return $tB <=> $tA;
        });

        // Hitung total unread & total archived
        $totalUnread = 0;
        $archivedCount = 0;
        foreach ($merged as $item) {
            $totalUnread += (int)$item['unread_count'];
            if (!empty($item['is_archived'])) {
                $archivedCount++;
            }
        }

        // Permintaan teman pending masuk
        $pendingReqs = $this->friendModel->getIncomingRequests($userId);

        return $this->ok([
            'conversations'           => $merged,
            'total_unread'            => $totalUnread,
            'archived_count'          => $archivedCount,
            'pending_friend_requests' => count($pendingReqs),
            'my_id'                   => $userId,
        ]);
    }

    /**
     * POST /api/chat/conversation/pin
     * Body: { type: 'direct'|'marketplace', target_id: int, target_sub_id: int }
     */
    public function pinConversation()
    {
        $userId = $this->uid();
        $type = $this->request->getPost('type') ?: 'direct';
        $targetId = (int)$this->request->getPost('target_id');
        $targetSubId = (int)($this->request->getPost('target_sub_id') ?? 0);

        $res = $this->convSettingModel->togglePin($userId, $type, $targetId, $targetSubId);
        return $this->ok([
            'is_pinned'   => $res['is_pinned'],
            'is_archived' => $res['is_archived'],
            'message'     => $res['is_pinned'] ? 'Obrolan disematkan ke atas' : 'Sematan obrolan dilepas',
        ]);
    }

    /**
     * POST /api/chat/conversation/archive
     * Body: { type: 'direct'|'marketplace', target_id: int, target_sub_id: int }
     */
    public function archiveConversation()
    {
        $userId = $this->uid();
        $type = $this->request->getPost('type') ?: 'direct';
        $targetId = (int)$this->request->getPost('target_id');
        $targetSubId = (int)($this->request->getPost('target_sub_id') ?? 0);

        $res = $this->convSettingModel->toggleArchive($userId, $type, $targetId, $targetSubId);
        return $this->ok([
            'is_archived' => $res['is_archived'],
            'is_pinned'   => $res['is_pinned'],
            'message'     => $res['is_archived'] ? 'Obrolan diarsipkan' : 'Obrolan dikeluarkan dari arsip',
        ]);
    }

    /**
     * POST /api/chat/conversation/delete
     * Body: { type: 'direct'|'marketplace', target_id: int, target_sub_id: int }
     */
    public function deleteConversation()
    {
        $userId = $this->uid();
        $type = $this->request->getPost('type') ?: 'direct';
        $targetId = (int)$this->request->getPost('target_id');
        $targetSubId = (int)($this->request->getPost('target_sub_id') ?? 0);

        $this->convSettingModel->deleteChat($userId, $type, $targetId, $targetSubId);
        return $this->ok([
            'message' => 'Obrolan berhasil dihapus',
        ]);
    }
}
