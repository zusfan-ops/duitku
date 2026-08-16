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
            $rawItems        = $this->request->getPost('items');
            $items           = is_string($rawItems) ? json_decode($rawItems, true) : ($rawItems ?? []);
            $orderType       = trim($this->request->getPost('order_type') ?? 'dine_in');
            $customerName    = trim($this->request->getPost('customer_name') ?? '');
            $customerPhone   = trim($this->request->getPost('customer_phone') ?? '');
            $tableNo         = trim($this->request->getPost('table_no') ?? '');
            $deliveryAddress = trim($this->request->getPost('delivery_address') ?? '');
            $deliveryNotes   = trim($this->request->getPost('delivery_notes') ?? '');
            $pickupTime      = trim($this->request->getPost('pickup_time') ?? '');
            $payMethod       = trim($this->request->getPost('payment_method') ?? 'cod');
            $voucherCode     = trim($this->request->getPost('voucher_code') ?? '');
            $notes           = trim($this->request->getPost('notes') ?? '');
        } else {
            $items           = $json['items'] ?? [];
            $orderType       = trim($json['order_type'] ?? 'dine_in');
            $customerName    = trim($json['customer_name'] ?? '');
            $customerPhone   = trim($json['customer_phone'] ?? '');
            $tableNo         = trim($json['table_no'] ?? '');
            $deliveryAddress = trim($json['delivery_address'] ?? '');
            $deliveryNotes   = trim($json['delivery_notes'] ?? '');
            $pickupTime      = trim($json['pickup_time'] ?? '');
            $payMethod       = trim($json['payment_method'] ?? 'cod');
            $voucherCode     = trim($json['voucher_code'] ?? '');
            $notes           = trim($json['notes'] ?? '');
        }

        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pilih minimal satu produk/menu untuk memesan.']);
        }

        if ($orderType === 'delivery') {
            if (!$deliveryAddress) {
                return $this->response->setJSON(['success' => false, 'message' => 'Alamat pengiriman lengkap wajib diisi untuk pesanan antar (Delivery).']);
            }
            if (!$customerPhone) {
                return $this->response->setJSON(['success' => false, 'message' => 'Nomor WhatsApp aktif wajib diisi agar kurir/toko dapat mengonfirmasi pengiriman.']);
            }
        } elseif ($orderType === 'dine_in') {
            if (!$tableNo) {
                return $this->response->setJSON(['success' => false, 'message' => 'Nomor Meja wajib diisi untuk makan di tempat.']);
            }
        } elseif ($orderType === 'takeaway') {
            if (!$customerPhone && !$customerName) {
                return $this->response->setJSON(['success' => false, 'message' => 'Nama atau Nomor WhatsApp wajib diisi untuk pesanan ambil sendiri.']);
            }
        }

        $subtotalProducts = 0.0;
        $totalCost        = 0.0;
        $orderItems       = [];

        foreach ($items as $item) {
            $productId        = (int)($item['product_id'] ?? 0);
            $qty              = (int)($item['qty'] ?? 1);
            $itemNotes        = trim($item['notes'] ?? '');
            $selectedVariants = $item['selected_variants'] ?? null;

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

            // Factor in variant price add-ons if selected
            $variantText = '';
            if (!empty($selectedVariants)) {
                if (is_string($selectedVariants)) {
                    $variantText = $selectedVariants;
                } elseif (is_array($selectedVariants)) {
                    $parts = [];
                    foreach ($selectedVariants as $v) {
                        $vName = $v['name'] ?? '';
                        $vPrice = (float)($v['price'] ?? 0);
                        if ($vPrice > 0) {
                            $price += $vPrice;
                            $parts[] = $vName . ' (+' . number_format($vPrice, 0, ',', '.') . ')';
                        } elseif ($vName) {
                            $parts[] = $vName;
                        }
                    }
                    $variantText = implode(', ', $parts);
                }
            }

            // Deduct stock if tracked
            if ((int)$product['stock'] > 0) {
                $this->productModel->deductStock($productId, $qty);
            }

            $itemSubtotal = $price * $qty;
            $subtotalProducts += $itemSubtotal;
            $totalCost        += ($cost * $qty);

            $orderItems[] = [
                'product_id'        => $productId,
                'product_name'      => $name,
                'notes'             => $itemNotes ?: null,
                'selected_variants' => $variantText ?: null,
                'qty'               => $qty,
                'price'             => $price,
                'cost_price'        => $cost,
                'subtotal'          => $itemSubtotal,
            ];
        }

        if (empty($orderItems)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Daftar pesanan kosong.']);
        }

        $deliveryFee = 0.0;
        if ($orderType === 'delivery') {
            $deliveryFee = (float)($store['store_delivery_fee'] ?? 0);
        }

        // Voucher Calculation
        $discountAmount = 0.0;
        $appliedVoucher = null;
        if ($voucherCode) {
            $voucherModel = new \App\Models\PosVoucherModel();
            $vRes = $voucherModel->validateAndCalculate($userId, $voucherCode, $subtotalProducts, $deliveryFee);
            if ($vRes['valid']) {
                $discountAmount = (float)$vRes['discount_amount'];
                $appliedVoucher = $vRes['voucher'];
                $voucherModel->where('id', $appliedVoucher['id'])->increment('used_count', 1);
            }
        }

        $totalAmount = max(0, ($subtotalProducts + $deliveryFee) - $discountAmount);
        $profit      = $totalAmount - $totalCost;
        $orderNumber = 'ORD-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $defaultCustomerName = $customerName;
        if (!$defaultCustomerName) {
            if ($orderType === 'delivery') {
                $defaultCustomerName = 'Pelanggan Delivery';
            } elseif ($orderType === 'takeaway') {
                $defaultCustomerName = 'Pelanggan Ambil Sendiri';
            } else {
                $defaultCustomerName = 'Pelanggan Meja ' . $tableNo;
            }
        }

        $orderId = $this->orderModel->insert([
            'user_id'          => $userId,
            'order_number'     => $orderNumber,
            'total_amount'     => $totalAmount,
            'total_cost'       => $totalCost,
            'profit'           => $profit,
            'payment_method'   => $payMethod ?: 'cod',
            'wallet_id'        => null,
            'cash_received'    => 0.0,
            'change_amount'    => 0.0,
            'customer_name'    => $defaultCustomerName,
            'customer_phone'   => $customerPhone ?: null,
            'table_no'         => ($orderType === 'dine_in') ? $tableNo : null,
            'status'           => 'pending',
            'order_source'     => 'public_menu',
            'order_type'       => $orderType,
            'delivery_address' => ($orderType === 'delivery') ? $deliveryAddress : null,
            'delivery_notes'   => ($orderType === 'delivery') ? $deliveryNotes : null,
            'delivery_fee'     => $deliveryFee,
            'voucher_code'     => $appliedVoucher ? $appliedVoucher['code'] : null,
            'discount_amount'  => $discountAmount,
            'pickup_time'      => ($orderType === 'takeaway') ? $pickupTime : null,
            'debt_id'          => null,
            'transaction_id'   => null,
            'notes'            => $notes ?: null,
            'date'             => date('Y-m-d'),
        ]);

        foreach ($orderItems as &$oi) {
            $oi['order_id'] = $orderId;
            $this->orderItemModel->insert($oi);
        }
        unset($oi);

        // Deduct raw ingredients based on Recipe (BOM)
        (new \App\Models\PosRecipeModel())->deductForOrder($orderId, $userId);

        return $this->response->setJSON([
            'success'      => true,
            'order_number' => $orderNumber,
            'order_id'     => $orderId,
            'status_url'   => '/menu/' . urlencode($slug) . '/status/' . urlencode($orderNumber),
            'message'      => 'Pesanan Anda berhasil dikirim ke toko!',
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
                'total_amount'   => (float)$order['total_amount'],
                'delivery_fee'   => (float)($order['delivery_fee'] ?? 0),
                'discount_amount'=> (float)($order['discount_amount'] ?? 0),
            ],
            'status'  => $order['status'],
        ]);
    }

    /**
     * POST /menu/{slug}/verify-voucher — Check voucher validity and calculate discount
     */
    public function verifyVoucher(string $slug)
    {
        $store = $this->settingModel->findUserByStoreSlug($slug);
        if (!$store) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Toko tidak ditemukan.']);
        }

        $userId      = (int)$store['user_id'];
        $code        = trim($this->request->getPost('code') ?? '');
        $subtotal    = (float)($this->request->getPost('subtotal') ?? 0);
        $deliveryFee = (float)($this->request->getPost('delivery_fee') ?? 0);

        if (!$code) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Masukkan kode promo.']);
        }

        $voucherModel = new \App\Models\PosVoucherModel();
        $result = $voucherModel->validateAndCalculate($userId, $code, $subtotal, $deliveryFee);

        return $this->response->setJSON($result);
    }

    /**
     * GET /menu/{slug}/stamps — Check customer loyalty stamp count
     */
    public function checkStamps(string $slug)
    {
        $store = $this->settingModel->findUserByStoreSlug($slug);
        if (!$store) {
            return $this->response->setJSON(['success' => false, 'message' => 'Toko tidak ditemukan.']);
        }

        $userId = (int)$store['user_id'];
        $phone  = trim($this->request->getGet('phone') ?? '');

        if (!$phone) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nomor telepon tidak valid.']);
        }

        $loyaltyModel = new \App\Models\PosLoyaltyModel();
        $record = $loyaltyModel->getCustomerStamps($userId, $phone);

        $target = (int)($store['store_loyalty_target'] ?? 10);
        $reward = $store['store_loyalty_reward'] ?? 'Gratis 1 Menu Favorit';
        $stamps = $record ? (int)$record['stamps_count'] : 0;

        return $this->response->setJSON([
            'success'      => true,
            'phone'        => $phone,
            'stamps_count' => $stamps,
            'target'       => $target,
            'reward'       => $reward,
            'progress_pct' => min(100, round(($stamps / max(1, $target)) * 100)),
        ]);
    }
}
