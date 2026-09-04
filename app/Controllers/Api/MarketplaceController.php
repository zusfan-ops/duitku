<?php

namespace App\Controllers\Api;

use App\Models\MarketplaceChatModel;
use App\Models\MarketplaceCommentModel;
use App\Models\MarketplaceImageModel;
use App\Models\MarketplaceListingModel;
use App\Models\MarketplaceOrderModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Services\FcmService;

class MarketplaceController extends ApiController
{
    protected MarketplaceListingModel $listingModel;
    protected MarketplaceImageModel   $imageModel;
    protected MarketplaceCommentModel $commentModel;
    protected MarketplaceOrderModel   $orderModel;
    protected MarketplaceChatModel    $chatModel;
    protected UserModel               $userModel;
    protected NotificationModel       $notificationModel;
    protected FcmService              $fcmService;

    public function __construct()
    {
        $this->listingModel       = new MarketplaceListingModel();
        $this->imageModel         = new MarketplaceImageModel();
        $this->commentModel       = new MarketplaceCommentModel();
        $this->orderModel         = new MarketplaceOrderModel();
        $this->chatModel          = new MarketplaceChatModel();
        $this->userModel          = new UserModel();
        $this->notificationModel  = new NotificationModel();
        $this->fcmService         = new FcmService();
    }

    /**
     * GET /api/marketplace
     */
    public function index()
    {
        $type     = $this->request->getGet('type');
        $category = $this->request->getGet('category');
        $search   = $this->request->getGet('search');
        $sort     = $this->request->getGet('sort') ?: 'latest';
        $userId   = $this->request->getGet('user_id');

        $filters = [
            'type'     => $type,
            'category' => $category,
            'search'   => $search,
            'sort'     => $sort,
            'user_id'  => $userId,
            'status'   => 'active',
        ];

        $listings = $this->listingModel->getListings($filters, 60);

        return $this->ok([
            'listings'           => $listings,
            'categories'         => MarketplaceListingModel::getCategoriesList(),
            'product_categories' => MarketplaceListingModel::getProductCategoriesList(),
            'service_categories' => MarketplaceListingModel::getServiceCategoriesList(),
            'rate_units'         => MarketplaceListingModel::getRateUnitsList(),
            'service_types'      => MarketplaceListingModel::getServiceTypesList(),
        ]);
    }

    /**
     * GET /api/marketplace/{id}
     */
    public function show(int $id)
    {
        $listing = $this->listingModel->getListingDetail($id);
        if (!$listing) {
            return $this->fail('Produk tidak ditemukan atau sudah dihapus.');
        }

        $this->listingModel->incrementViews($id);

        $currentUserId = $this->uid();
        $isOwner = ((int)$listing['user_id'] === $currentUserId);

        return $this->ok([
            'listing'  => $listing,
            'is_owner' => $isOwner,
            'share_url'=> site_url('marketplace/item/' . $id),
            'safety_notice' => [
                'title' => 'Panduan Transaksi Aman Anti-Penipuan',
                'tips'  => [
                    'DILARANG KERAS mentransfer uang muka (DP) kepada penjual yang belum dikenal.',
                    'Dianjurkan selalu COD (Ketemu Langsung) di tempat umum untuk cek fisik barang.',
                    'Gunakan Shopee / Tokopedia jika transaksi jarak jauh agar pembayaran aman bergaransi.',
                ],
            ],
        ]);
    }

