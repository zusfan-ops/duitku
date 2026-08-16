<?php

namespace App\Controllers;

use App\Models\PosOrderItemModel;
use App\Models\PosOrderModel;
use App\Models\PosProductModel;
use App\Models\SettingModel;

class PublicMenuController extends BaseController
{
    protected SettingModel      $settingModel;
    protected PosProductModel   $productModel;
    protected PosOrderModel     $orderModel;
    protected PosOrderItemModel $orderItemModel;

    public function __construct()
    {
        $this->settingModel   = new SettingModel();
        $this->productModel   = new PosProductModel();
        $this->orderModel     = new PosOrderModel();
        $this->orderItemModel = new PosOrderItemModel();
    }

    /**
     * GET /menu/{slug} or /{slug} — Public Consumer Menu Page
     */
    public function index(string $slug)
    {
        $store = $this->settingModel->findUserByStoreSlug($slug);
        if (!$store) {
            return view('errors/html/error_404', [
                'message' => 'Outlet / Toko "' . esc($slug) . '" tidak ditemukan. Silakan periksa kembali link atau scan QR terbaru.'
            ]);
        }

        $userId     = (int)$store['user_id'];
        $category   = $this->request->getGet('category');
        $search     = $this->request->getGet('search');
        $tableQuery = $this->request->getGet('table') ?: $this->request->getGet('meja');

        $products   = $this->productModel->getForPublicMenu($userId, $category, $search);
        $categories = $this->productModel->getCategories($userId);

        // Group products by category
        $groupedProducts = [];
        foreach ($products as $p) {
            $cat = $p['category'] ?: 'Menu Pilihan';
            $groupedProducts[$cat][] = $p;
        }

        return view('pos/public_menu', [
            'store'           => $store,
            'slug'            => $slug,
            'products'        => $products,
            'groupedProducts' => $groupedProducts,
            'categories'      => array_merge(['Semua'], $categories),
            'selectedCat'     => $category ?: 'Semua',
            'symbol'          => $store['currency_symbol'] ?? 'Rp',
            'tableQuery'      => $tableQuery ? trim($tableQuery) : '',
        ]);
    }

    /**
     * POST /menu/{slug}/order — Customer submits self-order
     */
    public function placeOrder(string $slug)
    {
        $store = $this->settingModel->findUserByStoreSlug($slug);
        if (!$store) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Toko tidak ditemukan.']);
        }

        if (!$store['store_is_open']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Mohon maaf, saat ini toko sedang tutup dan tidak menerima pesanan baru.']);
        }

        $userId = (int)$store['user_id'];
        $json   = $this->request->getJSON(true);

