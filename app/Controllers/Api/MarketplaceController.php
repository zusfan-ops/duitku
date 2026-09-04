<?php

namespace App\Controllers\Api;

use App\Models\MarketplaceCommentModel;
use App\Models\MarketplaceImageModel;
use App\Models\MarketplaceListingModel;
use App\Models\MarketplaceOrderModel;
use App\Models\UserModel;

class MarketplaceController extends ApiController
{
    protected MarketplaceListingModel $listingModel;
    protected MarketplaceImageModel   $imageModel;
    protected MarketplaceCommentModel $commentModel;
    protected MarketplaceOrderModel   $orderModel;
    protected UserModel               $userModel;

    public function __construct()
    {
        $this->listingModel = new MarketplaceListingModel();
        $this->imageModel   = new MarketplaceImageModel();
        $this->commentModel = new MarketplaceCommentModel();
        $this->orderModel   = new MarketplaceOrderModel();
        $this->userModel    = new UserModel();
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
            'listings'   => $listings,
            'categories' => MarketplaceListingModel::getCategoriesList(),
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
        $title       = trim($json['title'] ?? $this->request->getPost('title') ?? '');
        $type        = ($json['type'] ?? $this->request->getPost('type')) === 'rent' ? 'rent' : 'sale';
        $category    = trim($json['category'] ?? $this->request->getPost('category') ?? 'Lainnya');
        $condition   = $json['condition'] ?? $this->request->getPost('condition') ?: 'used_good';
        $price       = $this->amount($json['price'] ?? $this->request->getPost('price') ?? 0);
        $rentPeriod  = trim($json['rent_period'] ?? $this->request->getPost('rent_period') ?? '');
        $location    = trim($json['location'] ?? $this->request->getPost('location') ?? '');
        $whatsapp    = trim($json['whatsapp'] ?? $this->request->getPost('whatsapp') ?? '');
        $thirdParty  = trim($json['third_party_url'] ?? $this->request->getPost('third_party_url') ?? '');
        $description = trim($json['description'] ?? $this->request->getPost('description') ?? '');

        if (strlen($title) < 4) {
            return $this->fail('Judul iklan minimal 4 karakter.');
        }
        if ($price <= 0) {
            return $this->fail('Harga wajib lebih dari 0.');
        }
        if (empty($location)) {
            return $this->fail('Lokasi / Wilayah COD wajib diisi.');
        }

        $baseSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        $slug = $baseSlug . '-' . time();

        $listingId = $this->listingModel->insert([
            'user_id'         => $userId,
            'title'           => $title,
            'slug'            => $slug,
            'type'            => $type,
            'category'        => $category,
            'condition'       => $condition,
            'price'           => $price,
            'rent_period'     => ($type === 'rent' && !empty($rentPeriod)) ? $rentPeriod : null,
            'location'        => $location,
            'whatsapp'        => $whatsapp,
            'third_party_url' => $thirdParty ?: null,
            'description'     => $description,
            'status'          => 'active',
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

        return $this->ok([
            'message'  => 'Pengajuan minat berhasil dikirim ke penjual!',
            'order_id' => $orderId,
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
}
