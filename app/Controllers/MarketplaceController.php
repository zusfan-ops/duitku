<?php

namespace App\Controllers;

use App\Models\ChatConversationSettingModel;
use App\Models\DirectChatModel;
use App\Models\MarketplaceCommentModel;
use App\Models\MarketplaceImageModel;
use App\Models\MarketplaceListingModel;
use App\Models\MarketplaceOrderModel;
use App\Models\MarketplaceChatModel;
use App\Models\NotificationModel;
use App\Models\SettingModel;
use App\Models\UserFriendModel;
use App\Models\UserModel;
use App\Services\FcmService;

class MarketplaceController extends BaseController
{
    protected MarketplaceListingModel $listingModel;
    protected MarketplaceImageModel   $imageModel;
    protected MarketplaceCommentModel $commentModel;
    protected MarketplaceOrderModel   $orderModel;
    protected MarketplaceChatModel    $chatModel;
    protected DirectChatModel         $directChatModel;
    protected UserFriendModel         $friendModel;
    protected UserModel               $userModel;
    protected SettingModel            $settingModel;
    protected NotificationModel       $notificationModel;
    protected FcmService              $fcmService;
    protected ChatConversationSettingModel $convSettingModel;

    public function __construct()
    {
        $this->listingModel       = new MarketplaceListingModel();
        $this->imageModel         = new MarketplaceImageModel();
        $this->commentModel       = new MarketplaceCommentModel();
        $this->orderModel         = new MarketplaceOrderModel();
        $this->chatModel          = new MarketplaceChatModel();
        $this->directChatModel    = new DirectChatModel();
        $this->friendModel        = new UserFriendModel();
        $this->userModel          = new UserModel();
        $this->settingModel       = new SettingModel();
        $this->notificationModel  = new NotificationModel();
        $this->fcmService         = new FcmService();
        $this->convSettingModel   = new ChatConversationSettingModel();
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
            'pageTitle'         => 'Jual Beli, Sewa & Jasa',
            'listings'          => $listings,
            'myListings'        => $myListings,
            'ordersReceived'    => $ordersReceived,
            'ordersPlaced'      => $ordersPlaced,
            'categories'        => MarketplaceListingModel::getCategoriesList(),
            'productCategories' => MarketplaceListingModel::getProductCategoriesList(),
            'serviceCategories' => MarketplaceListingModel::getServiceCategoriesList(),
            'rateUnits'         => MarketplaceListingModel::getRateUnitsList(),
            'serviceTypes'      => MarketplaceListingModel::getServiceTypesList(),
            'selectedType'      => $type,
            'selectedCategory'  => $category ?: 'Semua',
            'searchQuery'       => $search,
            'currentSort'       => $sort,
            'activeTab'         => $tab,
            'symbol'            => $currencySymbol,
            'userId'            => $userId,
            'myUsername'        => $myUsername,
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
            'pageTitle'         => 'Pasang Iklan Jual, Sewa & Jasa',
            'user'              => $user,
            'symbol'            => $symbol,
            'productCategories' => MarketplaceListingModel::getProductCategoriesList(),
            'serviceCategories' => MarketplaceListingModel::getServiceCategoriesList(),
            'categories'        => MarketplaceListingModel::getCategoriesList(),
            'rateUnits'         => MarketplaceListingModel::getRateUnitsList(),
            'serviceTypes'      => MarketplaceListingModel::getServiceTypesList(),
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

            $rawType     = $this->request->getPost('type');
            $type        = in_array($rawType, ['sale', 'rent', 'service']) ? $rawType : 'sale';
            $title       = trim($this->request->getPost('title') ?? '');
            $category    = trim($this->request->getPost('category') ?? 'Lainnya');
            $price       = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('price') ?? '0');
            $location    = trim($this->request->getPost('location') ?? '');
            $whatsapp    = trim($this->request->getPost('whatsapp') ?? '');
            $thirdParty  = trim($this->request->getPost('third_party_url') ?? '');

            // Field kondisional sesuai tipe
            $condition        = null;
            $rentPeriod       = null;
            $serviceType      = null;
            $serviceArea      = null;
            $serviceHours     = null;
            $rateUnit         = null;
            $experienceYears  = null;

