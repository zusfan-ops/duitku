<?php

namespace App\Controllers\Api;

use App\Models\DebtModel;
use App\Models\PosOrderItemModel;
use App\Models\PosOrderModel;
use App\Models\PosProductModel;
use App\Models\SettingModel;
use App\Models\TransactionModel;
use App\Models\WalletModel;

class PosController extends ApiController
{
    protected PosProductModel   $productModel;
    protected PosOrderModel     $orderModel;
    protected PosOrderItemModel $orderItemModel;
    protected SettingModel      $settingModel;
    protected WalletModel       $walletModel;
    protected TransactionModel  $txModel;
    protected DebtModel         $debtModel;

    public function __construct()
    {
        $this->productModel   = new PosProductModel();
        $this->orderModel     = new PosOrderModel();
        $this->orderItemModel = new PosOrderItemModel();
        $this->settingModel   = new SettingModel();
        $this->walletModel    = new WalletModel();
        $this->txModel        = new TransactionModel();
        $this->debtModel      = new DebtModel();
    }

    /**
     * GET api/pos
     * Cashier screen data (catalog, categories, today's summary, wallets)
     */
    public function index()
    {
        $userId     = $this->uid();
        $category   = $this->request->getGet('category');
        $search     = $this->request->getGet('search');

        $products   = $this->productModel->getForCashier($userId, $category, $search);
        $categories = $this->productModel->getCategories($userId);
        $summary    = $this->orderModel->getTodaySummary($userId);
        $walletData = $this->walletModel->getWithBalances($userId);
        $symbol     = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return $this->ok([
            'products'   => $products,
            'categories' => array_merge(['Semua'], $categories),
            'summary'    => $summary,
            'wallets'    => $walletData['wallets'] ?? [],
            'symbol'     => $symbol,
        ]);
    }

    /**
     * POST api/pos/checkout
     * Process cart checkout
     */
    public function checkout()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        $items         = $json['items'] ?? [];
        $paymentMethod = $json['payment_method'] ?? 'cash';
        $walletId      = (int)($json['wallet_id'] ?? 0) ?: null;
        $cashReceived  = (float)($json['cash_received'] ?? 0);
        $customerName  = trim($json['customer_name'] ?? '');
        $customerPhone = trim($json['customer_phone'] ?? '');
        $notes         = trim($json['notes'] ?? '');

        if (empty($items)) {
            return $this->fail('Keranjang belanja masih kosong.');
        }

        // Calculate totals and verify items
        $totalAmount = 0.0;
        $totalCost   = 0.0;
        $orderItems  = [];

        foreach ($items as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            $qty       = (int)($item['qty'] ?? 1);
            if ($qty <= 0) continue;

            $product = $this->productModel->where('id', $productId)->where('user_id', $userId)->first();
            if (!$product) {
                // If custom/adhoc item
                $name  = trim($item['name'] ?? 'Item');
                $price = (float)($item['price'] ?? 0);
                $cost  = (float)($item['cost_price'] ?? 0);
            } else {
                $name  = $product['name'];
                $price = (float)$product['selling_price'];
                $cost  = (float)$product['cost_price'];
                // Deduct stock
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

        // Create transaction entry in DuitKu transactions table (income)
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

        // If Kasbon (Piutang Pelanggan), create entry in debts table
        $debtId = null;
        if ($paymentMethod === 'kasbon') {
            $debtPerson = $customerName ?: 'Pelanggan POS (' . $orderNumber . ')';
            $debtId = $this->debtModel->insert([
                'user_id'   => $userId,
                'type'      => 'piutang', // Piutang karena pelanggan yang berhutang ke kita
                'person'    => $debtPerson,
                'amount'    => $totalAmount,
                'paid'      => 0,
                'due_date'  => date('Y-m-d', strtotime('+7 days')),
                'notes'     => 'Kasbon POS ' . $orderNumber . ($customerPhone ? ' (WA: ' . $customerPhone . ')' : ''),
                'is_settled'=> 0,
            ]);
        }

        // Create POS Order record
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
            'debt_id'        => $debtId,
            'transaction_id' => $txId,
            'notes'          => $notes ?: null,
            'date'           => date('Y-m-d'),
        ]);

        // Insert line items
        foreach ($orderItems as &$oi) {
            $oi['order_id'] = $orderId;
            $this->orderItemModel->insert($oi);
        }
        unset($oi);

        $orderData = $this->orderModel->getWithItems($orderId, $userId);

