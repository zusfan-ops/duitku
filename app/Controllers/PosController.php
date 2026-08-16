<?php

namespace App\Controllers;

use App\Models\DebtModel;
use App\Models\PosOrderItemModel;
use App\Models\PosOrderModel;
use App\Models\PosProductModel;
use App\Models\PosShiftModel;
use App\Models\SettingModel;
use App\Models\TransactionModel;
use App\Models\WalletModel;

class PosController extends BaseController
{
    protected PosProductModel   $productModel;
    protected PosOrderModel     $orderModel;
    protected PosOrderItemModel $orderItemModel;
    protected PosShiftModel     $shiftModel;
    protected SettingModel      $settingModel;
    protected WalletModel       $walletModel;
    protected TransactionModel  $txModel;
    protected DebtModel         $debtModel;

    public function __construct()
    {
        $this->productModel   = new PosProductModel();
        $this->orderModel     = new PosOrderModel();
        $this->orderItemModel = new PosOrderItemModel();
        $this->shiftModel     = new PosShiftModel();
        $this->settingModel   = new SettingModel();
        $this->walletModel    = new WalletModel();
        $this->txModel        = new TransactionModel();
        $this->debtModel      = new DebtModel();
    }

    // GET /pos — Main Mobile-friendly Cashier Screen
    public function index()
    {
        $userId     = session()->get('user_id');
        $category   = $this->request->getGet('category');
        $search     = $this->request->getGet('search');

        $products   = $this->productModel->getForCashier($userId, $category, $search);
        $categories = $this->productModel->getCategories($userId);
        $summary    = $this->orderModel->getTodaySummary($userId);
        $walletData = $this->walletModel->getWithBalances($userId);
        $symbol     = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return view('pos/index', [
            'pageTitle'  => 'Kasir Mini POS',
            'products'   => $products,
            'categories' => array_merge(['Semua'], $categories),
            'summary'    => $summary,
            'wallets'    => $walletData['wallets'] ?? [],
            'symbol'     => $symbol,
        ]);
    }

    // POST /pos/checkout
    public function checkout()
    {
        $userId = session()->get('user_id');
        $json   = $this->request->getJSON(true);

        if (!$json) {
            // Check form post
            $rawItems = $this->request->getPost('items');
            $items    = is_string($rawItems) ? json_decode($rawItems, true) : ($rawItems ?? []);
            $paymentMethod = $this->request->getPost('payment_method') ?? 'cash';
            $walletId      = (int)($this->request->getPost('wallet_id') ?? 0) ?: null;
            $cashReceived  = (float)($this->request->getPost('cash_received') ?? 0);
            $customerName  = trim($this->request->getPost('customer_name') ?? '');
            $customerPhone = trim($this->request->getPost('customer_phone') ?? '');
            $notes         = trim($this->request->getPost('notes') ?? '');
        } else {
            $items         = $json['items'] ?? [];
            $paymentMethod = $json['payment_method'] ?? 'cash';
            $walletId      = (int)($json['wallet_id'] ?? 0) ?: null;
            $cashReceived  = (float)($json['cash_received'] ?? 0);
            $customerName  = trim($json['customer_name'] ?? '');
            $customerPhone = trim($json['customer_phone'] ?? '');
            $notes         = trim($json['notes'] ?? '');
        }

        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Keranjang belanja kosong.']);
        }

        $totalAmount = 0.0;
        $totalCost   = 0.0;
        $orderItems  = [];

        foreach ($items as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            $qty       = (int)($item['qty'] ?? 1);
            if ($qty <= 0) continue;

            $product = $this->productModel->where('id', $productId)->where('user_id', $userId)->first();
            if (!$product) {
                $name  = trim($item['name'] ?? 'Item');
                $price = (float)($item['price'] ?? 0);
                $cost  = (float)($item['cost_price'] ?? 0);
            } else {
                $name  = $product['name'];
                $price = (float)$product['selling_price'];
                $cost  = (float)$product['cost_price'];
                $this->productModel->deductStock($productId, $qty);
            }

            $subtotal = $price * $qty;
            $totalAmount += $subtotal;
            $totalCost   += ($cost * $qty);

            $orderItems[] = [
                'product_id'   => $productId ?: null,
                'product_name' => $name,
                'qty'          => $qty,
                'price'        => $price,
                'cost_price'   => $cost,
                'subtotal'     => $subtotal,
            ];
        }