            if ($type === 'service') {
                $serviceType     = trim($this->request->getPost('service_type') ?? 'panggilan');
                $serviceArea     = trim($this->request->getPost('service_area') ?? '');
                $serviceHours    = trim($this->request->getPost('service_hours') ?? '');
                $rateUnit        = trim($this->request->getPost('rate_unit') ?? 'per_panggilan');
                $experienceYears = trim($this->request->getPost('experience_years') ?? '');
            } else {
                $condition  = $this->request->getPost('condition') ?: 'used_good';
                if ($type === 'rent') {
                    $rentPeriod = trim($this->request->getPost('rent_period') ?? 'bulan');
                }
            }
            
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
                return $this->response->setJSON(['success' => false, 'message' => 'Harga / Tarif wajib lebih dari 0.']);
            }
            if (empty($location)) {
                return $this->response->setJSON(['success' => false, 'message' => ($type === 'service' ? 'Lokasi / Kota asal penyedia jasa wajib diisi.' : 'Lokasi / Wilayah COD wajib diisi.')]);
            }

            // Generate slug
            $baseSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
            $slug = $baseSlug . '-' . time();

            $listingData = [
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
            'pageTitle'         => 'Edit Iklan: ' . esc($listing['title']),
            'listing'           => $listing,
            'images'            => $images,
            'user'              => $user,
            'symbol'            => $symbol,
            'productCategories' => MarketplaceListingModel::getProductCategoriesList(),
            'serviceCategories' => MarketplaceListingModel::getServiceCategoriesList(),
            'categories'        => MarketplaceListingModel::getCategoriesList(),
            'rateUnits'         => MarketplaceListingModel::getRateUnitsList(),
            'serviceTypes'      => MarketplaceListingModel::getServiceTypesList(),
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

            $rawType     = $this->request->getPost('type');
            $type        = in_array($rawType, ['sale', 'rent', 'service']) ? $rawType : 'sale';
            $title       = trim($this->request->getPost('title') ?? '');
            $category    = trim($this->request->getPost('category') ?? 'Lainnya');
            $price       = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('price') ?? '0');
            $location    = trim($this->request->getPost('location') ?? '');
            $whatsapp    = trim($this->request->getPost('whatsapp') ?? '');
            $thirdParty  = trim($this->request->getPost('third_party_url') ?? '');

            // Field kondisional sesuai tipe
            $condition        = null;
            $rentPeriod       = null;
            $serviceType      = null;
            $serviceArea      = null;
            $serviceHours     = null;
            $rateUnit         = null;
            $experienceYears  = null;

            if ($type === 'service') {
                $serviceType     = trim($this->request->getPost('service_type') ?? 'panggilan');
                $serviceArea     = trim($this->request->getPost('service_area') ?? '');
                $serviceHours    = trim($this->request->getPost('service_hours') ?? '');
                $rateUnit        = trim($this->request->getPost('rate_unit') ?? 'per_panggilan');
                $experienceYears = trim($this->request->getPost('experience_years') ?? '');
            } else {
                $condition  = $this->request->getPost('condition') ?: 'used_good';
                if ($type === 'rent') {
                    $rentPeriod = trim($this->request->getPost('rent_period') ?? 'bulan');
                }
            }

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
                return $this->response->setJSON(['success' => false, 'message' => 'Harga / Tarif wajib lebih dari 0.']);
            }
            if (empty($location)) {
                return $this->response->setJSON(['success' => false, 'message' => ($type === 'service' ? 'Lokasi / Kota asal penyedia jasa wajib diisi.' : 'Lokasi / Wilayah COD wajib diisi.')]);
            }

            $updateData = [
                'title'            => $title,
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

            $sellerId   = (int)$listing['user_id'];
            $buyer      = $this->userModel->find($buyerId);
            $buyerName  = $buyer['name'] ?? 'Calon Pembeli';
            $buyerPhone = $buyer['phone'] ?? '';

            // Otomatis buat obrolan pertama di Chat Room
            try {
                $this->chatModel->insert([
                    'listing_id' => $id,
                    'buyer_id'   => $buyerId,
                    'seller_id'  => $sellerId,
                    'sender_id'  => $buyerId,
                    'message'    => $notes ?: 'Halo, saya berminat dengan produk Anda.',
                    'is_read'    => 0,
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Marketplace web auto-chat error: ' . $e->getMessage());
            }

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
                log_message('error', 'Marketplace web order in-app notif error: ' . $e->getMessage());
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
                log_message('error', 'Marketplace web order FCM error: ' . $e->getMessage());
            }

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
     * Update Status Order / Minat (pending, contacted, completed, cancelled)
     * POST /marketplace/order-status/{id}
     */
    public function updateOrderStatus(int $id)
    {
        try {
            $userId = (int)session()->get('user_id');
            $order  = $this->orderModel->find($id);
            if (!$order || (int)$order['seller_id'] !== $userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak atau pesanan tidak ditemukan.']);
            }

            $status = $this->request->getPost('status') ?? ($this->request->getJSON(true)['status'] ?? '');
            if (!in_array($status, ['pending', 'contacted', 'completed', 'cancelled'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Status tidak valid.']);
            }

            $this->orderModel->update($id, ['status' => $status]);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Status pengajuan minat berhasil diperbarui!',
                'status'  => $status,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengubah status: ' . $e->getMessage()]);
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
            'pageTitle'    => 'Toko ' . esc($user['name']),
            'seller'       => $user,
            'avatar'       => $avatar,
            'avatarImg'    => $avatarImg,
            'listings'     => $listings,
            'shareUrl'     => $shareUrl,
            'symbol'       => 'Rp',
        ]);
    }

    /**
     * Halaman Daftar Obrolan (Pesan & Obrolan) di PWA
     * GET /chat or /pesan
     */
    /**
     * Halaman Daftar Obrolan (Pesan & Obrolan) di PWA
     * GET /chat or /pesan
     */
    public function conversations()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login');
        }

        // Ambil pengaturan obrolan user (pin, archive, cleared)
        $convSettings = $this->convSettingModel->getSettingsForUser($userId);

        // 1. Direct chats dengan teman (WhatsApp style)
        $directConvs = $this->directChatModel->getConversations($userId);

        // Ambil semua teman yang sudah disetujui (Accepted)
        $allFriends = $this->friendModel->getFriends($userId);
        $existingPartnerIds = array_column($directConvs, 'partner_id');

        // Tambahkan teman yang belum pernah diajak chat agar langsung muncul di tab Teman (kecuali sudah pernah dibersihkan)
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
        $marketConvs = $this->chatModel->getConversationsForUser($userId);
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

        // Gabungkan seluruh obrolan
        $allConvs = array_merge($normalizedDirect, $normalizedMarket);
        usort($allConvs, function ($a, $b) {
            // Urutkan sematan (pin) terlebih dahulu
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

        // Hitung total diarsipkan
        $archivedCount = 0;
        foreach ($allConvs as $c) {
            if (!empty($c['is_archived'])) {
                $archivedCount++;
            }
        }

        // 3. Permintaan pertemanan masuk
        $incomingRequests = $this->friendModel->getIncomingRequests($userId);

        // 4. Daftar teman yang sudah disetujui
        $friends = $this->friendModel->getFriends($userId);

        // 5. Total unread chat
        $totalUnread = $this->chatModel->getTotalUnreadCount($userId) + $this->directChatModel->getTotalUnreadCount($userId);

        return view('marketplace/conversations', [
            'pageTitle'        => 'Pesan & Obrolan',
            'conversations'    => $allConvs,
            'archivedCount'    => $archivedCount,
            'incomingRequests' => $incomingRequests,
            'friends'          => $friends,
            'totalUnread'      => $totalUnread,
            'userId'           => $userId,
        ]);
    }

    /**
     * Sematkan / Lepas Sematan Percakapan (PWA AJAX)
     * POST /chat/conversation/pin
     */
    public function pinConversation()
    {
        $userId = session()->get('user_id');
        if (!$userId) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $type = $this->request->getPost('type') ?: 'direct';
        $targetId = (int)$this->request->getPost('target_id');
        $targetSubId = (int)($this->request->getPost('target_sub_id') ?? 0);

        $res = $this->convSettingModel->togglePin($userId, $type, $targetId, $targetSubId);
        return $this->response->setJSON([
            'status'      => 'success',
            'is_pinned'   => $res['is_pinned'],
            'is_archived' => $res['is_archived'],
            'message'     => $res['is_pinned'] ? 'Obrolan disematkan ke atas' : 'Sematan obrolan dilepas',
        ]);
    }

    /**
     * Arsipkan / Buka Arsip Percakapan (PWA AJAX)
     * POST /chat/conversation/archive
     */
    public function archiveConversation()
    {
        $userId = session()->get('user_id');
        if (!$userId) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $type = $this->request->getPost('type') ?: 'direct';
        $targetId = (int)$this->request->getPost('target_id');
        $targetSubId = (int)($this->request->getPost('target_sub_id') ?? 0);

        $res = $this->convSettingModel->toggleArchive($userId, $type, $targetId, $targetSubId);
        return $this->response->setJSON([
            'status'      => 'success',
            'is_archived' => $res['is_archived'],
            'is_pinned'   => $res['is_pinned'],
            'message'     => $res['is_archived'] ? 'Obrolan diarsipkan' : 'Obrolan dikeluarkan dari arsip',
        ]);
    }

    /**
     * Hapus Obrolan (PWA AJAX)
     * POST /chat/conversation/delete
     */
    public function deleteConversation()
    {
        $userId = session()->get('user_id');
        if (!$userId) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $type = $this->request->getPost('type') ?: 'direct';
        $targetId = (int)$this->request->getPost('target_id');
        $targetSubId = (int)($this->request->getPost('target_sub_id') ?? 0);

        $this->convSettingModel->deleteChat($userId, $type, $targetId, $targetSubId);
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Obrolan berhasil dihapus',
        ]);
    }

    /**
     * Upload Attachment untuk Chat (Foto / Dokumen)
     * POST /chat/upload
     */
    public function uploadChatAttachment()
    {
        $userId = session()->get('user_id');
        if (!$userId) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak valid']);
        }

        $uploadDir = FCPATH . 'uploads/chats/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);

        $url = '/uploads/chats/' . $newName;
        $mime = $file->getClientMimeType();
        $isImage = str_starts_with($mime, 'image/');

        return $this->response->setJSON([
            'status'   => 'success',
            'url'      => $url,
            'is_image' => $isImage,
            'filename' => $file->getClientName(),
        ]);
    }

    /**
     * Endpoint Polling Total Unread Chat Count
     * GET /marketplace/chat/unread-count
     */
    public function unreadChatCount()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'unread_count' => 0]);
        }

        $marketUnread = $this->chatModel->getTotalUnreadCount($userId);
        $directUnread = $this->directChatModel->getTotalUnreadCount($userId);
        $pendingReqs  = count($this->friendModel->getIncomingRequests($userId));

        return $this->response->setJSON([
            'status'         => 'success',
            'unread_count'   => ($marketUnread + $directUnread + $pendingReqs),
            'chat_unread'    => ($marketUnread + $directUnread),
            'pending_friends'=> $pendingReqs,
        ]);
    }

    /**
     * Cari Teman via Username (AJAX PWA)
     * GET /friends/search?q=...
     */
    public function searchFriends()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.']);
        }

        $query = trim((string)$this->request->getVar('q'));
        $users = $this->friendModel->searchUsers($query, $userId);

        return $this->response->setJSON([
            'status' => 'success',
            'users'  => $users,
        ]);
    }

    /**
     * Kirim Permintaan Pertemanan (AJAX PWA)
     * POST /friends/request
     */
    public function sendFriendRequest()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.']);
        }

        $username = trim((string)$this->request->getVar('username'));
        if (empty($username)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Username tidak boleh kosong.']);
        }

        $res = $this->friendModel->sendRequest($userId, $username);
        if (!$res['success']) {
            return $this->response->setJSON(['status' => 'error', 'message' => $res['message']]);
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

            try {
                $this->notificationModel->insert([
                    'user_id'    => $friendId,
                    'title'      => $notifTitle,
                    'message'    => $notifMsg,
                    'type'       => 'friend_request',
                    'action_url' => '/chat?tab=friends',
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Friend request notif error: ' . $e->getMessage());
            }

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

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $res['message'],
            'friend'  => $res['friend'] ?? null,
        ]);
    }

    /**
     * Respon Permintaan Pertemanan: Terima / Tolak (AJAX PWA)
     * POST /friends/respond
     */
    public function respondFriendRequest()
    {
        $userId    = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.']);
        }

        $requestId = (int)$this->request->getVar('request_id');
        $action    = trim((string)$this->request->getVar('action'));

        if ($requestId <= 0 || !in_array($action, ['accept', 'reject'], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Parameter tidak valid.']);
        }

        $res = $this->friendModel->respondRequest($requestId, $userId, $action);
        if (!$res['success']) {
            return $this->response->setJSON(['status' => 'error', 'message' => $res['message']]);
        }

        if ($action === 'accept' && !empty($res['friend'])) {
            $senderId = (int)$res['friend']['id'];
            $myInfo   = $this->userModel->find($userId);
            $myName   = $myInfo['name'] ?? 'Teman';

            try {
                $this->notificationModel->insert([
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

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $res['message'],
            'action'  => $action,
            'friend'  => $res['friend'] ?? null,
        ]);
    }

    /**
     * Ambil Pesan Direct Chat (AJAX PWA)
     * GET /chat/direct/messages?friend_id=...&after_id=...
     */
    public function directChatMessages()
    {
        $userId   = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $friendId = (int)$this->request->getVar('friend_id');
        $afterId  = (int)($this->request->getVar('after_id') ?? 0);

        if ($friendId <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID Teman tidak valid.']);
        }

        $this->directChatModel->markAsRead($friendId, $userId);
        $messages = $this->directChatModel->getMessages($userId, $friendId, $afterId);
        $friend   = $this->userModel->find($friendId);

        return $this->response->setJSON([
            'status'   => 'success',
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
     * Kirim Pesan Direct Chat (AJAX PWA)
     * POST /chat/direct/send
     */
    public function sendDirectChatMessage()
    {
        $userId   = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $friendId = (int)$this->request->getVar('friend_id');
        $message  = trim((string)$this->request->getVar('message'));

        if ($friendId <= 0 || empty($message)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pesan atau teman tidak valid.']);
        }

        if (!$this->friendModel->isFriend($userId, $friendId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda harus berteman terlebih dahulu untuk mengirim pesan.']);
        }

        $chat = $this->directChatModel->sendMessage($userId, $friendId, $message);
        $sender = $this->userModel->find($userId);
        $senderName = $sender['name'] ?? 'Teman';

        try {
            $this->notificationModel->insert([
                'user_id'    => $friendId,
                'title'      => "💬 {$senderName}",
                'message'    => $message,
                'type'       => 'direct_chat',
                'action_url' => '/chat?direct_user=' . $userId,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Direct chat notif error: ' . $e->getMessage());
        }

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

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Pesan terkirim',
            'chat'    => $chat,
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

    /**
     * Hapus Pesanan / Minat Masuk
     * POST /marketplace/order/delete/{id}
     */
    public function deleteOrder(int $id)
    {
        try {
            $userId = session()->get('user_id');
            if (!$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
            }

            $order = $this->orderModel->find($id);
            if (!$order) {
                return $this->response->setJSON(['success' => false, 'message' => 'Pesanan atau minat tidak ditemukan.']);
            }

            if ((int)$order['seller_id'] !== (int)$userId && (int)$order['buyer_id'] !== (int)$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki hak untuk menghapus pesanan ini.']);
            }

            $this->orderModel->delete($id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pesanan minat berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus pesanan: ' . $e->getMessage()]);
        }
    }

    /**
     * Ambil Riwayat Pesan Chat
     * GET /marketplace/chat/messages?listing_id=X&buyer_id=Y
     */
    public function chatMessages()
    {
        try {
            $userId = session()->get('user_id');
            if (!$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Silakan login.']);
            }

            $listingId = (int)$this->request->getGet('listing_id');
            $buyerId   = (int)$this->request->getGet('buyer_id');
            $afterId   = (int)($this->request->getGet('after_id') ?? 0);

            if ($listingId <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'ID Produk tidak valid.']);
            }

            $listing = $this->listingModel->find($listingId);
            if (!$listing) {
                return $this->response->setJSON(['success' => false, 'message' => 'Produk tidak ditemukan.']);
            }

            $sellerId = (int)$listing['user_id'];
            if ($userId === $sellerId) {
                if ($buyerId <= 0) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Pilih calon pembeli untuk melihat obrolan.']);
                }
            } else {
                $buyerId = $userId;
            }

            // Tandai sudah dibaca
            $this->chatModel->markAsRead($listingId, $buyerId, $userId);

            $messages = $this->chatModel->getMessages($listingId, $buyerId, $afterId);
            $buyer    = $this->userModel->find($buyerId);
            $seller   = $this->userModel->find($sellerId);

            // Format buyer phone for WA if exists
            $buyerPhoneClean = '';
            if (!empty($buyer['phone'])) {
                $p = preg_replace('/[^0-9]/', '', $buyer['phone']);
                $buyerPhoneClean = str_starts_with($p, '0') ? '62' . substr($p, 1) : $p;
            }

            $sellerPhoneClean = '';
            if (!empty($seller['phone'])) {
                $p = preg_replace('/[^0-9]/', '', $seller['phone']);
                $sellerPhoneClean = str_starts_with($p, '0') ? '62' . substr($p, 1) : $p;
            }

            return $this->response->setJSON([
                'success'  => true,
                'messages' => $messages,
                'listing'  => [
                    'id'    => $listingId,
                    'title' => $listing['title'],
                    'price' => $listing['price'],
                ],
                'buyer'    => [
                    'id'    => $buyerId,
                    'name'  => $buyer['name'] ?? 'Pembeli',
                    'phone' => $buyerPhoneClean,
                ],
                'seller'   => [
                    'id'    => $sellerId,
                    'name'  => $seller['name'] ?? 'Penjual',
                    'phone' => $sellerPhoneClean,
                ],
                'my_id'    => (int)$userId,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal memuat pesan: ' . $e->getMessage()]);
        }
    }

    /**
     * Kirim Pesan Chat Baru
     * POST /marketplace/chat/send
     */
    public function sendChatMessage()
    {
        try {
            $userId = session()->get('user_id');
            if (!$userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
            }

            $listingId = (int)($this->request->getPost('listing_id') ?? 0);
            $message   = trim($this->request->getPost('message') ?? '');

            if ($listingId <= 0 || $message === '') {
                return $this->response->setJSON(['success' => false, 'message' => 'Pesan tidak boleh kosong.']);
            }

            $listing = $this->listingModel->find($listingId);
            if (!$listing) {
                return $this->response->setJSON(['success' => false, 'message' => 'Produk tidak ditemukan.']);
            }

            $sellerId = (int)$listing['user_id'];
            $buyerId  = (int)($this->request->getPost('buyer_id') ?? 0);

            if ($userId === $sellerId) {
                if ($buyerId <= 0) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Tentukan calon pembeli tujuan.']);
                }
                $recipientId = $buyerId;
            } else {
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

            // In-app notifikasi penerima
            try {
                $this->notificationModel->insert([
                    'title'      => '💬 Pesan Baru: ' . $listing['title'],
                    'message'    => "{$senderName}: {$message}",
                    'type'       => 'info',
                    'target'     => 'user',
                    'user_id'    => $recipientId,
                    'action_url' => '/marketplace?tab=orders',
                ]);
            } catch (\Throwable $e) {}

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pesan berhasil dikirim.',
                'chat'    => [
                    'id'          => $chatId,
                    'listing_id'  => $listingId,
                    'buyer_id'    => $buyerId,
                    'seller_id'   => $sellerId,
                    'sender_id'   => $userId,
                    'sender_name' => $senderName,
                    'message'     => esc($message),
                    'created_at'  => date('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengirim pesan: ' . $e->getMessage()]);
        }
    }
}
