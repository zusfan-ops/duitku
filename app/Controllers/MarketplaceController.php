<?php

namespace App\Controllers;

use App\Models\MarketplaceCommentModel;
use App\Models\MarketplaceImageModel;
use App\Models\MarketplaceListingModel;
use App\Models\MarketplaceOrderModel;
use App\Models\SettingModel;
use App\Models\UserModel;

class MarketplaceController extends BaseController
{
    protected MarketplaceListingModel $listingModel;
    protected MarketplaceImageModel   $imageModel;
    protected MarketplaceCommentModel $commentModel;
    protected MarketplaceOrderModel   $orderModel;
    protected UserModel               $userModel;
    protected SettingModel            $settingModel;

    public function __construct()
    {
        $this->listingModel = new MarketplaceListingModel();
        $this->imageModel   = new MarketplaceImageModel();
        $this->commentModel = new MarketplaceCommentModel();
        $this->orderModel   = new MarketplaceOrderModel();
        $this->userModel    = new UserModel();
        $this->settingModel = new SettingModel();
    }

    /**
     * Halaman Utama Jual Beli & Sewa
     * GET /marketplace or /jual-beli-sewa
     */
    public function index()
    {
        $userId   = session()->get('user_id');
        $tab      = $this->request->getGet('tab') ?: 'browse'; // 'browse', 'my_listings', 'orders'
        $type     = $this->request->getGet('type');            // 'sale', 'rent', or empty (semua)
        $category = $this->request->getGet('category');
        $search   = $this->request->getGet('search');
        $sort     = $this->request->getGet('sort') ?: 'latest';

        $filters = [
            'type'     => $type,
            'category' => $category,
            'search'   => $search,
            'sort'     => $sort,
            'status'   => 'active',
        ];

        $listings = $this->listingModel->getListings($filters, 60);

        // My listings (if logged in)
        $myListings = [];
        $ordersReceived = [];
        $ordersPlaced   = [];
        $myUsername     = '';
        if ($userId) {
            $myUsername = $this->userModel->ensureUsername($userId);
            $myListings = $this->listingModel->getListings(['user_id' => $userId, 'status' => ''], 100);
            $ordersReceived = $this->orderModel->getOrdersForSeller($userId);
            $ordersPlaced   = $this->orderModel->getOrdersForBuyer($userId);
        }

        $currencySymbol = $userId ? $this->settingModel->get($userId, 'currency_symbol', 'Rp') : 'Rp';

        return view('marketplace/index', [
            'pageTitle'       => 'Jual Beli & Sewa',
            'listings'        => $listings,
            'myListings'      => $myListings,
            'ordersReceived'  => $ordersReceived,
            'ordersPlaced'    => $ordersPlaced,
            'categories'      => MarketplaceListingModel::getCategoriesList(),
            'selectedType'    => $type,
            'selectedCategory'=> $category ?: 'Semua',
            'searchQuery'     => $search,
            'currentSort'     => $sort,
            'activeTab'       => $tab,
            'symbol'          => $currencySymbol,
            'userId'          => $userId,
            'myUsername'      => $myUsername,
        ]);
    }