        $profit       = $totalAmount - $totalCost;
        $changeAmount = ($paymentMethod === 'cash' && $cashReceived > $totalAmount) ? ($cashReceived - $totalAmount) : 0.0;
        $orderNumber  = 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // Create transaction in transactions table (income)
        $txId = null;
        if ($paymentMethod !== 'kasbon') {
            if (!$walletId) {
                $walletId = $this->walletModel->getDefaultWalletId($userId);
            }
            $txId = $this->txModel->insert([
                'user_id'     => $userId,
                'wallet_id'   => $walletId,
                'type'        => 'income',
                'amount'      => $totalAmount,
                'note'        => 'Penjualan Kasir: ' . $orderNumber . ($customerName ? ' (' . $customerName . ')' : ''),
                'date'        => date('Y-m-d'),
            ]);
        }

        // If Kasbon (Piutang)
        $debtId = null;
        if ($paymentMethod === 'kasbon') {
            $debtPerson = $customerName ?: 'Pelanggan POS (' . $orderNumber . ')';
            $debtId = $this->debtModel->insert([
                'user_id'   => $userId,
                'type'      => 'piutang',
                'person'    => $debtPerson,
                'amount'    => $totalAmount,
                'paid'      => 0,
                'due_date'  => date('Y-m-d', strtotime('+7 days')),
                'notes'     => 'Kasbon POS ' . $orderNumber . ($customerPhone ? ' (WA: ' . $customerPhone . ')' : ''),
                'is_settled'=> 0,
            ]);
        }

        $orderId = $this->orderModel->insert([
            'user_id'        => $userId,
            'order_number'   => $orderNumber,
            'total_amount'   => $totalAmount,
            'total_cost'     => $totalCost,
            'profit'         => $profit,
            'payment_method' => $paymentMethod,
            'wallet_id'      => $walletId,
            'cash_received'  => $cashReceived,
            'change_amount'  => $changeAmount,
            'customer_name'  => $customerName ?: null,
            'customer_phone' => $customerPhone ?: null,
            'status'         => 'paid',
            'debt_id'        => $debtId,
            'transaction_id' => $txId,
            'notes'          => $notes ?: null,
            'date'           => date('Y-m-d'),
        ]);

        foreach ($orderItems as &$oi) {
            $oi['order_id'] = $orderId;
            $this->orderItemModel->insert($oi);
        }
        unset($oi);

        // Add Loyalty Stamp if customer phone exists
        if ($customerPhone) {
            $loyaltyModel = new \App\Models\PosLoyaltyModel();
            $loyaltyModel->addStamps($userId, $customerPhone, $customerName ?: 'Pelanggan', 1);
        }

        $orderData = $this->orderModel->getWithItems($orderId, $userId);

