<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #<?= esc($order['order_number']) ?> — <?= esc($store['store_name']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #F1F5F9;
            color: #000;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px;
        }
        .receipt-container {
            width: 100%;
            max-width: 320px; /* Standard 58mm / 80mm thermal width */
            background: #fff;
            padding: 16px 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 6px;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .store-title {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .store-sub {
            font-size: 11px;
            margin-top: 3px;
            line-height: 1.3;
        }
        .order-meta {
            font-size: 11.5px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
        }
        .receipt-items {
            width: 100%;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .item-row {
            font-size: 12px;
            margin-bottom: 6px;
        }
        .item-main {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
        }
        .item-variant {
            font-size: 10.5px;
            color: #333;
            padding-left: 8px;
        }
        .item-note {
            font-size: 10.5px;
            font-style: italic;
            padding-left: 8px;
        }
        .totals-box {
            font-size: 12px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .grand-total {
            font-size: 14px;
            font-weight: 900;
            margin-top: 4px;
            border-top: 1px solid #000;
            padding-top: 4px;
        }
        .receipt-footer {
            text-align: center;
            font-size: 11px;
            line-height: 1.4;
        }
        .btn-print-wrap {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            width: 100%;
            max-width: 320px;
        }
        .btn-action {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 800;
            font-size: 13px;
            text-align: center;
            text-decoration: none;
        }
        .btn-print { background: #EA580C; color: #fff; }
        .btn-back { background: #E2E8F0; color: #0F172A; }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                max-width: 100%;
                width: 100%;
                padding: 0;
            }
            .btn-print-wrap {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="receipt-container" id="printableReceipt">
    <div class="receipt-header">
        <div class="store-title"><?= esc($store['store_name']) ?></div>
        <?php if (!empty($store['store_tagline'])): ?>
            <div class="store-sub"><?= esc($store['store_tagline']) ?></div>
        <?php endif; ?>
        <?php if (!empty($store['store_address'])): ?>
            <div class="store-sub"><?= esc($store['store_address']) ?></div>
        <?php endif; ?>
        <?php if (!empty($store['store_phone'])): ?>
            <div class="store-sub">Telp/WA: <?= esc($store['store_phone']) ?></div>
        <?php endif; ?>
    </div>

    <div class="order-meta">
        <div class="meta-row">
            <span>No. Nota:</span>
            <strong>#<?= esc($order['order_number']) ?></strong>
        </div>
        <div class="meta-row">
            <span>Tanggal:</span>
            <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="meta-row">
            <span>Kasir:</span>
            <span><?= esc($cashierName ?? 'Admin') ?></span>
        </div>
        <div class="meta-row">
            <span>Pelanggan:</span>
            <span><?= esc($order['customer_name'] ?: 'Umum') ?></span>
        </div>
        <div class="meta-row">
            <span>Tipe Pesanan:</span>
            <strong>
                <?php
                    $type = $order['order_type'] ?? 'dine_in';
                    if ($type === 'delivery') echo 'DELIVERY';
                    elseif ($type === 'takeaway') echo 'TAKEAWAY';
                    else echo 'DINE-IN (Meja ' . ($order['table_no'] ?: '-') . ')';
                ?>
            </strong>
        </div>
    </div>

    <div class="receipt-items">
        <?php foreach ($order['items'] as $item): ?>
            <div class="item-row">
                <div class="item-main">
                    <span><?= esc($item['product_name']) ?></span>
                    <span><?= $symbol ?> <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                </div>
                <div style="font-size: 11px; color: #444;">
                    <?= (int)$item['qty'] ?> x <?= $symbol ?> <?= number_format($item['price'], 0, ',', '.') ?>
                </div>
                <?php if (!empty($item['selected_variants'])): ?>
                    <div class="item-variant">+ <?= esc($item['selected_variants']) ?></div>
                <?php endif; ?>
                <?php if (!empty($item['notes'])): ?>
                    <div class="item-note">Catatan: <?= esc($item['notes']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="totals-box">
        <div class="total-line">
            <span>Subtotal</span>
            <span><?= $symbol ?> <?= number_format($order['subtotal_amount'] ?? ($order['total_amount'] - ($order['delivery_fee'] ?? 0) + ($order['discount_amount'] ?? 0)), 0, ',', '.') ?></span>
        </div>
        <?php if ((float)($order['delivery_fee'] ?? 0) > 0): ?>
            <div class="total-line">
                <span>Ongkos Kirim</span>
                <span><?= $symbol ?> <?= number_format($order['delivery_fee'], 0, ',', '.') ?></span>
            </div>
        <?php endif; ?>
        <?php if ((float)($order['discount_amount'] ?? 0) > 0): ?>
            <div class="total-line" style="font-weight: 700;">
                <span>Diskon Kupon (<?= esc($order['voucher_code'] ?: 'PROMO') ?>)</span>
                <span>-<?= $symbol ?> <?= number_format($order['discount_amount'], 0, ',', '.') ?></span>
            </div>
        <?php endif; ?>
        <div class="total-line grand-total">
            <span>TOTAL TAGIHAN</span>
            <span><?= $symbol ?> <?= number_format($order['total_amount'], 0, ',', '.') ?></span>
        </div>
        <div class="total-line" style="margin-top: 6px;">
            <span>Metode Bayar</span>
            <span><?= strtoupper(esc($order['payment_method'] ?? 'CASH')) ?></span>
        </div>
        <?php if ((float)($order['cash_received'] ?? 0) > 0): ?>
            <div class="total-line">
                <span>Tunai Diterima</span>
                <span><?= $symbol ?> <?= number_format($order['cash_received'], 0, ',', '.') ?></span>
            </div>
            <div class="total-line">
                <span>Kembalian</span>
                <span><?= $symbol ?> <?= number_format(max(0, $order['cash_received'] - $order['total_amount']), 0, ',', '.') ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="receipt-footer">
        <p style="font-weight: 700;">*** TERIMA KASIH ***</p>
        <p style="margin-top: 4px;">Selamat Menikmati & Datang Kembali!</p>
        <p style="font-size: 9.5px; margin-top: 8px; color: #555;">Struk ini dicetak otomatis melalui DuitKu POS</p>
    </div>
</div>

<div class="btn-print-wrap">
    <button class="btn-action btn-print" onclick="window.print()">🖨️ Cetak Nota (Print)</button>
    <a href="javascript:history.back()" class="btn-action btn-back">← Kembali</a>
</div>

<script>
    // Auto-trigger print if query param ?autoprint=1 is passed
    if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 300);
        });
    }
</script>

</body>
</html>