        return $this->ok([
            'order'        => $orderData,
            'message'      => 'Transaksi berhasil disimpan!',
        ]);
    }

    /**
     * GET api/pos/products
     * Products and inventory list
     */
    public function products()
    {
        $userId   = $this->uid();
        $products = $this->productModel->where('user_id', $userId)->where('is_active', 1)->orderBy('category', 'ASC')->orderBy('name', 'ASC')->findAll();
        $lowStock = $this->productModel->getLowStock($userId);
        $categories = $this->productModel->getCategories($userId);
        $symbol   = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return $this->ok([
            'products'   => $products,
            'low_stock'  => $lowStock,
            'categories' => $categories,
            'symbol'     => $symbol,
        ]);
    }

    /**
     * POST api/pos/products/store
     * Create or update product
     */
    public function storeProduct()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        $id           = (int)($json['id'] ?? 0);
        $name         = trim($json['name'] ?? '');
        $category     = trim($json['category'] ?? 'Umum') ?: 'Umum';
        $sku          = trim($json['sku'] ?? '') ?: null;
        $costPrice    = (float)($json['cost_price'] ?? 0);
        $sellingPrice = (float)($json['selling_price'] ?? 0);
        $stock        = (int)($json['stock'] ?? 0);
        $minStock     = (int)($json['min_stock_alert'] ?? 5);
        $unit         = trim($json['unit'] ?? 'pcs') ?: 'pcs';
        $icon         = trim($json['icon'] ?? 'coffee') ?: 'coffee';

        if (!$name || $sellingPrice <= 0) {
            return $this->fail('Nama produk dan harga jual wajib diisi.');
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
            'is_active'       => 1,
        ];

        if ($id > 0) {
            $this->productModel->where('id', $id)->where('user_id', $userId)->set($data)->update();
        } else {
            $id = $this->productModel->insert($data);
        }

        return $this->ok(['id' => $id, 'message' => 'Produk berhasil disimpan!']);
    }

    /**
     * POST api/pos/products/adjust-stock
     * Quick stock adjustment
     */
    public function adjustStock()
    {
        $userId    = $this->uid();
        $json      = $this->request->getJSON(true) ?? [];
        $productId = (int)($json['product_id'] ?? 0);
        $stock     = (int)($json['stock'] ?? 0);

        $product = $this->productModel->where('id', $productId)->where('user_id', $userId)->first();
        if (!$product) return $this->fail('Produk tidak ditemukan.');

        $this->productModel->update($productId, ['stock' => max(0, $stock)]);
        return $this->ok(['message' => 'Stok produk berhasil diperbarui!']);
    }

    /**
     * POST api/pos/products/delete/(:num)
     */
    public function deleteProduct(int $id)
    {
        $userId = $this->uid();
        $this->productModel->where('id', $id)->where('user_id', $userId)->set(['is_active' => 0])->update();
        return $this->ok(['message' => 'Produk berhasil dihapus!']);
    }

    /**
     * GET api/pos/history
     * Sales history list
     */
    public function history()
    {
        $userId = $this->uid();
        $orders = $this->orderModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll(30);
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return $this->ok([
            'orders' => $orders,
            'symbol' => $symbol,
        ]);
    }

    /**
     * GET api/pos/order/(:num)
     */
    public function orderDetail(int $id)
    {
        $userId = $this->uid();
        $order  = $this->orderModel->getWithItems($id, $userId);
        if (!$order) return $this->fail('Pesanan tidak ditemukan.');

        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        return $this->ok([
            'order'  => $order,
            'symbol' => $symbol,
        ]);
    }

    /**
     * GET api/pos/reports
     * P&L and Best Sellers report
     */
    public function reports()
    {
        $userId   = $this->uid();
        $monthKey = $this->request->getGet('month') ?: date('Y-m');
        $report   = $this->orderModel->getMonthlyReport($userId, $monthKey);
        $symbol   = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return $this->ok([
            'report'   => $report,
            'month'    => date('F Y', strtotime($monthKey . '-01')),
            'monthKey' => $monthKey,
            'symbol'   => $symbol,
        ]);
    }

    /**
     * GET api/pos/orders
     * Live orders list with status filtering & counts
     */
    public function orders()
    {
        $userId = $this->uid();
        $status = $this->request->getGet('status') ?: 'all';
        $orders = $this->orderModel->getOrdersList($userId, $status);
        $counts = $this->orderModel->getStatusCounts($userId);
        $store  = $this->settingModel->getStoreProfile($userId);
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return $this->ok([
            'orders' => $orders,
            'counts' => $counts,
            'store'  => $store,
            'symbol' => $symbol,
        ]);
    }

    /**
     * POST api/pos/orders/update-status
     */
    public function updateOrderStatus()
    {
        $userId  = $this->uid();
        $json    = $this->request->getJSON(true) ?? [];
        $orderId = (int)($json['order_id'] ?? 0);
        $status  = trim($json['status'] ?? '');

        $allowedStatuses = ['pending', 'processing', 'delivering', 'served_unpaid', 'delivered_unpaid', 'paid', 'cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            return $this->fail('Status tidak valid.');
        }

        $order = $this->orderModel->where('id', $orderId)->where('user_id', $userId)->first();
        if (!$order) {
            return $this->fail('Pesanan tidak ditemukan.');
        }

        if ($status === 'cancelled' && $order['status'] !== 'cancelled') {
            $items = $this->orderItemModel->where('order_id', $orderId)->findAll();
            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $this->productModel->addStock((int)$item['product_id'], (int)$item['qty']);
                }
            }
        }

        $this->orderModel->update($orderId, ['status' => $status]);

        return $this->ok([
            'status'  => $status,
            'message' => 'Status pesanan berhasil diperbarui.',
        ]);
    }

    /**
     * POST api/pos/orders/pay
     */
    public function payOrder()
    {
        $userId        = $this->uid();
        $json          = $this->request->getJSON(true) ?? [];
        $orderId       = (int)($json['order_id'] ?? 0);
        $paymentMethod = $json['payment_method'] ?? 'cash';
        $walletId      = (int)($json['wallet_id'] ?? 0) ?: null;
        $cashReceived  = (float)($json['cash_received'] ?? 0);

        $order = $this->orderModel->where('id', $orderId)->where('user_id', $userId)->first();
        if (!$order) {
            return $this->fail('Pesanan tidak ditemukan.');
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

        // Add loyalty stamp
        if (!empty($order['customer_phone'])) {
            $loyaltyModel = new \App\Models\PosLoyaltyModel();
            $loyaltyModel->addStamps($userId, $order['customer_phone'], $order['customer_name'] ?: 'Pelanggan', 1);
        }

        return $this->ok([
            'change_amount' => $changeAmount,
            'message'       => 'Pembayaran berhasil disimpan! Pesanan selesai.',
        ]);
    }

    /**
     * GET api/pos/vouchers
     */
    public function getVouchers()
    {
        $userId       = $this->uid();
        $voucherModel = new \App\Models\PosVoucherModel();
        $vouchers     = $voucherModel->where('user_id', $userId)->orderBy('id', 'DESC')->findAll();

        return $this->ok(['vouchers' => $vouchers]);
    }

    /**
     * POST api/pos/vouchers/store
     */
    public function storeVoucher()
    {
        $userId       = $this->uid();
        $json         = $this->request->getJSON(true) ?? [];
        $voucherModel = new \App\Models\PosVoucherModel();

        $id           = (int)($json['id'] ?? 0);
        $code         = strtoupper(trim($json['code'] ?? ''));
        $title        = trim($json['title'] ?? '');
        $type         = trim($json['type'] ?? 'nominal');
        $value        = (float)($json['value'] ?? 0);
        $minOrder     = (float)($json['min_order'] ?? 0);
        $maxDiscount  = (float)($json['max_discount'] ?? 0);
        $usageLimit   = (int)($json['usage_limit'] ?? 0);
        $expiresAt    = trim($json['expires_at'] ?? '') ?: null;
        $isActive     = ($json['is_active'] ?? true) ? 1 : 0;

        if (!$code) {
            return $this->fail('Kode voucher wajib diisi.');
        }

        $existing = $voucherModel->where('user_id', $userId)->where('code', $code);
        if ($id > 0) $existing->where('id !=', $id);
        if ($existing->first()) {
            return $this->fail('Kode promo "' . esc($code) . '" sudah ada.');
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

        return $this->ok(['message' => 'Kupon promo berhasil disimpan!']);
    }

    /**
     * POST api/pos/vouchers/delete/{id}
     */
    public function deleteVoucher(int $id)
    {
        $userId       = $this->uid();
        $voucherModel = new \App\Models\PosVoucherModel();
        $voucherModel->where('id', $id)->where('user_id', $userId)->delete();
        return $this->ok(['message' => 'Kupon promo berhasil dihapus!']);
    }

    /**
     * GET api/pos/loyalty
     */
    public function getLoyaltyStamps()
    {
        $userId       = $this->uid();
        $loyaltyModel = new \App\Models\PosLoyaltyModel();
        $stamps       = $loyaltyModel->where('user_id', $userId)->orderBy('stamps_count', 'DESC')->findAll();
        $store        = $this->settingModel->getStoreProfile($userId);

        return $this->ok([
            'stamps' => $stamps,
            'store'  => $store,
        ]);
    }

    /**
     * GET api/pos/store-profile
     */
    public function getStoreProfile()
    {
        $userId = $this->uid();
        $store  = $this->settingModel->getStoreProfile($userId);
        $baseUrl= base_url();
        $menuUrl= rtrim($baseUrl, '/') . '/menu/' . urlencode($store['store_slug']);

        return $this->ok([
            'store'    => $store,
            'menu_url' => $menuUrl,
        ]);
    }

    /**
     * POST api/pos/store-profile
     */
    public function saveStoreProfile()
    {
        $userId        = $this->uid();
        $json          = $this->request->getJSON(true) ?? [];
        $storeName     = trim($json['store_name'] ?? '');
        $storeSlug     = trim($json['store_slug'] ?? '');
        $tagline       = trim($json['store_tagline'] ?? '');
        $address       = trim($json['store_address'] ?? '');
        $phone         = trim($json['store_phone'] ?? '');
        $qrFooter      = trim($json['store_qr_footer'] ?? '');
        $isOpen        = ($json['store_is_open'] ?? true) ? '1' : '0';
        $deliveryOn    = ($json['store_delivery_enabled'] ?? true) ? '1' : '0';
        $deliveryFee   = (float)($json['store_delivery_fee'] ?? 0);
        $pickupOn      = ($json['store_pickup_enabled'] ?? true) ? '1' : '0';
        $bankInfo      = trim($json['store_bank_info'] ?? '');
        $qrisInfo      = trim($json['store_qris_info'] ?? '');
        $loyaltyOn     = ($json['store_loyalty_enabled'] ?? true) ? '1' : '0';
        $loyaltyTarget = (int)($json['store_loyalty_target'] ?? 10);
        $loyaltyReward = trim($json['store_loyalty_reward'] ?? 'Gratis 1 Menu Favorit');

        if (!$storeName) {
            return $this->fail('Nama toko/outlet wajib diisi.');
        }

        if (!$storeSlug) {
            $storeSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $storeName), '-'));
        } else {
            $storeSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9\-]+/', '', $storeSlug), '-'));
        }

        $existing = $this->settingModel->where('key', 'store_slug')
                                       ->where('LOWER(value)', strtolower($storeSlug))
                                       ->where('user_id !=', $userId)
                                       ->first();
        if ($existing) {
            return $this->fail('Alamat slug "' . esc($storeSlug) . '" sudah digunakan oleh toko lain.');
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

        $store = $this->settingModel->getStoreProfile($userId);

        return $this->ok([
            'store'    => $store,
            'menu_url' => base_url('/menu/' . $storeSlug),
            'message'  => 'Profil toko & pengaturan marketplace delivery berhasil disimpan!',
        ]);
    }

    /**
     * GET api/pos/shifts
     */
    public function getShifts()
    {
        $userId     = $this->uid();
        $shiftModel = new \App\Models\PosShiftModel();
        $active     = $shiftModel->getActiveShift($userId);
        $history    = $shiftModel->getShiftHistory($userId, 20);

        return $this->ok([
            'active_shift' => $active,
            'history'      => $history,
        ]);
    }

    /**
     * GET api/pos/shifts/active
     */
    public function getActiveShift()
    {
        $userId     = $this->uid();
        $shiftModel = new \App\Models\PosShiftModel();
        $active     = $shiftModel->getActiveShift($userId);

        return $this->ok([
            'active_shift' => $active,
        ]);
    }

    /**
     * POST api/pos/shifts/open
     */
    public function openShift()
    {
        $userId       = $this->uid();
        $json         = $this->request->getJSON(true) ?? [];
        $cashierName  = trim($json['cashier_name'] ?? 'Kasir');
        $startingCash = (float)($json['starting_cash'] ?? 0);
        $notes        = trim($json['notes'] ?? '') ?: null;

        $shiftModel = new \App\Models\PosShiftModel();
        $id = $shiftModel->openShift($userId, $cashierName, $startingCash, $notes);

        return $this->ok([
            'id'      => $id,
            'message' => 'Shift kasir berhasil dibuka.',
        ]);
    }

    /**
     * POST api/pos/shifts/close
     */
    public function closeShift()
    {
        $userId     = $this->uid();
        $json       = $this->request->getJSON(true) ?? [];
        $shiftId    = (int)($json['shift_id'] ?? 0);
        $actualCash = (float)($json['actual_cash'] ?? 0);
        $notes      = trim($json['notes'] ?? '') ?: null;

        $shiftModel = new \App\Models\PosShiftModel();
        if (!$shiftId) {
            $active = $shiftModel->getActiveShift($userId);
            $shiftId = $active ? (int)$active['id'] : 0;
        }

        if (!$shiftId) {
            return $this->fail('Tidak ada shift aktif untuk ditutup.');
        }

        $ok = $shiftModel->closeShift($shiftId, $userId, $actualCash, $notes);

        return $this->ok([
            'success' => $ok,
            'message' => 'Shift berhasil ditutup dan direkonsiliasi.',
        ]);
    }

    // ── Raw Ingredients & Recipes (BOM) ───────────────────────────

    public function ingredients()
    {
        $userId = $this->uid();
        $ingModel = new \App\Models\PosIngredientModel();
        $ingredients = $ingModel->getForUser($userId);
        $lowStock = $ingModel->getLowStockCount($userId);

        return $this->ok([
            'ingredients'     => $ingredients,
            'low_stock_count' => $lowStock,
        ]);
    }

    public function storeIngredient()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        $id          = (int)($json['id'] ?? 0);
        $name        = trim($json['name'] ?? '');
        $unit        = trim($json['unit'] ?? 'gram') ?: 'gram';
        $stock       = (float)($json['stock'] ?? 0);
        $minStock    = (float)($json['min_stock'] ?? 10);
        $costPerUnit = (float)($json['cost_per_unit'] ?? 0);

        if (!$name) {
            return $this->fail('Nama bahan baku wajib diisi.');
        }

        $ingModel = new \App\Models\PosIngredientModel();
        $data = [
            'user_id'       => $userId,
            'name'          => $name,
            'unit'          => $unit,
            'stock'         => $stock,
            'min_stock'     => $minStock,
            'cost_per_unit' => $costPerUnit,
        ];

        if ($id > 0) {
            $ingModel->where('id', $id)->where('user_id', $userId)->set($data)->update();
        } else {
            $id = $ingModel->insert($data);
        }

        return $this->ok(['id' => $id, 'message' => 'Bahan baku berhasil disimpan.']);
    }

    public function restockIngredient()
    {
        $userId      = $this->uid();
        $json        = $this->request->getJSON(true) ?? [];
        $id          = (int)($json['id'] ?? 0);
        $addStock    = (float)($json['add_stock'] ?? 0);
        $costPerUnit = isset($json['cost_per_unit']) ? (float)$json['cost_per_unit'] : null;

        if ($addStock <= 0) {
            return $this->fail('Jumlah restock harus lebih dari 0.');
        }

        $ingModel = new \App\Models\PosIngredientModel();
        $ok = $ingModel->restock($id, $userId, $addStock, $costPerUnit);
        if (!$ok) {
            return $this->fail('Bahan baku tidak ditemukan.');
        }

        return $this->ok(['message' => 'Stok bahan baku berhasil ditambah.']);
    }

    public function deleteIngredient($id = null)
    {
        $userId   = $this->uid();
        $id       = (int)$id;
        $ingModel = new \App\Models\PosIngredientModel();
        $recModel = new \App\Models\PosRecipeModel();

        $ingModel->where('id', $id)->where('user_id', $userId)->delete();
        $recModel->where('ingredient_id', $id)->where('user_id', $userId)->delete();

        return $this->ok(['message' => 'Bahan baku berhasil dihapus.']);
    }

    public function productRecipe($productId)
    {
        $recModel = new \App\Models\PosRecipeModel();
        $recipes  = $recModel->getForProduct((int)$productId);
        return $this->ok(['recipes' => $recipes]);
    }

    public function saveProductRecipe($productId)
    {
        $userId   = $this->uid();
        $json     = $this->request->getJSON(true) ?? [];
        $recipes  = $json['recipes'] ?? [];

        $recModel = new \App\Models\PosRecipeModel();
        $recModel->saveProductRecipes($userId, (int)$productId, (array)$recipes);

        return $this->ok(['message' => 'Resep produk berhasil disimpan.']);
    }
}


