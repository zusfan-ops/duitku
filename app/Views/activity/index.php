<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="activity-page">
    <div class="page-header">
        <h1>Aktivitas</h1>
    </div>

    <!-- Search Bar -->
    <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="searchInput" class="search-input"
               placeholder="Cari catatan atau kategori..."
               value="<?= esc($search) ?>"
               autocomplete="off">
        <?php if ($search): ?>
        <button class="search-clear" id="searchClear" title="Hapus pencarian">✕</button>
        <?php endif; ?>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="/activity?type=all<?= $search ? '&search=' . urlencode($search) : '' ?>"
           class="filter-tab <?= $activeType === 'all' ? 'active' : '' ?>"
           id="filter-all">Semua</a>
        <a href="/activity?type=income<?= $search ? '&search=' . urlencode($search) : '' ?>"
           class="filter-tab <?= $activeType === 'income' ? 'active' : '' ?>"
           id="filter-income">Pemasukan</a>
        <a href="/activity?type=expense<?= $search ? '&search=' . urlencode($search) : '' ?>"
           class="filter-tab <?= $activeType === 'expense' ? 'active' : '' ?>"
           id="filter-expense">Pengeluaran</a>
    </div>

    <?php if ($search && empty($transactions)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </div>
        <p class="empty-title">Tidak ditemukan</p>
        <p class="empty-sub">Tidak ada transaksi yang cocok dengan "<?= esc($search) ?>"</p>
    </div>
    <?php elseif (empty($transactions)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                <line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/>
                <line x1="9" y1="15" x2="11" y2="15"/>
            </svg>
        </div>
        <p class="empty-title">Tidak ada transaksi</p>
        <p class="empty-sub">Tekan tombol + untuk mencatat transaksi.</p>
    </div>
    <?php else: ?>
    <?php if ($search): ?>
    <p style="font-size:12px;color:var(--text-muted);padding:0 2px 8px;">
        Menampilkan <?= $total ?> hasil untuk "<strong><?= esc($search) ?></strong>"
    </p>
    <?php endif; ?>

    <!-- Grouped by date -->
    <?php
        $grouped = [];
        foreach ($transactions as $tx) {
            $grouped[$tx['date']][] = $tx;
        }
    ?>
    <div class="tx-list" id="activityList">
        <?php foreach ($grouped as $date => $txs): ?>
        <div class="tx-date-group">
            <div class="tx-date-label">
                <?= date('d F Y', strtotime($date)) ?>
            </div>
            <?php foreach ($txs as $tx): ?>
            <div class="tx-item" data-id="<?= $tx['id'] ?>" data-tx='<?= json_encode($tx) ?>'>
                <div class="tx-icon" style="background:<?= esc($tx['category_color'] ?? '#6B7280') ?>20;color:<?= esc($tx['category_color'] ?? '#6B7280') ?>">
                    <?= categoryIcon($tx['category_icon'] ?? 'other') ?>
                </div>
                <div class="tx-body">
                    <div class="tx-name"><?= esc($tx['category_name'] ?? 'Tanpa Kategori') ?></div>
                    <div class="tx-note">
                        <?= esc($tx['note'] ?? '') ?>
                        <?php if (!empty($tx['image'])): ?>
                            <span title="Ada Foto" style="margin-left:4px; opacity:0.6">📷</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="tx-right">
                    <div class="tx-amount <?= $tx['type'] === 'income' ? 'income' : 'expense' ?>">
                        <?= $tx['type'] === 'income' ? '+' : '-' ?> <?= esc($symbol) ?> <?= number_format($tx['amount'], 0, ',', '.') ?>
                    </div>
                    <div class="tx-actions">
                        <button class="tx-edit-btn" title="Edit">✏️</button>
                        <button class="tx-delete-btn" title="Hapus">🗑️</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="/activity?type=<?= $activeType ?>&page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="page-btn">← Sebelumnya</a>
        <?php endif; ?>
        <span class="page-info">Halaman <?= $page ?> / <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a href="/activity?type=<?= $activeType ?>&page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="page-btn">Berikutnya →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Delete Confirm Modal -->
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <p>Yakin hapus transaksi ini?</p>
        <div class="confirm-actions">
            <button class="btn-cancel" id="confirmCancel">Batal</button>
            <button class="btn-danger" id="confirmDelete">Hapus</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    const searchInput = document.getElementById('searchInput');
    const searchClear = document.getElementById('searchClear');
    let searchTimer;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                const q = this.value.trim();
                const url = new URL(window.location.href);
                if (q) {
                    url.searchParams.set('search', q);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }, 500);
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function() {
            const url = new URL(window.location.href);
            url.searchParams.delete('search');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }
})();
</script>
<?= $this->endSection() ?>
