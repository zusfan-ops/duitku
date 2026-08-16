<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System (KDS) — <?= esc($store['store_name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-kds: #0B0F19;
            --card-kds: #151D2E;
            --card-border: #1E293B;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --accent-orange: #F97316;
            --accent-green: #10B981;
            --accent-blue: #38BDF8;
            --accent-purple: #A855F7;
            --accent-yellow: #FBBF24;
            --accent-red: #EF4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-kds);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        .kds-header {
            background: #0F172A;
            border-bottom: 2px solid #1E293B;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .kds-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .kds-badge {
            background: var(--accent-orange);
            color: #fff;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .kds-clock {
            font-family: 'JetBrains Mono', monospace;
            font-size: 20px;
            font-weight: 800;
            color: var(--accent-blue);
        }
        .kds-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
            padding: 20px;
            flex: 1;
        }
        .kds-card {
            background: var(--card-kds);
            border: 2px solid var(--card-border);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
            transition: transform 0.15s ease, border-color 0.15s ease;
        }
        .kds-card.status-pending {
            border-color: var(--accent-yellow);
        }
        .kds-card.status-processing {
            border-color: var(--accent-blue);
        }
        .kds-card-head {
            padding: 14px 16px;
            background: rgba(255,255,255,0.03);
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .kds-type-badge {
            font-size: 11px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
        }
        .kds-type-dine { background: rgba(249, 115, 22, 0.2); color: #FDBA74; border: 1px solid rgba(249, 115, 22, 0.4); }
        .kds-type-delivery { background: rgba(168, 85, 247, 0.2); color: #D8B4FE; border: 1px solid rgba(168, 85, 247, 0.4); }
        .kds-type-takeaway { background: rgba(56, 189, 248, 0.2); color: #7DD3FC; border: 1px solid rgba(56, 189, 248, 0.4); }

        .kds-timer {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 800;
            color: var(--accent-yellow);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .kds-card-body {
            padding: 16px;
            flex: 1;
        }
        .kds-order-num {
            font-size: 17px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 2px;
        }
        .kds-customer {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 12px;
        }
        .kds-items-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .kds-item-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.3;
        }
        .kds-item-qty {
            background: rgba(255,255,255,0.1);
            color: var(--accent-blue);
            min-width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 14px;
        }
        .kds-item-details {
            flex: 1;
        }
        .kds-item-notes {
            font-size: 12px;
            font-weight: 600;
            color: #FCD34D;
            background: rgba(245, 158, 11, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
            margin-top: 3px;
            display: inline-block;
        }
        .kds-item-variant {
            font-size: 11.5px;
            font-weight: 600;
            color: #93C5FD;
            margin-top: 2px;
        }
        .kds-card-footer {
            padding: 12px 16px;
            background: rgba(0,0,0,0.2);
            border-top: 1px solid var(--card-border);
            display: flex;
            gap: 8px;
        }
        .kds-btn {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 800;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: opacity 0.15s ease;
        }
        .kds-btn:active { transform: scale(0.98); }
        .kds-btn-cook { background: #2563EB; color: #fff; }
        .kds-btn-done { background: #10B981; color: #fff; }
        .kds-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<header class="kds-header">
    <div class="kds-title">
        <span class="kds-badge">KDS DAPUR</span>
        <h1 style="font-size: 18px; font-weight: 800;"><?= esc($store['store_name']) ?></h1>
    </div>
    <div style="display: flex; align-items: center; gap: 20px;">
        <div id="activeCount" style="font-size: 13px; font-weight: 700; color: #94A3B8;">0 Pesanan Antre</div>
        <div class="kds-clock" id="liveClock">00:00:00</div>
        <a href="/pos/orders" style="color: #94A3B8; text-decoration: none; font-size: 12.5px; font-weight: 700; padding: 6px 12px; border: 1px solid #334155; border-radius: 8px;">✕ Keluar KDS</a>
    </div>
</header>

<main class="kds-grid" id="kdsGrid">
    <?php if (empty($kitchenOrders)): ?>
        <div class="kds-empty">
            <div style="font-size: 48px; margin-bottom: 12px;">👨‍🍳</div>
            <h3 style="font-size: 18px; font-weight: 800; color: #fff;">Semua Pesanan Sudah Selesai Dimasak!</h3>
            <p style="font-size: 13px; margin-top: 4px;">Pesanan baru yang masuk dari meja atau online delivery akan otomatis muncul di sini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($kitchenOrders as $ord): ?>
            <?php
                $type = $ord['order_type'] ?? 'dine_in';
                $badgeClass = 'kds-type-dine';
                $badgeLabel = '🪑 MEJA ' . ($ord['table_no'] ?: '-');
                if ($type === 'delivery') {
                    $badgeClass = 'kds-type-delivery';
                    $badgeLabel = '🛵 DELIVERY';
                } elseif ($type === 'takeaway') {
                    $badgeClass = 'kds-type-takeaway';
                    $badgeLabel = '🛍️ TAKEAWAY';
                }
            ?>
            <div class="kds-card status-<?= esc($ord['status']) ?>" id="card-<?= $ord['id'] ?>">
                <div class="kds-card-head">
                    <span class="kds-type-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                    <div class="kds-timer" data-time="<?= esc($ord['created_at']) ?>">⏱️ <span class="timer-val">00:00</span></div>
                </div>
                <div class="kds-card-body">
                    <div class="kds-order-num">#<?= esc($ord['order_number']) ?></div>
                    <div class="kds-customer"><?= esc($ord['customer_name']) ?></div>
                    <ul class="kds-items-list">
                        <?php foreach ($ord['items'] as $item): ?>
                            <li class="kds-item-row">
                                <div class="kds-item-qty"><?= (int)$item['qty'] ?>x</div>
                                <div class="kds-item-details">
                                    <div><?= esc($item['product_name']) ?></div>
                                    <?php if (!empty($item['selected_variants'])): ?>
                                        <div class="kds-item-variant">✨ <?= esc($item['selected_variants']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['notes'])): ?>
                                        <div class="kds-item-notes">📝 <?= esc($item['notes']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="kds-card-footer">
                    <?php if ($ord['status'] === 'pending'): ?>
                        <button class="kds-btn kds-btn-cook" onclick="updateKdsStatus(<?= $ord['id'] ?>, 'processing')">🍳 Mulai Masak</button>
                    <?php else: ?>
                        <?php
                            $targetStatus = ($type === 'delivery') ? 'delivering' : (($type === 'takeaway') ? 'delivering' : 'served_unpaid');
                            $btnLabel = ($type === 'delivery') ? '🛵 Siap Diantar' : (($type === 'takeaway') ? '🛍️ Siap Diambil' : '🍽️ Sajikan');
                        ?>
                        <button class="kds-btn kds-btn-done" onclick="updateKdsStatus(<?= $ord['id'] ?>, '<?= $targetStatus ?>')">✅ <?= $btnLabel ?></button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<script>
    // Live Clock
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').textContent = now.toTimeString().split(' ')[0];
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Elapsed Timers
    function updateTimers() {
        const now = new Date().getTime();
        document.querySelectorAll('.kds-timer').forEach(el => {
            const timeStr = el.getAttribute('data-time');
            if (!timeStr) return;
            const orderTime = new Date(timeStr.replace(/-/g, '/')).getTime();
            const diffSec = Math.max(0, Math.floor((now - orderTime) / 1000));
            const m = Math.floor(diffSec / 60);
            const s = diffSec % 60;
            el.querySelector('.timer-val').textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            if (m >= 15) {
                el.style.color = 'var(--accent-red)';
            } else if (m >= 8) {
                el.style.color = 'var(--accent-yellow)';
            } else {
                el.style.color = 'var(--accent-green)';
            }
        });
    }
    setInterval(updateTimers, 1000);
    updateTimers();

    // Realtime Polling
    let lastOrderCount = <?= count($kitchenOrders) ?>;
    async function pollKds() {
        try {
            const res = await fetch('/pos/kds/poll');
            const data = await res.json();
            if (data.success) {
                document.getElementById('activeCount').textContent = `${data.count} Pesanan Antre`;
                if (data.count > lastOrderCount) {
                    playBell();
                }
                lastOrderCount = data.count;
            }
        } catch (e) {}
    }
    setInterval(pollKds, 4000);

    function playBell() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.4, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.6);
            osc.start();
            osc.stop(ctx.currentTime + 0.6);
        } catch(e) {}
    }

    async function updateKdsStatus(orderId, status) {
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('status', status);

        try {
            const res = await fetch('/pos/orders/update-status', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal mengubah status');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
    }
</script>

</body>
</html>
