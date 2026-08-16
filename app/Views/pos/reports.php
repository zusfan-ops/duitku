<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.pos-rep-page {
    padding: 12px 14px 110px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.pos-rep-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.pos-pnl-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.pos-pnl-item {
    background: var(--bg);
    border-radius: 12px;
    padding: 12px;
    border: 1px solid var(--border);
}
.pos-pnl-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
.pos-pnl-val { font-size: 16px; font-weight: 800; color: var(--text-primary); margin-top: 2px; }

.best-seller-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}
.best-seller-row:last-child { border-bottom: none; }
.best-seller-rank {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #EA580C;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="pos-rep-page">

    <!-- Header & Month Filter -->
    <div style="display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:8px">
            <a href="/pos" style="background:var(--bg-card);border:1px solid var(--border);color:var(--text-primary);padding:8px 12px;border-radius:10px;text-decoration:none;font-size:12px;font-weight:700">
                ← Kasir
            </a>
            <h2 style="font-size:18px;font-weight:800;margin:0">Laporan Laba Rugi</h2>
        </div>
        <form method="GET" action="/pos/reports">
            <input type="month" name="month" value="<?= esc($monthKey) ?>" class="form-input" style="padding:6px 10px;font-size:12px;font-weight:700" onchange="this.form.submit()">
        </form>
    </div>

    <!-- P&L Hero Card -->
    <div class="pos-rep-card" style="background:linear-gradient(135deg, #1E1B4B 0%, #312E81 100%);color:#fff;border:none">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
                <span style="font-size:11px;font-weight:700;opacity:0.8;text-transform:uppercase">Laba Bersih Usaha (<?= esc($month) ?>)</span>
                <div style="font-size:26px;font-weight:900;color:#34D399;margin-top:2px">
                    <?= esc($symbol) ?> <?= number_format($report['summary']['total_profit'], 0, ',', '.') ?>
                </div>
            </div>
            <div style="text-align:right">
                <span style="font-size:11px;font-weight:700;opacity:0.8;text-transform:uppercase">Total Pesanan</span>
                <div style="font-size:18px;font-weight:800;margin-top:2px"><?= $report['summary']['total_orders'] ?> TRX</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px">
            <div style="background:rgba(255,255,255,0.08);border-radius:10px;padding:10px">
                <div style="font-size:10.5px;opacity:0.8">Omset Penjualan:</div>
                <div style="font-size:14px;font-weight:800"><?= esc($symbol) ?> <?= number_format($report['summary']['total_sales'], 0, ',', '.') ?></div>
            </div>
            <div style="background:rgba(255,255,255,0.08);border-radius:10px;padding:10px">
                <div style="font-size:10.5px;opacity:0.8">Total Modal (HPP):</div>
                <div style="font-size:14px;font-weight:800;color:#FCA5A5"><?= esc($symbol) ?> <?= number_format($report['summary']['total_cost'], 0, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <!-- Top 5 Best Sellers -->
    <div class="pos-rep-card">
        <h3 style="margin:0;font-size:15px;font-weight:800">🏆 5 Produk Terlaris (Best Seller)</h3>
        <?php if (empty($report['bestSellers'])): ?>
            <div style="text-align:center;padding:20px 0;color:var(--text-muted);font-size:12px">
                Belum ada transaksi penjualan pada bulan ini.
            </div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column">
                <?php foreach ($report['bestSellers'] as $idx => $bs): ?>
                <div class="best-seller-row">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="best-seller-rank" style="<?= $idx === 0 ? 'background:#F59E0B' : ($idx === 1 ? 'background:#94A3B8' : ($idx === 2 ? 'background:#B45309' : 'background:var(--primary)')) ?>">
                            <?= $idx + 1 ?>
                        </div>
                        <div>
                            <strong style="font-size:13px"><?= esc($bs['product_name']) ?></strong>
                            <div style="font-size:11.5px;color:var(--text-muted)"><?= $bs['total_qty'] ?> terjual</div>
                        </div>
                    </div>
                    <strong style="font-size:13px;color:#EA580C">
                        <?= esc($symbol) ?> <?= number_format($bs['total_revenue'], 0, ',', '.') ?>
                    </strong>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Payment Methods Breakdown -->
    <div class="pos-rep-card">
        <h3 style="margin:0;font-size:15px;font-weight:800">💳 Rincian Metode Pembayaran</h3>
        <?php if (empty($report['payments'])): ?>
            <div style="text-align:center;padding:15px 0;color:var(--text-muted);font-size:12px">
                Belum ada data pembayaran.
            </div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:8px">
                <?php foreach ($report['payments'] as $pay): 
                    $methodLabels = [
                        'cash' => '💵 Tunai / Cash',
                        'qris' => '📱 QRIS',
                        'transfer' => '💳 Transfer Bank',
                        'kasbon' => '📒 Kasbon Pelanggan',
                    ];
                ?>
                <div style="display:flex;align-items:center;justify-content:space-between;background:var(--bg);padding:10px 12px;border-radius:10px;border:1px solid var(--border)">
                    <span style="font-size:12.5px;font-weight:700"><?= $methodLabels[$pay['payment_method']] ?? strtoupper($pay['payment_method']) ?></span>
                    <span style="font-size:13px;font-weight:800;color:var(--text-primary)"><?= esc($symbol) ?> <?= number_format($pay['total'], 0, ',', '.') ?> (<?= $pay['count'] ?>x)</span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
<?= $this->endSection() ?>