        return $this->response->setJSON([
            'success' => true,
            'order'   => $orderData,
            'message' => 'Transaksi berhasil disimpan!',
        ]);
    }

    // GET /pos/products — Products & Stock Management
    public function products()
    {
        $userId     = session()->get('user_id');
        $products   = $this->productModel->where('user_id', $userId)->where('is_active', 1)->orderBy('category', 'ASC')->orderBy('name', 'ASC')->findAll();
        $lowStock   = $this->productModel->getLowStock($userId);
        $categories = $this->productModel->getCategories($userId);
        $symbol     = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return view('pos/products', [
            'pageTitle'  => 'Manajemen Produk & Stok',
            'products'   => $products,
            'lowStock'   => $lowStock,
            'categories' => $categories,
            'symbol'     => $symbol,
        ]);
    }

    // POST /pos/products/store
    public function storeProduct()
    {
        $userId       = session()->get('user_id');
        $id           = (int)($this->request->getPost('id') ?? 0);
        $name         = trim($this->request->getPost('name') ?? '');
        $category     = trim($this->request->getPost('category') ?? 'Umum') ?: 'Umum';
        $sku          = trim($this->request->getPost('sku') ?? '') ?: null;
        $costPrice    = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('cost_price') ?? '0');
        $sellingPrice = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('selling_price') ?? '0');
        $stock        = (int)str_replace(['.', ','], ['', ''], $this->request->getPost('stock') ?? '0');
        $minStock     = (int)($this->request->getPost('min_stock_alert') ?? 5);
        $unit         = trim($this->request->getPost('unit') ?? 'pcs') ?: 'pcs';
        $icon         = trim($this->request->getPost('icon') ?? 'coffee') ?: 'coffee';
        $variantsRaw  = $this->request->getPost('variants_json');

        if (!$name || $sellingPrice <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama produk dan harga jual wajib diisi.']);
        }

        $variantsJson = null;
        if (!empty($variantsRaw)) {
            $variantsJson = is_string($variantsRaw) ? $variantsRaw : json_encode($variantsRaw);
        }

        $data = [
            'user_id'         => $userId,
            'name'            => $name,
            'category'        => $category,
            'sku'             => $sku,
            'cost_price'      => $costPrice,
            'selling_price'   => $sellingPrice,
            'stock'           => $stock,
            'min_stock_alert' => $minStock,
            'unit'            => $unit,
            'icon'            => $icon,
            'variants_json'   => $variantsJson,
            'is_active'       => 1,
        ];

        if ($id > 0) {
            $this->productModel->where('id', $id)->where('user_id', $userId)->set($data)->update();
        } else {
            $id = $this->productModel->insert($data);
        }

        return $this->response->setJSON(['success' => true, 'id' => $id, 'message' => 'Produk berhasil disimpan!']);
    }

    // POST /pos/products/adjust-stock
    public function adjustStock()
    {
        $userId    = session()->get('user_id');
        $productId = (int)($this->request->getPost('product_id') ?? 0);
        $stock     = (int)str_replace(['.', ','], ['', ''], $this->request->getPost('stock') ?? '0');

        $product = $this->productModel->where('id', $productId)->where('user_id', $userId)->first();
        if (!$product) return $this->response->setJSON(['success' => false, 'message' => 'Produk tidak ditemukan.']);

        $this->productModel->update($productId, ['stock' => max(0, $stock)]);
        return $this->response->setJSON(['success' => true, 'message' => 'Stok produk berhasil diperbarui!']);
    }

    // POST /pos/products/delete/(:num)
    public function deleteProduct(int $id)
    {
        $userId = session()->get('user_id');
        $this->productModel->where('id', $id)->where('user_id', $userId)->set(['is_active' => 0])->update();
        return $this->response->setJSON(['success' => true, 'message' => 'Produk berhasil dihapus!']);
    }

    // GET /pos/reports — P&L and Best Sellers Report
    public function reports()
    {
        $userId   = session()->get('user_id');
        $monthKey = $this->request->getGet('month') ?: date('Y-m');
        $report   = $this->orderModel->getMonthlyReport($userId, $monthKey);
        $symbol   = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return view('pos/reports', [
            'pageTitle' => 'Laporan Kasir & Laba Rugi',
            'report'    => $report,
            'month'     => date('F Y', strtotime($monthKey . '-01')),
            'monthKey'  => $monthKey,
            'symbol'    => $symbol,
        ]);
    }

    // GET /pos/orders — Live Order Screen & Table Management
    public function orders()
    {
        $userId    = session()->get('user_id');
        $status    = $this->request->getGet('status') ?: 'all';
        $orders    = $this->orderModel->getOrdersList($userId, $status);
        $counts    = $this->orderModel->getStatusCounts($userId);
        $store     = $this->settingModel->getStoreProfile($userId);
        $walletData= $this->walletModel->getWithBalances($userId);
        $symbol    = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return view('pos/orders', [
            'pageTitle'  => 'Daftar Pesanan Masuk (Live Orders)',
            'orders'     => $orders,
            'counts'     => $counts,
            'currentTab' => $status,
            'store'      => $store,
            'wallets'    => $walletData['wallets'] ?? [],
            'symbol'     => $symbol,
        ]);
    }

    // GET /pos/orders/poll — Realtime AJAX polling for orders
    public function pollOrders()
    {
        $userId = session()->get('user_id');
        $status = $this->request->getGet('status') ?: 'all';
        $orders = $this->orderModel->getOrdersList($userId, $status);
        $counts = $this->orderModel->getStatusCounts($userId);

        return $this->response->setJSON([
            'success' => true,
            'orders'  => $orders,
            'counts'  => $counts,
        ]);
    }

    // POST /pos/orders/update-status
    public function updateOrderStatus()
    {
        $userId  = session()->get('user_id');
        $orderId = (int)($this->request->getPost('order_id') ?? 0);
        $status  = trim($this->request->getPost('status') ?? '');

        $allowedStatuses = ['pending', 'processing', 'delivering', 'served_unpaid', 'delivered_unpaid', 'paid', 'cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Status pesanan tidak valid.']);
        }

        $order = $this->orderModel->where('id', $orderId)->where('user_id', $userId)->first();
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
        }

        // If cancelling, restore stocks
        if ($status === 'cancelled' && $order['status'] !== 'cancelled') {
            $items = $this->orderItemModel->where('order_id', $orderId)->findAll();
            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $this->productModel->addStock((int)$item['product_id'], (int)$item['qty']);
                }
            }
        }

        $this->orderModel->update($orderId, ['status' => $status]);

        $statusLabels = [
            'pending'          => 'Pesanan Baru (Pending)',
            'processing'       => 'Sedang Disiapkan / Dikemas',
            'delivering'       => 'Sedang Dikirim Kurir / Siap Diambil',
            'served_unpaid'    => 'Sudah Disajikan Meja (Belum Bayar)',
            'delivered_unpaid' => 'Sudah Diterima Pembeli (Belum Bayar/COD)',
            'paid'             => 'Selesai & Lunas',
            'cancelled'        => 'Dibatalkan',
        ];

        return $this->response->setJSON([
            'success' => true,
            'status'  => $status,
            'message' => 'Status pesanan diubah menjadi: ' . ($statusLabels[$status] ?? $status),
        ]);
    }

    // POST /pos/orders/pay — Settle payment for served_unpaid / delivered_unpaid / pending orders
    public function payOrder()
    {
        $userId        = session()->get('user_id');
        $orderId       = (int)($this->request->getPost('order_id') ?? 0);
        $paymentMethod = $this->request->getPost('payment_method') ?? 'cash';
        $walletId      = (int)($this->request->getPost('wallet_id') ?? 0) ?: null;
        $cashReceived  = (float)($this->request->getPost('cash_received') ?? 0);

        $order = $this->orderModel->where('id', $orderId)->where('user_id', $userId)->first();
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
        }

        $totalAmount  = (float)$order['total_amount'];
        $changeAmount = ($paymentMethod === 'cash' && $cashReceived > $totalAmount) ? ($cashReceived - $totalAmount) : 0.0;

        // Record income transaction if not kasbon
        $txId = $order['transaction_id'];
        if ($paymentMethod !== 'kasbon' && !$txId) {
            if (!$walletId) {
                $walletId = $this->walletModel->getDefaultWalletId($userId);
            }
            $targetNote = 'Pembayaran POS: ' . $order['order_number'] . ($order['table_no'] ? ' (' . $order['table_no'] . ')' : '') . ($order['customer_name'] ? ' - ' . $order['customer_name'] : '');
            if (($order['order_type'] ?? '') === 'delivery') {
                $targetNote = 'Pembayaran Delivery: ' . $order['order_number'] . ' - ' . $order['customer_name'];
            }

            $txId = $this->txModel->insert([
                'user_id'   => $userId,
                'wallet_id' => $walletId,
                'type'      => 'income',
                'amount'    => $totalAmount,
                'note'      => $targetNote,
                'date'      => date('Y-m-d'),
            ]);
        }

        // If Kasbon (Piutang)
        $debtId = $order['debt_id'];
        if ($paymentMethod === 'kasbon' && !$debtId) {
            $debtPerson = $order['customer_name'] ?: ('Pelanggan POS (' . $order['order_number'] . ')');
            $debtId = $this->debtModel->insert([
                'user_id'   => $userId,
                'type'      => 'piutang',
                'person'    => $debtPerson,
                'amount'    => $totalAmount,
                'paid'      => 0,
                'due_date'  => date('Y-m-d', strtotime('+7 days')),
                'notes'     => 'Kasbon POS ' . $order['order_number'] . ($order['customer_phone'] ? ' (WA: ' . $order['customer_phone'] . ')' : ''),
                'is_settled'=> 0,
            ]);
        }

        $this->orderModel->update($orderId, [
            'status'         => 'paid',
            'payment_method' => $paymentMethod,
            'wallet_id'      => $walletId,
            'cash_received'  => $cashReceived,
            'change_amount'  => $changeAmount,
            'debt_id'        => $debtId,
            'transaction_id' => $txId,
        ]);

        // Add Loyalty Stamp if customer phone exists
        if (!empty($order['customer_phone'])) {
            $loyaltyModel = new \App\Models\PosLoyaltyModel();
            $loyaltyModel->addStamps($userId, $order['customer_phone'], $order['customer_name'] ?: 'Pelanggan', 1);
        }

        return $this->response->setJSON([
            'success'       => true,
            'change_amount' => $changeAmount,
            'message'       => 'Pembayaran berhasil disimpan! Pesanan selesai.',
        ]);
    }

    // GET /pos/kds — Kitchen Display System (Fullscreen Screen for Kitchen/Barista)
    public function kds()
    {
        $userId = session()->get('user_id');
        $store  = $this->settingModel->getStoreProfile($userId);
        $orders = $this->orderModel->getOrdersList($userId, null, 100);

        // Filter active cooking orders (pending & processing)
        $kitchenOrders = array_values(array_filter($orders, function($o) {
            return in_array($o['status'], ['pending', 'processing'], true);
        }));

        return view('pos/kds', [
            'pageTitle'     => 'Kitchen Display System (KDS)',
            'store'         => $store,
            'kitchenOrders' => $kitchenOrders,
            'symbol'        => $store['currency_symbol'] ?? 'Rp',
        ]);
    }

    // GET /pos/kds/poll — Realtime AJAX polling for Kitchen Display System
    public function kdsPoll()
    {
        $userId = session()->get('user_id');
        $orders = $this->orderModel->getOrdersList($userId, null, 100);

        $kitchenOrders = array_values(array_filter($orders, function($o) {
            return in_array($o['status'], ['pending', 'processing'], true);
        }));

        return $this->response->setJSON([
            'success' => true,
            'orders'  => $kitchenOrders,
            'count'   => count($kitchenOrders),
        ]);
    }

    // GET /pos/vouchers — Voucher Codes & Promotions Management
    public function vouchers()
    {
        $userId       = session()->get('user_id');
        $voucherModel = new \App\Models\PosVoucherModel();
        $vouchers     = $voucherModel->where('user_id', $userId)->orderBy('id', 'DESC')->findAll();
        $symbol       = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return view('pos/vouchers', [
            'pageTitle' => 'Kupon Diskon & Promo Toko',
            'vouchers'  => $vouchers,
            'symbol'    => $symbol,
        ]);
    }

    // POST /pos/vouchers/store
    public function storeVoucher()
    {
        $userId       = session()->get('user_id');
        $voucherModel = new \App\Models\PosVoucherModel();
        $id           = (int)($this->request->getPost('id') ?? 0);
        $code         = strtoupper(trim($this->request->getPost('code') ?? ''));
        $title        = trim($this->request->getPost('title') ?? '');
        $type         = trim($this->request->getPost('type') ?? 'nominal');
        $value        = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('value') ?? '0');
        $minOrder     = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('min_order') ?? '0');
        $maxDiscount  = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('max_discount') ?? '0');
        $usageLimit   = (int)($this->request->getPost('usage_limit') ?? 0);
        $expiresAt    = trim($this->request->getPost('expires_at') ?? '') ?: null;
        $isActive     = $this->request->getPost('is_active') === '0' ? 0 : 1;

        if (!$code) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kode voucher wajib diisi.']);
        }

        // Check uniqueness for this user
        $existing = $voucherModel->where('user_id', $userId)->where('code', $code);
        if ($id > 0) $existing->where('id !=', $id);
        if ($existing->first()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kode promo "' . esc($code) . '" sudah ada.']);
        }

        $data = [
            'user_id'      => $userId,
            'code'         => $code,
            'title'        => $title ?: $code,
            'type'         => in_array($type, ['percent', 'nominal', 'free_shipping']) ? $type : 'nominal',
            'value'        => $value,
            'min_order'    => $minOrder,
            'max_discount' => $maxDiscount,
            'usage_limit'  => $usageLimit,
            'is_active'    => $isActive,
            'expires_at'   => $expiresAt,
        ];

        if ($id > 0) {
            $voucherModel->where('id', $id)->where('user_id', $userId)->set($data)->update();
        } else {
            $voucherModel->insert($data);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Kupon promo berhasil disimpan!']);
    }

    // POST /pos/vouchers/delete/(:num)
    public function deleteVoucher(int $id)
    {
        $userId       = session()->get('user_id');
        $voucherModel = new \App\Models\PosVoucherModel();
        $voucherModel->where('id', $id)->where('user_id', $userId)->delete();
        return $this->response->setJSON(['success' => true, 'message' => 'Kupon promo berhasil dihapus!']);
    }

    // GET /pos/loyalty — Customer Stamps Program
    public function loyalty()
    {
        $userId       = session()->get('user_id');
        $loyaltyModel = new \App\Models\PosLoyaltyModel();
        $stamps       = $loyaltyModel->where('user_id', $userId)->orderBy('stamps_count', 'DESC')->findAll();
        $store        = $this->settingModel->getStoreProfile($userId);

        return view('pos/loyalty', [
            'pageTitle' => 'Program Stamp & Loyalitas Pelanggan',
            'stamps'    => $stamps,
            'store'     => $store,
        ]);
    }

    // GET /pos/qr — Printable PDF Standee & Poster QR Code
    public function qrPrint()
    {
        $userId   = session()->get('user_id');
        $store    = $this->settingModel->getStoreProfile($userId);
        $tableNo  = trim($this->request->getGet('table') ?? '');
        $template = trim($this->request->getGet('template') ?? 'modern'); // 'modern', 'vintage', 'dark'

        // Build target URL
        $baseUrl = base_url();
        $targetUrl = rtrim($baseUrl, '/') . '/menu/' . urlencode($store['store_slug']);
        if ($tableNo) {
            $targetUrl .= '?table=' . urlencode($tableNo);
        }

        return view('pos/qr_print', [
            'pageTitle' => 'Cetak QR Code Menu & Standee Meja',
            'store'     => $store,
            'tableNo'   => $tableNo,
            'targetUrl' => $targetUrl,
            'template'  => $template,
            'symbol'    => $store['currency_symbol'] ?? 'Rp',
        ]);
    }

    // POST /pos/store-profile — Save Store Name, Slug, Address & QR Settings
    public function saveStoreProfile()
    {
        $userId       = session()->get('user_id');
        $storeName    = trim($this->request->getPost('store_name') ?? '');
        $storeSlug    = trim($this->request->getPost('store_slug') ?? '');
        $tagline      = trim($this->request->getPost('store_tagline') ?? '');
        $address      = trim($this->request->getPost('store_address') ?? '');
        $phone        = trim($this->request->getPost('store_phone') ?? '');
        $qrFooter     = trim($this->request->getPost('store_qr_footer') ?? '');
        $isOpen       = $this->request->getPost('store_is_open') === '1' ? '1' : '0';
        $deliveryOn   = $this->request->getPost('store_delivery_enabled') === '1' ? '1' : '0';
        $deliveryFee  = (float)($this->request->getPost('store_delivery_fee') ?? 0);
        $pickupOn     = $this->request->getPost('store_pickup_enabled') === '1' ? '1' : '0';
        $bankInfo     = trim($this->request->getPost('store_bank_info') ?? '');
        $qrisInfo     = trim($this->request->getPost('store_qris_info') ?? '');
        $loyaltyOn    = $this->request->getPost('store_loyalty_enabled') === '1' ? '1' : '0';
        $loyaltyTarget= (int)($this->request->getPost('store_loyalty_target') ?? 10);
        $loyaltyReward= trim($this->request->getPost('store_loyalty_reward') ?? 'Gratis 1 Menu Favorit');

        if (!$storeName) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama toko/pos wajib diisi.']);
        }

        // Slugify if empty or clean up
        if (!$storeSlug) {
            $storeSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $storeName), '-'));
        } else {
            $storeSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9\-]+/', '', $storeSlug), '-'));
        }

        // Check if slug is used by other user
        $existing = $this->settingModel->where('key', 'store_slug')
                                       ->where('LOWER(value)', strtolower($storeSlug))
                                       ->where('user_id !=', $userId)
                                       ->first();
        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Alamat URL slug "' . esc($storeSlug) . '" sudah digunakan oleh outlet lain. Gunakan nama/slug unik.'
            ]);
        }

        $this->settingModel->setPref($userId, 'store_name', $storeName);
        $this->settingModel->setPref($userId, 'store_slug', $storeSlug);
        $this->settingModel->setPref($userId, 'store_tagline', $tagline);
        $this->settingModel->setPref($userId, 'store_address', $address);
        $this->settingModel->setPref($userId, 'store_phone', $phone);
        $this->settingModel->setPref($userId, 'store_qr_footer', $qrFooter);
        $this->settingModel->setPref($userId, 'store_is_open', $isOpen);
        $this->settingModel->setPref($userId, 'store_delivery_enabled', $deliveryOn);
        $this->settingModel->setPref($userId, 'store_delivery_fee', (string)$deliveryFee);
        $this->settingModel->setPref($userId, 'store_pickup_enabled', $pickupOn);
        $this->settingModel->setPref($userId, 'store_bank_info', $bankInfo);
        $this->settingModel->setPref($userId, 'store_qris_info', $qrisInfo);
        $this->settingModel->setPref($userId, 'store_loyalty_enabled', $loyaltyOn);
        $this->settingModel->setPref($userId, 'store_loyalty_target', (string)$loyaltyTarget);
        $this->settingModel->setPref($userId, 'store_loyalty_reward', $loyaltyReward);

        return $this->response->setJSON([
            'success'    => true,
            'store_slug' => $storeSlug,
            'menu_url'   => base_url('/menu/' . $storeSlug),
            'message'    => 'Profil toko & pengaturan online delivery berhasil disimpan!',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // THERMAL RECEIPT PRINTING (58mm / 80mm)
    // ─────────────────────────────────────────────────────────────────────────
    public function receipt(int $orderId)
    {
        $userId = session()->get('user_id');
        $order = $this->orderModel->getWithItems($orderId, $userId);

        if (!$order) {
            return redirect()->to('/pos/orders')->with('error', 'Pesanan tidak ditemukan.');
        }

        $storeInfo = [
            'store_name'    => $this->settingModel->get($userId, 'store_name', session()->get('user_name') . ' Store'),
            'store_tagline' => $this->settingModel->get($userId, 'store_tagline', ''),
            'store_address' => $this->settingModel->get($userId, 'store_address', ''),
            'store_phone'   => $this->settingModel->get($userId, 'store_phone', ''),
        ];

        $activeShift = $this->shiftModel->getActiveShift($userId);
        $cashierName = $activeShift ? $activeShift['cashier_name'] : session()->get('user_name');
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return view('pos/receipt', [
            'order'       => $order,
            'store'       => $storeInfo,
            'cashierName' => $cashierName,
            'symbol'      => $symbol,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CASHIER SHIFT & CASH DRAWER RECONCILIATION
    // ─────────────────────────────────────────────────────────────────────────
    public function shifts()
    {
        $userId = session()->get('user_id');
        $activeShift = $this->shiftModel->getActiveShift($userId);
        $shiftHistory = $this->shiftModel->getShiftHistory($userId, 20);
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        $currentCashSales = 0.0;
        $currentTrxCount = 0;
        $currentExpectedCash = 0.0;

        if ($activeShift) {
            $db = \Config\Database::connect();
            $salesRow = $db->query("
                SELECT 
                    COUNT(*) AS total_trx,
                    COALESCE(SUM(CASE WHEN payment_method = 'cash' OR payment_method = 'cod' THEN total_amount ELSE 0 END), 0) AS total_cash
                FROM pos_orders
                WHERE user_id = ? AND status = 'paid' AND created_at >= ?
            ", [$userId, $activeShift['opened_at']])->getRowArray();

            $currentCashSales = (float)($salesRow['total_cash'] ?? 0);
            $currentTrxCount = (int)($salesRow['total_trx'] ?? 0);
            $currentExpectedCash = (float)$activeShift['starting_cash'] + $currentCashSales;
        }

        return view('pos/shifts', [
            'pageTitle'           => 'Shift Kasir & Laci Uang',
            'activeShift'         => $activeShift,
            'shiftHistory'        => $shiftHistory,
            'symbol'              => $symbol,
            'currentUserName'     => session()->get('user_name'),
            'currentCashSales'    => $currentCashSales,
            'currentTrxCount'     => $currentTrxCount,
            'currentExpectedCash' => $currentExpectedCash,
        ]);
    }

    public function openShift()
    {
        $userId       = session()->get('user_id');
        $cashierName  = trim($this->request->getPost('cashier_name') ?? 'Kasir');
        $startingCash = (float)($this->request->getPost('starting_cash') ?? 0);
        $notes        = trim($this->request->getPost('notes') ?? '') ?: null;

        $id = $this->shiftModel->openShift($userId, $cashierName, $startingCash, $notes);

        return $this->response->setJSON([
            'success' => $id > 0,
            'id'      => $id,
            'message' => 'Shift kasir berhasil dibuka dengan modal awal ' . number_format($startingCash, 0, ',', '.'),
        ]);
    }

    public function closeShift()
    {
        $userId     = session()->get('user_id');
        $shiftId    = (int)($this->request->getPost('shift_id') ?? 0);
        $actualCash = (float)($this->request->getPost('actual_cash') ?? 0);
        $notes      = trim($this->request->getPost('notes') ?? '') ?: null;

        if (!$shiftId) {
            $active = $this->shiftModel->getActiveShift($userId);
            $shiftId = $active ? (int)$active['id'] : 0;
        }

        if (!$shiftId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada shift aktif yang dapat ditutup.']);
        }

        $ok = $this->shiftModel->closeShift($shiftId, $userId, $actualCash, $notes);

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Shift kasir berhasil ditutup dan direkonsiliasi.' : 'Gagal menutup shift.',
        ]);
    }

    public function activeShift()
    {
        $userId = session()->get('user_id');
        $activeShift = $this->shiftModel->getActiveShift($userId);

        return $this->response->setJSON([
            'success' => true,
            'shift'   => $activeShift,
        ]);
    }
}