    /**
     * POST /api/marketplace/store
     */
    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true);

        // Can receive JSON or multipart
        $rawType     = $json['type'] ?? $this->request->getPost('type') ?? 'sale';
        $type        = in_array($rawType, ['sale', 'rent', 'service']) ? $rawType : 'sale';
        $title       = trim($json['title'] ?? $this->request->getPost('title') ?? '');
        $category    = trim($json['category'] ?? $this->request->getPost('category') ?? 'Lainnya');
        $price       = $this->amount($json['price'] ?? $this->request->getPost('price') ?? 0);
        $location    = trim($json['location'] ?? $this->request->getPost('location') ?? '');
        $whatsapp    = trim($json['whatsapp'] ?? $this->request->getPost('whatsapp') ?? '');
        $thirdParty  = trim($json['third_party_url'] ?? $this->request->getPost('third_party_url') ?? '');
        $description = trim($json['description'] ?? $this->request->getPost('description') ?? '');

        // Specific fields
        $condition        = null;
        $rentPeriod       = null;
        $serviceType      = null;
        $serviceArea      = null;
        $serviceHours     = null;
        $rateUnit         = null;
        $experienceYears  = null;

        if ($type === 'service') {
            $serviceType     = trim($json['service_type'] ?? $this->request->getPost('service_type') ?? 'panggilan');
            $serviceArea     = trim($json['service_area'] ?? $this->request->getPost('service_area') ?? '');
            $serviceHours    = trim($json['service_hours'] ?? $this->request->getPost('service_hours') ?? '');
            $rateUnit        = trim($json['rate_unit'] ?? $this->request->getPost('rate_unit') ?? 'per_panggilan');
            $experienceYears = trim($json['experience_years'] ?? $this->request->getPost('experience_years') ?? '');
        } else {
            $condition  = $json['condition'] ?? $this->request->getPost('condition') ?: 'used_good';
            if ($type === 'rent') {
                $rentPeriod = trim($json['rent_period'] ?? $this->request->getPost('rent_period') ?? 'bulan');
            }
        }

        if (strlen($title) < 4) {
            return $this->fail('Judul iklan minimal 4 karakter.');
        }
        if ($price <= 0) {
            return $this->fail('Harga / Tarif wajib lebih dari 0.');
        }
        if (empty($location)) {
            return $this->fail($type === 'service' ? 'Lokasi / Kota asal penyedia jasa wajib diisi.' : 'Lokasi / Wilayah COD wajib diisi.');
        }

        $baseSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        $slug = $baseSlug . '-' . time();

        $listingId = $this->listingModel->insert([
            'user_id'          => $userId,
            'title'            => $title,
            'slug'             => $slug,
            'type'             => $type,
            'category'         => $category,
            'condition'        => $condition,
            'price'            => $price,
            'rent_period'      => $rentPeriod,
            'service_type'     => $serviceType,
            'service_area'     => $serviceArea,
            'service_hours'    => $serviceHours,
            'rate_unit'        => $rateUnit,
            'experience_years' => $experienceYears,
            'location'         => $location,
            'whatsapp'         => $whatsapp,
            'third_party_url'  => $thirdParty ?: null,
            'description'      => $description,
            'status'           => 'active',
        ]);

        if (!$listingId) {
            return $this->fail('Gagal menyimpan iklan.');
        }

        $uploadDir = FCPATH . 'uploads/marketplace/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $isPrimary = 1;
        $sortOrder = 0;

        // Base64 images array
        $imagesBase64 = $json['images'] ?? [];
        if (is_array($imagesBase64)) {
            foreach ($imagesBase64 as $img) {
                if (preg_match('/^data:image\/(png|jpe?g|webp);base64,(.+)$/i', $img, $m)) {
                    $raw = base64_decode($m[2]);
                    if ($raw !== false) {
                        $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
                        $newName = 'mkt_' . $listingId . '_' . uniqid() . '.' . $ext;
                        file_put_contents($uploadDir . $newName, $raw);

                        $this->imageModel->insert([
                            'listing_id' => $listingId,
                            'image_url'  => '/uploads/marketplace/' . $newName,
                            'is_primary' => $isPrimary,
                            'sort_order' => $sortOrder,
                        ]);
                        $isPrimary = 0;
                        $sortOrder++;
                    }
                }
            }
        }

        // Multipart files if any
        $files = $this->request->getFiles()['images'] ?? [];
        if (!empty($files)) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $ext = strtolower($file->getClientExtension());
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $newName = 'mkt_' . $listingId . '_' . uniqid() . '.' . $ext;
                        $file->move($uploadDir, $newName);

                        $this->imageModel->insert([
                            'listing_id' => $listingId,
                            'image_url'  => '/uploads/marketplace/' . $newName,
                            'is_primary' => $isPrimary,
                            'sort_order' => $sortOrder,
                        ]);
                        $isPrimary = 0;
                        $sortOrder++;
                    }
                }
            }
        }

        return $this->ok([
            'message'    => 'Iklan berhasil ditayangkan!',
            'listing_id' => $listingId,
        ]);
    }

    /**
     * POST /api/marketplace/comment/{id}
     */
    public function comment(int $id)
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $comment = trim($json['comment'] ?? $this->request->getPost('comment') ?? '');

        if (strlen($comment) < 2) {
            return $this->fail('Komentar tidak boleh kosong.');
        }

        $listing = $this->listingModel->find($id);
        if (!$listing) {
            return $this->fail('Produk tidak ditemukan.');
        }

        $commentId = $this->commentModel->insert([
            'listing_id' => $id,
            'user_id'    => $userId,
            'comment'    => $comment,
        ]);

        $user = $this->userModel->find($userId);

        return $this->ok([
            'message' => 'Komentar berhasil dikirim!',
            'comment' => [
                'id'         => $commentId,
                'user_name'  => $user['name'] ?? 'Pengguna',
                'comment'    => $comment,
                'created_at' => date('d M Y H:i'),
            ],
        ]);
    }

    /**
     * POST /api/marketplace/order/{id}
     */
    public function order(int $id)
    {
        $buyerId = $this->uid();
        $listing = $this->listingModel->find($id);
        if (!$listing) {
            return $this->fail('Produk tidak ditemukan.');
        }

        if ((int)$listing['user_id'] === $buyerId) {
            return $this->fail('Anda tidak dapat memesan barang milik sendiri.');
        }

        $json  = $this->request->getJSON(true) ?? [];
        $notes = trim($json['notes'] ?? $this->request->getPost('notes') ?? '');

        $orderId = $this->orderModel->insert([
            'listing_id' => $id,
            'buyer_id'   => $buyerId,
            'seller_id'  => $listing['user_id'],
            'order_type' => $listing['type'] === 'rent' ? 'rent' : 'buy',
            'price'      => $listing['price'],
            'notes'      => $notes ?: 'Pengajuan minat transaksi via aplikasi.',
            'status'     => 'pending',
        ]);

        $sellerId   = (int)$listing['user_id'];
        $buyer      = $this->userModel->find($buyerId);
        $buyerName  = $buyer['name'] ?? 'Calon Pembeli';
        $buyerPhone = $buyer['phone'] ?? '';

        // 1. Simpan In-App Notification untuk penjual
        try {
            $this->notificationModel->insert([
                'title'      => '🛒 Minat Baru: ' . $listing['title'],
                'message'    => "{$buyerName} mengajukan minat pada produk Anda! Catatan: \"{$notes}\"",
                'type'       => 'info',
                'target'     => 'user',
                'user_id'    => $sellerId,
                'action_url' => '/marketplace?tab=orders',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Marketplace order in-app notif error: ' . $e->getMessage());
        }

        // 2. Kirim Push Notification FCM ke topik HP penjual
        try {
            if ($this->fcmService->isConfigured()) {
                $this->fcmService->sendToTopic(
                    "user_{$sellerId}",
                    '🛒 Minat Baru: ' . $listing['title'],
                    "{$buyerName} mengajukan minat pada produk Anda. Buka aplikasi DuitKu untuk hubungi pembeli via WhatsApp!",
                    [
                        'type'        => 'marketplace_order',
                        'order_id'    => (string)$orderId,
                        'listing_id'  => (string)$id,
                        'buyer_name'  => (string)$buyerName,
                        'buyer_phone' => (string)$buyerPhone,
                        'action_url'  => '/marketplace?tab=orders',
                    ]
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'Marketplace order FCM error: ' . $e->getMessage());
        }

        // Otomatis inisialisasi percakapan di chat room
        try {
            $this->chatModel->insert([
                'listing_id' => $id,
                'buyer_id'   => $buyerId,
                'seller_id'  => $sellerId,
                'sender_id'  => $buyerId,
                'message'    => $notes ?: 'Halo, saya berminat dengan produk ini. Apakah masih tersedia?',
                'is_read'    => 0,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Auto first chat insert error: ' . $e->getMessage());
        }

        return $this->ok([
            'message'    => 'Pengajuan minat berhasil dikirim ke penjual!',
            'order_id'   => $orderId,
            'listing_id' => $id,
            'buyer_id'   => $buyerId,
            'seller_id'  => $sellerId,
        ]);
    }

    /**
     * GET /api/marketplace/my-listings
     */
    public function myListings()
    {
        $userId = $this->uid();
        $listings       = $this->listingModel->getListings(['user_id' => $userId, 'status' => ''], 100);
        $ordersReceived = $this->orderModel->getOrdersForSeller($userId);
        $ordersPlaced   = $this->orderModel->getOrdersForBuyer($userId);
        $username       = $this->userModel->ensureUsername($userId);

        return $this->ok([
            'listings'        => $listings,
            'orders_received' => $ordersReceived,
            'orders_placed'   => $ordersPlaced,
            'my_store_url'    => site_url('u/' . $username),
            'username'        => $username,
        ]);
    }

    /**
     * POST /api/marketplace/update/{id}
     */
    public function update(int $id)
    {
        $userId = $this->uid();
        $listing = $this->listingModel->find($id);
        if (!$listing || (int)$listing['user_id'] !== $userId) {
            return $this->fail('Akses ditolak atau iklan tidak ditemukan.');
        }

        $json = $this->request->getJSON(true) ?? [];
        $title       = trim($json['title'] ?? $this->request->getPost('title') ?? '');
        $type        = ($json['type'] ?? $this->request->getPost('type')) === 'rent' ? 'rent' : 'sale';
        $category    = trim($json['category'] ?? $this->request->getPost('category') ?? 'Lainnya');
        $condition   = $json['condition'] ?? $this->request->getPost('condition') ?: 'used_good';
        $price       = (float)($json['price'] ?? $this->request->getPost('price') ?? 0);
        $rentPeriod  = trim($json['rent_period'] ?? $this->request->getPost('rent_period') ?? '');
        $location    = trim($json['location'] ?? $this->request->getPost('location') ?? '');
        $whatsapp    = trim($json['whatsapp'] ?? $this->request->getPost('whatsapp') ?? '');
        $thirdParty  = trim($json['third_party_url'] ?? $this->request->getPost('third_party_url') ?? '');
        $description = trim($json['description'] ?? $this->request->getPost('description') ?? '');

        if (strlen($title) < 4) return $this->fail('Judul minimal 4 karakter.');
        if ($price <= 0) return $this->fail('Harga harus lebih dari 0.');

        $this->listingModel->update($id, [
            'title'           => $title,
            'type'            => $type,
            'category'        => $category,
            'condition'       => $condition,
            'price'           => $price,
            'rent_period'     => ($type === 'rent' && !empty($rentPeriod)) ? $rentPeriod : null,
            'location'        => $location,
            'whatsapp'        => $whatsapp,
            'third_party_url' => $thirdParty ?: null,
            'description'     => $description,
        ]);

        return $this->ok(['message' => 'Iklan berhasil diperbarui!']);
    }

    /**
     * POST /api/marketplace/status/{id}
     */
    public function updateStatus(int $id)
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $status = $json['status'] ?? $this->request->getPost('status');

        if (!in_array($status, ['active', 'sold', 'rented', 'inactive'])) {
            return $this->fail('Status tidak valid.');
        }

        $listing = $this->listingModel->find($id);
        if (!$listing || (int)$listing['user_id'] !== $userId) {
            return $this->fail('Akses ditolak.');
        }

        $this->listingModel->update($id, ['status' => $status]);
        return $this->ok(['status' => $status]);
    }

    /**
     * POST /api/marketplace/delete/{id}
     */
    public function delete(int $id)
    {
        $userId = $this->uid();
        $listing = $this->listingModel->find($id);
        if (!$listing || (int)$listing['user_id'] !== $userId) {
            return $this->fail('Akses ditolak atau iklan tidak ditemukan.');
        }

        $this->listingModel->delete($id);
        return $this->ok(['message' => 'Iklan berhasil dihapus.']);
    }

    /**
     * POST /api/marketplace/order-status/{id}
     */
    public function updateOrderStatus(int $id)
    {
        $userId = $this->uid();
        $order  = $this->orderModel->find($id);
        if (!$order) {
            return $this->fail('Pengajuan minat / pesanan tidak ditemukan.');
        }

        if ((int)$order['seller_id'] !== $userId) {
            return $this->fail('Akses ditolak. Anda bukan pemilik produk.');
        }

        $json   = $this->request->getJSON(true) ?? [];
        $status = $json['status'] ?? $this->request->getPost('status');
        if (!in_array($status, ['pending', 'contacted', 'completed', 'cancelled'])) {
            return $this->fail('Status tidak valid.');
        }

        $this->orderModel->update($id, ['status' => $status]);
        return $this->ok([
            'message' => 'Status pengajuan minat berhasil diperbarui!',
            'status'  => $status,
        ]);
    }

    /**
     * POST /api/marketplace/order/delete/{id}
     */
    public function deleteOrder(int $id)
    {
        $userId = $this->uid();
        $order  = $this->orderModel->find($id);
        if (!$order) {
            return $this->fail('Pengajuan minat / pesanan tidak ditemukan.');
        }

        if ((int)$order['seller_id'] !== $userId && (int)$order['buyer_id'] !== $userId) {
            return $this->fail('Akses ditolak.');
        }

        $this->orderModel->delete($id);
        return $this->ok(['message' => 'Pesanan minat berhasil dihapus.']);
    }

    /**
     * GET /api/marketplace/chat/messages
     */
    public function chatMessages()
    {
        try {
            $userId    = $this->uid();
            $this->chatModel->ensureTable();
            $listingId = (int)$this->request->getGet('listing_id');
            if ($listingId <= 0) {
                return $this->fail('Parameter listing_id wajib diisi.');
            }

            $listing = $this->listingModel->find($listingId);
            if (!$listing) {
                return $this->fail('Produk tidak ditemukan.');
            }

            $sellerId = (int)$listing['user_id'];
            $buyerId  = (int)($this->request->getGet('buyer_id') ?? 0);

            // Jika dipanggil oleh pembeli, buyer_id otomatis userId
            if ($userId !== $sellerId) {
                $buyerId = $userId;
            }

            if ($buyerId <= 0) {
                return $this->fail('Parameter buyer_id tidak valid.');
            }

            // Akses hanya untuk penjual atau pembeli
            if ($userId !== $sellerId && $userId !== $buyerId) {
                return $this->fail('Akses chat ditolak.');
            }

            $afterId  = (int)($this->request->getGet('after_id') ?? 0);
            $messages = $this->chatModel->getMessages($listingId, $buyerId, $afterId);

            // Tandai pesan sebagai sudah dibaca
            $this->chatModel->markAsRead($listingId, $buyerId, $userId);

            $buyer  = $this->userModel->find($buyerId);
            $seller = $this->userModel->find($sellerId);
            $images = $this->imageModel->where('listing_id', $listingId)->orderBy('is_primary', 'DESC')->findAll();

            return $this->ok([
                'messages' => $messages,
                'listing'  => [
                    'id'            => (int)$listing['id'],
                    'title'         => $listing['title'],
                    'price'         => (float)$listing['price'],
                    'type'          => $listing['type'],
                    'status'        => $listing['status'],
                    'primary_image' => !empty($images) ? $images[0]['image_url'] : null,
                ],
                'buyer'    => [
                    'id'       => $buyerId,
                    'name'     => $buyer['name'] ?? 'Pembeli',
                    'phone'    => $buyer['phone'] ?? '',
                    'username' => $buyer['username'] ?? '',
                ],
                'seller'   => [
                    'id'       => $sellerId,
                    'name'     => $seller['name'] ?? 'Penjual',
                    'phone'    => $seller['phone'] ?? '',
                    'username' => $seller['username'] ?? '',
                ],
                'my_id'    => $userId,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'chatMessages error: ' . $e->getMessage());
            return $this->fail('Gagal memuat pesan: ' . $e->getMessage());
        }
    }

    /**
     * POST /api/marketplace/chat/send
     */
    public function sendChatMessage()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $listingId = (int)($json['listing_id'] ?? $this->request->getPost('listing_id') ?? 0);
        $message   = trim($json['message'] ?? $this->request->getPost('message') ?? '');

        if ($listingId <= 0) {
            return $this->fail('ID Produk tidak valid.');
        }

        if ($message === '') {
            return $this->fail('Pesan tidak boleh kosong.');
        }

        $listing = $this->listingModel->find($listingId);
        if (!$listing) {
            return $this->fail('Produk tidak ditemukan.');
        }

        $sellerId = (int)$listing['user_id'];
        $buyerId  = (int)($json['buyer_id'] ?? $this->request->getPost('buyer_id') ?? 0);

        if ($userId === $sellerId) {
            // Penjual mengirim pesan ke pembeli tertentu
            if ($buyerId <= 0) {
                return $this->fail('Tentukan buyer_id untuk mengirim pesan.');
            }
            $recipientId = $buyerId;
        } else {
            // Pembeli mengirim pesan ke penjual
            $buyerId = $userId;
            $recipientId = $sellerId;
        }

        $chatId = $this->chatModel->insert([
            'listing_id' => $listingId,
            'buyer_id'   => $buyerId,
            'seller_id'  => $sellerId,
            'sender_id'  => $userId,
            'message'    => $message,
            'is_read'    => 0,
        ]);

        $sender = $this->userModel->find($userId);
        $senderName = $sender['name'] ?? 'Pengguna';

        // 1. In-App Notification untuk penerima
        try {
            $this->notificationModel->insert([
                'title'      => '💬 Pesan Baru dari ' . $senderName,
                'message'    => "Pesan terkait \"{$listing['title']}\": {$message}",
                'type'       => 'info',
                'target'     => 'user',
                'user_id'    => $recipientId,
                'action_url' => '/marketplace?tab=chat&listing_id=' . $listingId . '&buyer_id=' . $buyerId,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Chat in-app notif error: ' . $e->getMessage());
        }

        // 2. Push Notification FCM untuk penerima
        try {
            if ($this->fcmService->isConfigured()) {
                $this->fcmService->sendToTopic(
                    "user_{$recipientId}",
                    "💬 Chat dari {$senderName}",
                    "{$listing['title']}: {$message}",
                    [
                        'type'        => 'marketplace_chat',
                        'listing_id'  => (string)$listingId,
                        'buyer_id'    => (string)$buyerId,
                        'seller_id'   => (string)$sellerId,
                        'sender_name' => (string)$senderName,
                        'action_url'  => '/marketplace?tab=chat&listing_id=' . $listingId . '&buyer_id=' . $buyerId,
                    ]
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'Chat FCM error: ' . $e->getMessage());
        }

        return $this->ok([
            'message' => 'Pesan berhasil dikirim!',
            'chat'    => [
                'id'              => $chatId,
                'listing_id'      => $listingId,
                'buyer_id'        => $buyerId,
                'seller_id'       => $sellerId,
                'sender_id'       => $userId,
                'sender_name'     => $senderName,
                'message'         => $message,
                'created_at'      => date('Y-m-d H:i:s'),
                'is_read'         => 0,
            ],
        ]);
    }

    /**
     * GET /api/marketplace/chat/conversations
     */
    public function chatConversations()
    {
        try {
            $userId = $this->uid();
            $conversations = $this->chatModel->getConversationsForUser($userId);
            return $this->ok([
                'conversations' => $conversations,
                'my_id'         => $userId,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'chatConversations API error: ' . $e->getMessage());
            return $this->ok([
                'conversations' => [],
                'my_id'         => $this->uid(),
            ]);
        }
    }
}