    /**
     * Form Pasang Iklan Baru
     * GET /marketplace/create
     */
    public function create()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu untuk memasang iklan.');
        }

        $user = $this->userModel->find($userId);
        $this->userModel->ensureUsername($userId);
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return view('marketplace/create', [
            'pageTitle'  => 'Pasang Iklan Jual / Sewa',
            'user'       => $user,
            'symbol'     => $symbol,
            'categories' => MarketplaceListingModel::getCategoriesList(),
        ]);
    }

    /**
     * Simpan Iklan Baru (POST /marketplace/store)
     */
    public function store()
    {
        try {
            $userId = session()->get('user_id');
            if (!$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
            }

            $title       = trim($this->request->getPost('title') ?? '');
            $type        = $this->request->getPost('type') === 'rent' ? 'rent' : 'sale';
            $category    = trim($this->request->getPost('category') ?? 'Lainnya');
            $condition   = $this->request->getPost('condition') ?: 'used_good';
            $price       = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('price') ?? '0');
            $rentPeriod  = trim($this->request->getPost('rent_period') ?? '');
            $location    = trim($this->request->getPost('location') ?? '');
            $whatsapp    = trim($this->request->getPost('whatsapp') ?? '');
            $thirdParty  = trim($this->request->getPost('third_party_url') ?? '');
            
            // Rich WYSIWYG HTML description sanitization
            $rawDesc     = $this->request->getPost('description') ?? '';
            $allowedTags = '<p><br><b><strong><i><em><u><s><del><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><pre><code><hr><a>';
            $cleanDesc   = strip_tags($rawDesc, $allowedTags);
            $cleanDesc   = preg_replace('/<(?:script|iframe|style|object|embed)[^>]*?>.*?<\/(?:script|iframe|style|object|embed)>/si', '', $cleanDesc);
            $cleanDesc   = preg_replace('/<([a-z0-9]+)[^>]*?(on[a-z]+)\s*=[^>]*?>/i', '<$1>', $cleanDesc);
            $cleanDesc   = preg_replace('/href\s*=\s*(["\'])\s*javascript:[^"\']*\1/i', 'href="#"', $cleanDesc);
            $description = trim($cleanDesc);

            if (strlen($title) < 4) {
                return $this->response->setJSON(['success' => false, 'message' => 'Judul iklan minimal 4 karakter.']);
            }
            if ($price <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Harga wajib lebih dari 0.']);
            }
            if (empty($location)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lokasi / Wilayah COD wajib diisi.']);
            }

            // Generate slug
            $baseSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
            $slug = $baseSlug . '-' . time();

            $listingData = [
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
            ];

            $listingId = $this->listingModel->insert($listingData);
            if (!$listingId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan data iklan ke database.']);
            }

            // Upload folder
            $uploadDir = FCPATH . 'uploads/marketplace/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            // Handle uploaded images (multiple files)
            $files = $this->request->getFileMultiple('images');
            if (empty($files)) {
                $allFiles = $this->request->getFiles();
                $files = $allFiles['images'] ?? [];
            }

            $isPrimary = 1;
            $sortOrder = 0;

            if (!empty($files) && is_array($files)) {
                foreach ($files as $file) {
                    if (is_object($file) && method_exists($file, 'isValid') && $file->isValid() && !$file->hasMoved()) {
                        $ext = strtolower($file->getClientExtension());
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic'])) {
                            $newName = 'mkt_' . $listingId . '_' . uniqid() . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
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

            // If user also sent photo URLs or base64 (from camera capture or fallback)
            $extraImages = $this->request->getPost('extra_images');
            if (!empty($extraImages) && is_array($extraImages)) {
                foreach ($extraImages as $base64) {
                    if (preg_match('/^data:image\/(png|jpe?g|webp);base64,(.+)$/i', $base64, $m)) {
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

            return $this->response->setJSON([
                'success'    => true,
                'message'    => 'Iklan berhasil ditayangkan!',
                'listing_id' => $listingId,
                'redirect'   => '/marketplace/item/' . $listingId,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[MarketplaceController::store] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memproses iklan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Halaman Edit Iklan (GET /marketplace/edit/{id})
     */
    public function edit(int $id)
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $listing = $this->listingModel->find($id);
        if (!$listing || (int)$listing['user_id'] !== (int)$userId) {
            return redirect()->to('/marketplace')->with('error', 'Iklan tidak ditemukan atau bukan milik Anda.');
        }

        $images = $this->imageModel->getForListing($id);
        $user = $this->userModel->find($userId);
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return view('marketplace/edit', [
            'pageTitle'  => 'Edit Iklan: ' . esc($listing['title']),
            'listing'    => $listing,
            'images'     => $images,
            'user'       => $user,
            'symbol'     => $symbol,
            'categories' => MarketplaceListingModel::getCategoriesList(),
        ]);
    }

    /**
     * Simpan Pembaruan Iklan (POST /marketplace/update/{id})
     */
    public function update(int $id)
    {
        try {
            $userId = session()->get('user_id');
            if (!$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
            }

            $listing = $this->listingModel->find($id);
            if (!$listing || (int)$listing['user_id'] !== (int)$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Iklan tidak ditemukan atau akses ditolak.']);
            }

            $title       = trim($this->request->getPost('title') ?? '');
            $type        = $this->request->getPost('type') === 'rent' ? 'rent' : 'sale';
            $category    = trim($this->request->getPost('category') ?? 'Lainnya');
            $condition   = $this->request->getPost('condition') ?: 'used_good';
            $price       = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('price') ?? '0');
            $rentPeriod  = trim($this->request->getPost('rent_period') ?? '');
            $location    = trim($this->request->getPost('location') ?? '');
            $whatsapp    = trim($this->request->getPost('whatsapp') ?? '');
            $thirdParty  = trim($this->request->getPost('third_party_url') ?? '');

            // Rich WYSIWYG HTML description sanitization
            $rawDesc     = $this->request->getPost('description') ?? '';
            $allowedTags = '<p><br><b><strong><i><em><u><s><del><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><pre><code><hr><a>';
            $cleanDesc   = strip_tags($rawDesc, $allowedTags);
            $cleanDesc   = preg_replace('/<(?:script|iframe|style|object|embed)[^>]*?>.*?<\/(?:script|iframe|style|object|embed)>/si', '', $cleanDesc);
            $cleanDesc   = preg_replace('/<([a-z0-9]+)[^>]*?(on[a-z]+)\s*=[^>]*?>/i', '<$1>', $cleanDesc);
            $cleanDesc   = preg_replace('/href\s*=\s*(["\'])\s*javascript:[^"\']*\1/i', 'href="#"', $cleanDesc);
            $description = trim($cleanDesc);

            if (strlen($title) < 4) {
                return $this->response->setJSON(['success' => false, 'message' => 'Judul iklan minimal 4 karakter.']);
            }
            if ($price <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Harga wajib lebih dari 0.']);
            }
            if (empty($location)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lokasi / Wilayah COD wajib diisi.']);
            }

            $updateData = [
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
            ];

            $this->listingModel->update($id, $updateData);

            // Handle additional uploaded images if any
            $uploadDir = FCPATH . 'uploads/marketplace/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            $files = $this->request->getFileMultiple('images');
            if (empty($files)) {
                $allFiles = $this->request->getFiles();
                $files = $allFiles['images'] ?? [];
            }

            // Count current images
            $existingCount = $this->imageModel->where('listing_id', $id)->countAllResults();
            $isPrimary = $existingCount === 0 ? 1 : 0;
            $sortOrder = $existingCount;

            if (!empty($files) && is_array($files)) {
                foreach ($files as $file) {
                    if (is_object($file) && method_exists($file, 'isValid') && $file->isValid() && !$file->hasMoved()) {
                        $ext = strtolower($file->getClientExtension());
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic'])) {
                            $newName = 'mkt_' . $id . '_' . uniqid() . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                            $file->move($uploadDir, $newName);

                            $this->imageModel->insert([
                                'listing_id' => $id,
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

            return $this->response->setJSON([
                'success'    => true,
                'message'    => 'Iklan berhasil diperbarui!',
                'redirect'   => '/marketplace/item/' . $id,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[MarketplaceController::update] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memperbarui iklan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Hapus Gambar Iklan Individual (POST /marketplace/image/delete/{id})
     */
    public function deleteImage(int $imageId)
    {
        try {
            $userId = session()->get('user_id');
            if (!$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Sesi berakhir.']);
            }

            $image = $this->imageModel->find($imageId);
            if (!$image) {
                return $this->response->setJSON(['success' => false, 'message' => 'Gambar tidak ditemukan.']);
            }

            // Verify owner
            $listing = $this->listingModel->find($image['listing_id']);
            if (!$listing || (int)$listing['user_id'] !== (int)$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
            }

            // Delete physical file
            $filePath = FCPATH . ltrim($image['image_url'], '/');
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $this->imageModel->delete($imageId);

            // If deleted image was primary, set another image as primary
            if (!empty($image['is_primary'])) {
                $nextImg = $this->imageModel->where('listing_id', $listing['id'])->first();
                if ($nextImg) {
                    $this->imageModel->update($nextImg['id'], ['is_primary' => 1]);
                }
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Gambar berhasil dihapus.']);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus gambar: ' . $e->getMessage()]);
        }
    }

    /**
     * Halaman Detail Produk & Tampilan Publik
     * GET /marketplace/item/{id} or /p/{id}
     */
    public function detail(int $id)
    {
        $listing = $this->listingModel->getListingDetail($id);
        if (!$listing) {
            return view('errors/html/error_404', [
                'message' => 'Produk atau iklan ini sudah tidak tersedia atau telah dihapus oleh pemiliknya.'
            ]);
        }

        // Increment view count
        $this->listingModel->incrementViews($id);

        $userId = session()->get('user_id');
        $isOwner = $userId && ((int)$listing['user_id'] === (int)$userId);

        // Share Link: product full URL
        $shareUrl = site_url('marketplace/item/' . $id);
        $userStoreUrl = site_url('u/' . ($listing['seller_username'] ?: 'toko-' . $listing['user_id']));

        // Release APK info
        $releaseInfo = [
            'latest_version' => 'v1.2.25',
            'github_releases'=> 'https://github.com/zusfan-ops/duitku/releases',
            'apk_download'   => 'https://github.com/zusfan-ops/duitku/releases/latest/download/app-arm64-v8a-release.apk',
        ];

        return view('marketplace/detail', [
            'pageTitle'    => esc($listing['title']) . ' — DuitKu Jual Beli & Sewa',
            'listing'      => $listing,
            'isOwner'      => $isOwner,
            'userId'       => $userId,
            'shareUrl'     => $shareUrl,
            'userStoreUrl' => $userStoreUrl,
            'releaseInfo'  => $releaseInfo,
            'symbol'       => 'Rp',
        ]);
    }

    /**
     * Tambah Komentar Produk
     * POST /marketplace/comment/{id}
     */
    public function comment(int $id)
    {
        try {
            $userId = session()->get('user_id');
            if (!$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Silakan login untuk memberikan komentar atau pertanyaan.']);
            }

            $comment = trim($this->request->getPost('comment') ?? '');
            if (strlen($comment) < 2) {
                return $this->response->setJSON(['success' => false, 'message' => 'Komentar tidak boleh kosong.']);
            }

            $listing = $this->listingModel->find($id);
            if (!$listing) {
                return $this->response->setJSON(['success' => false, 'message' => 'Produk tidak ditemukan.']);
            }

            $this->commentModel->insert([
                'listing_id' => $id,
                'user_id'    => $userId,
                'comment'    => $comment,
            ]);

            $user = $this->userModel->find($userId);
            $avatarData = $user ? json_decode($user['avatar'], true) : ['initials' => 'U', 'color' => '#2D5A27'];

            return $this->response->setJSON([
                'success' => true,
                'comment' => [
                    'user_name'     => $user['name'] ?? 'Pengguna',
                    'user_username' => $user['username'] ?? '',
                    'avatar'        => $avatarData,
                    'comment'       => esc($comment),
                    'created_at'    => date('d M Y H:i'),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengirim komentar: ' . $e->getMessage()]);
        }
    }

    /**
     * Ajukan Minat / Order via Aplikasi
     * POST /marketplace/order/{id}
     */
    public function order(int $id)
    {
        try {
            $buyerId = session()->get('user_id');
            if (!$buyerId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Silakan login untuk memesan atau mengajukan minat.']);
            }

            $listing = $this->listingModel->find($id);
            if (!$listing) {
                return $this->response->setJSON(['success' => false, 'message' => 'Produk tidak ditemukan.']);
            }

            if ((int)$listing['user_id'] === (int)$buyerId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak dapat memesan barang milik sendiri.']);
            }

            $notes = trim($this->request->getPost('notes') ?? '');
            $orderId = $this->orderModel->insert([
                'listing_id' => $id,
                'buyer_id'   => $buyerId,
                'seller_id'  => $listing['user_id'],
                'order_type' => $listing['type'] === 'rent' ? 'rent' : 'buy',
                'price'      => $listing['price'],
                'notes'      => $notes ?: 'Saya berminat untuk transaksi COD atau lewat platform ketiga.',
                'status'     => 'pending',
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pengajuan minat transaksi telah dikirim ke penjual. Penjual akan menghubungi Anda atau Anda dapat langsung menghubunginya via WhatsApp.',
                'order_id'=> $orderId,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal memproses pengajuan: ' . $e->getMessage()]);
        }
    }

    /**
     * Update Status Listing (Active, Sold, Rented, Inactive)
     * POST /marketplace/status/{id}
     */
    public function updateStatus(int $id)
    {
        try {
            $userId = session()->get('user_id');
            $status = $this->request->getPost('status');
            if (!in_array($status, ['active', 'sold', 'rented', 'inactive'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Status tidak valid.']);
            }

            $listing = $this->listingModel->find($id);
            if (!$listing || (int)$listing['user_id'] !== (int)$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
            }

            $this->listingModel->update($id, ['status' => $status]);
            return $this->response->setJSON(['success' => true, 'status' => $status]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengubah status: ' . $e->getMessage()]);
        }
    }

    /**
     * Hapus Iklan
     * POST /marketplace/delete/{id}
     */
    public function delete(int $id)
    {
        try {
            $userId = session()->get('user_id');
            $listing = $this->listingModel->find($id);
            if (!$listing || (int)$listing['user_id'] !== (int)$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Iklan tidak ditemukan atau bukan milik Anda.']);
            }

            $this->listingModel->delete($id);
            return $this->response->setJSON(['success' => true, 'message' => 'Iklan berhasil dihapus.']);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus iklan: ' . $e->getMessage()]);
        }
    }

    /**
     * Halaman Toko / Etalase Publik User
     * GET /u/{username} or /{username}
     */
    public function userStore(string $username)
    {
        $username = strtolower(trim($username));

        // Find user by username or fallback by store_slug or user ID
        $user = $this->userModel->findByUsername($username);
        if (!$user && preg_match('/^(?:toko-)?(\d+)$/', $username, $m)) {
            $user = $this->userModel->find((int)$m[1]);
        }
        if (!$user) {
            // Check setting store_slug fallback
            $storeProfile = $this->settingModel->findUserByStoreSlug($username);
            if ($storeProfile && !empty($storeProfile['user_id'])) {
                $user = $this->userModel->find((int)$storeProfile['user_id']);
            }
        }

        if (!$user) {
            return view('errors/html/error_404', [
                'message' => 'Katalog pengguna atau toko "' . esc($username) . '" tidak ditemukan. Silakan periksa kembali tautan.'
            ]);
        }

        $sellerId = (int)$user['id'];
        $listings = $this->listingModel->getListings(['user_id' => $sellerId, 'status' => 'active'], 100);

        $avatarJson = json_decode($user['avatar'] ?? '', true);
        $avatar = is_array($avatarJson) ? $avatarJson : ['initials' => 'U', 'color' => '#2D5A27'];

        // Get avatar image if any
        $avatarImg = null;
        $avatarImgFile = $this->settingModel->get($sellerId, 'avatar_image');
        if ($avatarImgFile && file_exists(FCPATH . 'uploads/avatars/' . $avatarImgFile)) {
            $avatarImg = '/uploads/avatars/' . $avatarImgFile;
        }

        $shareUrl = site_url('u/' . ($user['username'] ?: 'toko-' . $user['id']));

        return view('marketplace/user_store', [
            'pageTitle'    => 'Katalog Jual & Sewa: ' . esc($user['name']),
            'seller'       => $user,
            'avatar'       => $avatar,
            'avatarImg'    => $avatarImg,
            'listings'     => $listings,
            'shareUrl'     => $shareUrl,
            'symbol'       => 'Rp',
        ]);
    }

    /**
     * Halaman Panduan & Download Rilis APK Android
     * GET /download or /release
     */
    public function downloadPage()
    {
        $releaseInfo = [
            'version'        => 'v1.2.25',
            'release_date'   => 'September 2026',
            'github_releases'=> 'https://github.com/zusfan-ops/duitku/releases',
            'apk_download'   => 'https://github.com/zusfan-ops/duitku/releases/latest/download/app-arm64-v8a-release.apk',
            'file_size'      => '24.8 MB',
        ];

        return view('marketplace/download', [
            'pageTitle'   => 'Unduh Aplikasi DuitKu Android & Panduan Install',
            'releaseInfo' => $releaseInfo,
        ]);
    }
}
