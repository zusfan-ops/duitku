<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="stats-page">
    <!-- Month Navigator -->
    <div class="stats-month-nav">
        <a href="/stats?month=<?= esc($prevMonth) ?>" class="month-nav-btn" title="Bulan sebelumnya">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div class="month-nav-center">
            <span class="month-nav-label"><?= esc($month) ?></span>
            <?php if (!$isCurrentMonth): ?>
            <a href="/stats" class="month-nav-today">Bulan ini</a>
            <?php endif; ?>
        </div>
        <a href="<?= $isCurrentMonth ? '#' : '/stats?month=' . esc($nextMonth) ?>"
           class="month-nav-btn <?= $isCurrentMonth ? 'disabled' : '' ?>" title="Bulan berikutnya">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </div>

    <div class="page-header" style="display:flex;align-items:center;justify-content:flex-end;gap:8px;margin-bottom:0">
        <a href="/export/pdf?month=<?= esc($monthKey) ?>" class="btn-export" title="Cetak laporan PDF" target="_blank" style="background:var(--primary);color:#0D1117;border-color:var(--primary)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
            </svg>
            PDF
        </a>
        <a href="/export/csv?month=<?= esc($monthKey) ?>" class="btn-export" title="Unduh CSV">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            CSV
        </a>
        <label class="btn-export" title="Import CSV" style="cursor:pointer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Import
            <input type="file" id="csvImportInput" accept=".csv" style="display:none">
        </label>
    </div>

    <!-- Summary Cards -->
    <div class="stats-summary">
        <div class="stats-card income">
            <span class="stats-card-label">Pemasukan</span>
            <span class="stats-card-value"><?= esc($symbol) ?> <?= number_format($monthly['income'], 0, ',', '.') ?></span>
        </div>
        <div class="stats-card expense">
            <span class="stats-card-label">Pengeluaran</span>
            <span class="stats-card-value"><?= esc($symbol) ?> <?= number_format($monthly['expense'], 0, ',', '.') ?></span>
        </div>
    </div>

    <!-- Pie Chart: Spending by category -->
    <div class="chart-card">
        <?php $expenseCats = array_filter($catStats, fn($c) => $c['type'] === 'expense'); ?>
        <?php if (empty($expenseCats)): ?>
        <div class="empty-state compact">
            <div class="empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40">
                    <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>
                </svg>
            </div>
            <p class="empty-title">Belum ada pengeluaran</p>
            <p class="empty-sub">Catat pengeluaran untuk melihat grafik.</p>
        </div>
        <?php else: ?>
        <canvas id="pieChart" height="250"></canvas>
        <!-- Category Legend -->
        <div class="cat-legend" id="catLegend">
            <?php foreach ($expenseCats as $cat): ?>
            <div class="cat-legend-item">
                <span class="cat-dot" style="background:<?= esc($cat['color']) ?>"></span>
                <span class="cat-legend-name"><?= esc($cat['category']) ?></span>
                <span class="cat-legend-val"><?= esc($symbol) ?> <?= number_format($cat['total'], 0, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Line Chart: Last 6 months -->
    <div class="chart-card">
        <h3 class="chart-title">Tren 6 Bulan Terakhir</h3>
        <?php if (empty($trend)): ?>
        <div class="empty-state compact">
            <p class="empty-title">Belum ada data</p>
        </div>
        <?php else: ?>
        <canvas id="barChart" height="200"></canvas>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    const catStats  = <?= json_encode(array_values(array_filter($catStats, fn($c) => $c['type'] === 'expense'))) ?>;
    const trendData = <?= json_encode($trend) ?>;
    const symbol    = '<?= esc($symbol) ?>';

    // Pie Chart
    if (catStats.length > 0 && document.getElementById('pieChart')) {
        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: catStats.map(c => c.category),
                datasets: [{
                    data:            catStats.map(c => parseFloat(c.total)),
                    backgroundColor: catStats.map(c => c.color),
                    borderWidth: 0,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${symbol} ${ctx.parsed.toLocaleString('id-ID')}`
                        }
                    }
                }
            }
        });
    }

    // Line Chart — 6-month trend
    if (trendData.length > 0 && document.getElementById('barChart')) {
        const isDark   = document.documentElement.getAttribute('data-theme') === 'dark';
        const mutedClr = isDark ? '#64748B' : '#94A3B8';
        const gridClr  = isDark ? 'rgba(30,48,80,.6)' : 'rgba(221,227,236,.7)';

        new Chart(document.getElementById('barChart'), {
            type: 'line',
            data: {
                labels: trendData.map(t => t.month_label),
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: trendData.map(t => parseFloat(t.income)),
                        borderColor: '#0AA956',
                        backgroundColor: 'rgba(10,169,86,.10)',
                        borderWidth: 2.5,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#0AA956',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Pengeluaran',
                        data: trendData.map(t => parseFloat(t.expense)),
                        borderColor: '#E53E3E',
                        backgroundColor: 'rgba(229,62,62,.08)',
                        borderWidth: 2.5,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#E53E3E',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: mutedClr,
                            font: { family: 'Inter', size: 12 },
                            boxWidth: 14,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#162032' : '#fff',
                        borderColor: isDark ? '#1E3050' : '#DDE3EC',
                        borderWidth: 1,
                        titleColor: isDark ? '#F1F5F9' : '#0F172A',
                        bodyColor: isDark ? '#CBD5E1' : '#475569',
                        padding: 12,
                        callbacks: {
                            label: (ctx) => ` ${ctx.dataset.label}: ${symbol} ${ctx.parsed.y.toLocaleString('id-ID')}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: mutedClr, font: { family: 'Inter', size: 11 } }
                    },
                    y: {
                        grid: { color: gridClr, drawBorder: false },
                        border: { display: false, dash: [4, 4] },
                        ticks: {
                            color: mutedClr,
                            font: { family: 'Inter', size: 11 },
                            callback: (v) => symbol + ' ' + (v >= 1000000 ? (v/1000000).toFixed(1) + 'jt' : v.toLocaleString('id-ID'))
                        }
                    }
                }
            }
        });
    }
})();

// CSV Import
(function() {
    const input = document.getElementById('csvImportInput');
    if (!input) return;
    input.addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;
        const fd = new FormData();
        fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
        fd.append('csv_file', file);
        try {
            const res  = await fetch('/import/csv', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
            const data = await res.json();
            if (data.success) {
                alert(`✅ Berhasil import ${data.imported} transaksi.\n${data.failed > 0 ? '⚠️ ' + data.failed + ' baris gagal (format tidak cocok).' : ''}`);
                location.reload();
            } else {
                alert('❌ ' + (data.message || 'Gagal import.'));
            }
        } catch(e) {
            alert('❌ Terjadi kesalahan saat upload.');
        }
        this.value = '';
    });
})();
</script>
<?= $this->endSection() ?>
