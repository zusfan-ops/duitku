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
}