        if (!$json) {
            $rawItems      = $this->request->getPost('items');
            $items         = is_string($rawItems) ? json_decode($rawItems, true) : ($rawItems ?? []);
            $customerName  = trim($this->request->getPost('customer_name') ?? '');
            $customerPhone = trim($this->request->getPost('customer_phone') ?? '');
            $tableNo       = trim($this->request->getPost('table_no') ?? '');
            $notes         = trim($this->request->getPost('notes') ?? '');
        } else {
            $items         = $json['items'] ?? [];
            $customerName  = trim($json['customer_name'] ?? '');
            $customerPhone = trim($json['customer_phone'] ?? '');
            $tableNo       = trim($json['table_no'] ?? '');
            $notes         = trim($json['notes'] ?? '');
        }

        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pilih minimal satu menu untuk memesan.']);
        }

        if (!$tableNo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nomor Meja atau Catatan Lokasi wajib diisi.']);
        }

        $totalAmount = 0.0;
        $totalCost   = 0.0;
        $orderItems  = [];

        foreach ($items as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            $qty       = (int)($item['qty'] ?? 1);
            $itemNotes = trim($item['notes'] ?? '');

            if ($qty <= 0) continue;

            $product = $this->productModel->where('id', $productId)->where('user_id', $userId)->where('is_active', 1)->first();
            if (!$product) {
                return $this->response->setJSON(['success' => false, 'message' => 'Salah satu menu yang dipilih sudah tidak tersedia.']);
            }

            // Check availability
            if (isset($product['is_available']) && !$product['is_available']) {
                return $this->response->setJSON(['success' => false, 'message' => 'Menu "' . $product['name'] . '" sedang habis / tidak tersedia.']);
            }

            $name  = $product['name'];
            $price = (float)$product['selling_price'];
            $cost  = (float)$product['cost_price'];

            // Deduct stock if tracked
            if ((int)$product['stock'] > 0) {
                $this->productModel->deductStock($productId, $qty);
            }

            $subtotal = $price * $qty;
            $totalAmount += $subtotal;
            $totalCost   += ($cost * $qty);

            $orderItems[] = [
                'product_id'   => $productId,
                'product_name' => $name,
                'notes'        => $itemNotes ?: null,
                'qty'          => $qty,
                'price'        => $price,
                'cost_price'   => $cost,
                'subtotal'     => $subtotal,
            ];
        }

        if (empty($orderItems)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Daftar pesanan kosong.']);
        }

        $profit      = $totalAmount - $totalCost;
        $orderNumber = 'ORD-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $orderId = $this->orderModel->insert([
            'user_id'        => $userId,
            'order_number'   => $orderNumber,
            'total_amount'   => $totalAmount,
            'total_cost'     => $totalCost,
            'profit'         => $profit,
            'payment_method' => 'unpaid', // Customer will pay at cashier
            'wallet_id'      => null,
            'cash_received'  => 0.0,
            'change_amount'  => 0.0,
            'customer_name'  => $customerName ?: ('Pelanggan ' . $tableNo),
            'customer_phone' => $customerPhone ?: null,
            'table_no'       => $tableNo,
            'status'         => 'pending', // 'pending', 'processing', 'served_unpaid', 'paid', 'cancelled'
            'order_source'   => 'public_menu',
            'debt_id'        => null,
            'transaction_id' => null,
            'notes'          => $notes ?: null,
            'date'           => date('Y-m-d'),
        ]);

        foreach ($orderItems as &$oi) {
            $oi['order_id'] = $orderId;
            $this->orderItemModel->insert($oi);
        }
        unset($oi);

        return $this->response->setJSON([
            'success'      => true,
            'order_number' => $orderNumber,
            'order_id'     => $orderId,
            'status_url'   => '/menu/' . urlencode($slug) . '/status/' . urlencode($orderNumber),
            'message'      => 'Pesanan Anda berhasil dikirim ke kasir / dapur!',
        ]);
    }

    /**
     * GET /menu/{slug}/status/{orderNumber} — Customer Order Live Tracking Screen
     */
    public function orderStatus(string $slug, string $orderNumber)
    {
        $store = $this->settingModel->findUserByStoreSlug($slug);
        if (!$store) {
            return redirect()->to('/menu/' . $slug);
        }

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        if (!$order || (int)$order['user_id'] !== (int)$store['user_id']) {
            return view('errors/html/error_404', [
                'message' => 'Pesanan dengan kode "' . esc($orderNumber) . '" tidak ditemukan.'
            ]);
        }

        return view('pos/order_status', [
            'store'  => $store,
            'slug'   => $slug,
            'order'  => $order,
            'symbol' => $store['currency_symbol'] ?? 'Rp',
        ]);
    }

    /**
     * GET /menu/{slug}/status-poll/{orderNumber} — Realtime AJAX polling for customer
     */
    public function pollStatus(string $slug, string $orderNumber)
    {
        $store = $this->settingModel->findUserByStoreSlug($slug);
        if (!$store) {
            return $this->response->setJSON(['success' => false, 'message' => 'Store not found']);
        }

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'order'   => [
                'id'             => $order['id'],
                'order_number'   => $order['order_number'],
                'status'         => $order['status'],
                'table_no'       => $order['table_no'],
                'customer_name'  => $order['customer_name'],
                'total_amount'   => (float)$order['total_amount'],
                'payment_method' => $order['payment_method'],
                'updated_at'     => $order['updated_at'],
            ]
        ]);
    }
}
