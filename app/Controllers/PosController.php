<?php

namespace App\Controllers;

use App\Models\DebtModel;
use App\Models\PosOrderItemModel;
use App\Models\PosOrderModel;
use App\Models\PosProductModel;
use App\Models\SettingModel;
use App\Models\TransactionModel;
use App\Models\WalletModel;

class PosController extends BaseController
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

        if (!$name || $sellingPrice <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama produk dan harga jual wajib diisi.']);
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
}
