<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div style="max-width: 900px; margin: 0 auto; padding-bottom: 80px;">

    <!-- Top Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <a href="/pos/orders" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">← Pesanan Masuk</a>
                <h1 style="font-size: 20px; font-weight: 800; margin: 0;">⭐ Program Stamp & Loyalitas Pelanggan</h1>
            </div>
            <p style="font-size: 13px; color: var(--text-muted); margin: 4px 0 0 0;">
                Setiap kali transaksi pelanggan selesai (berdasarkan No. WhatsApp), stamp otomatis bertambah.
            </p>
        </div>

        <a href="/pos/orders" class="btn btn-outline-primary" style="border-radius: 12px; font-weight: 700;">
            ⚙️ Atur Target Reward di Profil Toko
        </a>
    </div>

    <!-- Info Reward Box -->
    <div style="background: linear-gradient(135deg, #EA580C 0%, #C2410C 100%); color: #fff; border-radius: 18px; padding: 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div>
            <span style="font-size: 11px; font-weight: 800; background: rgba(255,255,255,0.2); padding: 3px 8px; border-radius: 6px; letter-spacing: 0.5px;">PROGRAM AKTIF</span>
            <h3 style="font-size: 18px; font-weight: 800; margin: 8px 0 4px;">Kumpulkan <?= (int)($store['store_loyalty_target'] ?? 10) ?> Stamp = <?= esc($store['store_loyalty_reward'] ?? 'Gratis 1 Menu Favorit') ?></h3>
            <p style="font-size: 12.5px; opacity: 0.9; margin: 0;">Pelanggan dapat melihat akumulasi stamp mereka langsung dari halaman menu online / live status.</p>
        </div>
        <div style="font-size: 38px;">🎁</div>
    </div>

    <!-- Search Input -->
    <div style="margin-bottom: 16px;">
        <input type="text" id="stampSearch" onkeyup="filterStamps()" placeholder="🔍 Cari nomor WhatsApp atau nama pelanggan..." class="form-control" style="border-radius: 12px; padding: 12px 16px; font-size: 14px;">
    </div>

    <!-- Stamps Table / Cards -->
    <?php if (empty($stamps)): ?>
        <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 48px 20px; text-align: center;">
            <div style="font-size: 44px; margin-bottom: 12px;">⭐</div>
            <h3 style="font-size: 17px; font-weight: 800;">Belum Ada Data Stamp Pelanggan</h3>
            <p style="font-size: 13px; color: var(--text-muted); max-width: 420px; margin: 6px auto 0;">
                Saat pelanggan memesan dan mengisi nomor WhatsApp aktif lalu pembayaran diselesaikan, data stamp mereka otomatis muncul di sini.
            </p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;" id="stampsList">
            <?php foreach ($stamps as $s): ?>
                <?php
                    $target = (int)($store['store_loyalty_target'] ?? 10);
                    $count = (int)$s['stamps_count'];
                    $pct = min(100, round(($count / max(1, $target)) * 100));
                    $isRewardReady = $count >= $target;
                ?>
                <div class="stamp-item-card" data-search="<?= strtolower(esc($s['customer_phone'] . ' ' . ($s['customer_name'] ?? ''))) ?>" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 14px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: <?= $isRewardReady ? '#10B981' : 'rgba(234,88,12,0.15)' ?>; color: <?= $isRewardReady ? '#fff' : '#EA580C' ?>; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900;">
                            <?= $isRewardReady ? '🎁' : '⭐' ?>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 800;"><?= esc($s['customer_name'] ?: 'Pelanggan') ?></div>
                            <div style="font-size: 12.5px; color: var(--text-muted); font-family: monospace;">📱 <?= esc($s['customer_phone']) ?></div>
                        </div>
                    </div>

                    <div style="flex: 1; max-width: 260px; min-width: 180px;">
                        <div style="display: flex; justify-content: space-between; font-size: 11.5px; font-weight: 700; margin-bottom: 4px;">
                            <span>Progress Stamp</span>
                            <span style="color: <?= $isRewardReady ? '#10B981' : '#EA580C' ?>; font-weight: 800;">
                                <?= $count ?> / <?= $target ?> Stamp
                            </span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 6px;">
                            <div class="progress-bar <?= $isRewardReady ? 'bg-success' : 'bg-warning' ?>" style="width: <?= $pct ?>%;"></div>
                        </div>
                    </div>

                    <div>
                        <?php if ($isRewardReady): ?>
                            <span class="badge bg-success" style="font-size: 12px; padding: 6px 10px;">Siap Klaim Reward!</span>
                        <?php else: ?>
                            <span style="font-size: 12px; color: var(--text-muted);">Kurang <?= ($target - $count) ?> lagi</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
    function filterStamps() {
        const query = document.getElementById('stampSearch').value.toLowerCase().trim();
        document.querySelectorAll('.stamp-item-card').forEach(el => {
            const data = el.getAttribute('data-search') || '';
            el.style.display = data.includes(query) ? 'flex' : 'none';
        });
    }
</script>

<?= $this->endSection() ?>
