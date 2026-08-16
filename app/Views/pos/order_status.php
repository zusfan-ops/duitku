<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Status Pesanan #<?= esc($order['order_number']) ?> — <?= esc($store['store_name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #EA580C;
            --bg: #0F172A;
            --card-bg: #1E293B;
            --border: #334155;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --accent-green: #10B981;
            --accent-amber: #F59E0B;
            --accent-red: #EF4444;
            --accent-blue: #3B82F6;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            padding: 20px 16px 60px;
        }

        .status-container {
            max-width: 520px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Top Header */
        .status-header-card {
            background: linear-gradient(180deg, #1E293B 0%, #0F172A 100%);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
        }
        .store-name-badge {
            font-size: 12px;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
        }
        .order-code-title {
            font-size: 22px;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.3px;
        }
        .order-meta-info {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Dynamic Status Card */
        .status-highlight-card {
            border-radius: 20px;
            padding: 24px 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        /* Status colors */
        .status-pending {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.25) 100%);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #FBBF24;
        }
        .status-processing {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(37, 99, 235, 0.25) 100%);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #60A5FA;
        }
        .status-delivering {
            background: linear-gradient(135deg, rgba(147, 51, 234, 0.2) 0%, rgba(126, 34, 206, 0.3) 100%);
            border: 2px solid #A855F7;
            color: #C084FC;
            animation: pulseDelivering 2s infinite ease-in-out;
        }
        .status-served_unpaid, .status-delivered_unpaid {
            background: linear-gradient(135deg, rgba(234, 88, 12, 0.25) 0%, rgba(194, 65, 12, 0.35) 100%);
            border: 2px solid #EA580C;
            color: #FB923C;
            animation: pulseServed 2s infinite ease-in-out;
        }
        .status-paid {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.25) 100%);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #34D399;
        }
        .status-cancelled {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(185, 28, 28, 0.25) 100%);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #F87171;
        }

        .status-big-icon { font-size: 46px; line-height: 1; margin-bottom: 4px; }
        .status-big-label { font-size: 18px; font-weight: 900; }
        .status-big-desc { font-size: 12.5px; color: #CBD5E1; max-width: 320px; line-height: 1.4; }

        /* Timeline tracker */
        .timeline-box {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .timeline-step {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            position: relative;
        }
        .timeline-step::before {
            content: '';
            position: absolute;
            left: 14px;
            top: 28px;
            bottom: -18px;
            width: 2px;
            background: var(--border);
        }
        .timeline-step:last-child::before { display: none; }
        
        .step-icon-wrap {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #334155;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            flex-shrink: 0;
            z-index: 1;
        }
        .timeline-step.active .step-icon-wrap {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 0 14px rgba(234, 88, 12, 0.6);
        }
        .timeline-step.completed .step-icon-wrap {
            background: var(--accent-green);
            color: #fff;
        }
        .step-content { flex: 1; }
        .step-title { font-size: 13.5px; font-weight: 800; color: #fff; }
        .timeline-step:not(.active):not(.completed) .step-title { color: var(--text-muted); }
        .step-desc { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }

        /* Order Details Card */
        .order-details-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
        }
        .card-heading {
            font-size: 14px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .order-item-line {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed var(--border);
            font-size: 13px;
        }
        .order-item-line:last-child { border-bottom: none; }
        .item-line-left { flex: 1; }
        .item-line-name { font-weight: 700; color: #F1F5F9; }
        .item-line-note { font-size: 11px; color: #FB923C; margin-top: 2px; }
        .item-line-price { font-weight: 800; color: #CBD5E1; text-align: right; }

        .total-summary-box {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 2px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .total-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: var(--text-muted);
        }
        .total-label-final { font-size: 15px; font-weight: 800; color: #fff; }
        .total-value-final { font-size: 18px; font-weight: 900; color: #FB923C; }

        /* Action Buttons */
        .btn-order-more {
            background: #334155;
            color: #fff;
            border: 1px solid var(--border);
            padding: 13px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 800;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: background 0.15s;
        }
        .btn-order-more:active { background: #475569; }

        .btn-wa-store {
            background: #15803D;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 14px;
            font-size: 13.5px;
            font-weight: 800;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        @keyframes pulseServed {
            0%, 100% { box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.4); }
            50% { box-shadow: 0 0 20px 6px rgba(234, 88, 12, 0.4); }
        }
        @keyframes pulseDelivering {
            0%, 100% { box-shadow: 0 0 0 0 rgba(168, 85, 247, 0.4); }
            50% { box-shadow: 0 0 20px 6px rgba(168, 85, 247, 0.4); }
        }
    </style>
</head>
<body>

    <div class="status-container">
        
        <!-- Header -->
        <div class="status-header-card">
            <div class="store-name-badge"><?= esc($store['store_name']) ?></div>
            <div class="order-code-title">Pesanan #<?= esc($order['order_number']) ?></div>
            <div class="order-meta-info">
                <?php if (($order['order_type'] ?? '') === 'delivery'): ?>
                    <span>🛵 Delivery</span>
                <?php elseif (($order['order_type'] ?? '') === 'takeaway'): ?>
                    <span>🛍️ Ambil di Toko</span>
                <?php else: ?>
                    <span>🪑 <?= esc($order['table_no'] ?: 'Makan di Tempat') ?></span>
                <?php endif; ?>
                <span>•</span>
                <span>👤 <?= esc($order['customer_name'] ?: 'Pelanggan') ?></span>
            </div>
        </div>

        <!-- Live Highlight Status Card -->
        <div id="liveStatusCard" class="status-highlight-card status-<?= esc($order['status']) ?>">
            <?php
                $isDelivery = ($order['order_type'] ?? '') === 'delivery';
                $isTakeaway = ($order['order_type'] ?? '') === 'takeaway';

                $statusConfig = [
                    'pending' => [
                        'icon' => '🔔',
                        'label' => 'Menunggu Konfirmasi Toko',
                        'desc' => 'Pesanan Anda sudah masuk ke sistem dan segera disiapkan.',
                    ],
                    'processing' => [
                        'icon' => '🍳',
                        'label' => 'Sedang Disiapkan / Dikemas',
                        'desc' => 'Toko sedang meracik, memasak, atau mengemas produk pesanan Anda.',
                    ],
                    'delivering' => [
                        'icon' => '🛵',
                        'label' => 'Sedang Dikirim Kurir',
                        'desc' => 'Kurir sedang dalam perjalanan menuju alamat Anda. Siapkan uang pas jika bayar COD.',
                    ],
                    'served_unpaid' => [
                        'icon' => '🍽️',
                        'label' => 'Sudah Disajikan (Belum Bayar)',
                        'desc' => 'Pesanan sudah disajikan ke meja Anda. Silakan menuju kasir untuk melakukan pembayaran.',
                    ],
                    'delivered_unpaid' => [
                        'icon' => '📦',
                        'label' => 'Pesanan Telah Sampai (COD)',
                        'desc' => 'Pesanan sudah diterima. Silakan serahkan pembayaran COD kepada kurir toko.',
                    ],
                    'paid' => [
                        'icon' => '✅',
                        'label' => 'Pesanan Selesai & Lunas',
                        'desc' => 'Terima kasih telah berbelanja di ' . esc($store['store_name']) . '! Selamat menikmati.',
                    ],
                    'cancelled' => [
                        'icon' => '❌',
                        'label' => 'Pesanan Dibatalkan',
                        'desc' => 'Pesanan ini telah dibatalkan oleh pihak toko.',
                    ],
                ];
                $cur = $statusConfig[$order['status']] ?? $statusConfig['pending'];
            ?>
            <div class="status-big-icon" id="statusBigIcon"><?= $cur['icon'] ?></div>
            <div class="status-big-label" id="statusBigLabel"><?= $cur['label'] ?></div>
            <div class="status-big-desc" id="statusBigDesc"><?= $cur['desc'] ?></div>
        </div>

        <!-- Timeline Progress Box -->
        <div class="timeline-box">
            
            <!-- Step 1: Pending -->
            <div class="timeline-step <?= in_array($order['status'], ['pending', 'processing', 'delivering', 'served_unpaid', 'delivered_unpaid', 'paid']) ? ($order['status'] === 'pending' ? 'active' : 'completed') : '' ?>" id="step-pending">
                <div class="step-icon-wrap">1</div>
                <div class="step-content">
                    <div class="step-title">Pesanan Diterima Toko</div>
                    <div class="step-desc">Toko menerima notifikasi pesanan</div>
                </div>
            </div>

            <!-- Step 2: Processing -->
            <div class="timeline-step <?= in_array($order['status'], ['processing', 'delivering', 'served_unpaid', 'delivered_unpaid', 'paid']) ? ($order['status'] === 'processing' ? 'active' : 'completed') : '' ?>" id="step-processing">
                <div class="step-icon-wrap">2</div>
                <div class="step-content">
                    <div class="step-title">Sedang Disiapkan / Dikemas</div>
                    <div class="step-desc">Pesanan sedang dimasak / dikemas rapi</div>
                </div>
            </div>

            <!-- Step 3: Delivering / Served -->
            <div class="timeline-step <?= in_array($order['status'], ['delivering', 'served_unpaid', 'delivered_unpaid', 'paid']) ? (in_array($order['status'], ['delivering', 'served_unpaid', 'delivered_unpaid']) ? 'active' : 'completed') : '' ?>" id="step-transit">
                <div class="step-icon-wrap">3</div>
                <div class="step-content">
                    <div class="step-title"><?= $isDelivery ? 'Sedang Dikirim / Antar' : ($isTakeaway ? 'Siap Diambil di Toko' : 'Disajikan ke Meja') ?></div>
                    <div class="step-desc"><?= $isDelivery ? 'Kurir membawa pesanan ke lokasi Anda' : ($isTakeaway ? 'Pesanan siap diambil di kasir' : 'Pesanan diantar ke meja Anda') ?></div>
                </div>
            </div>

            <!-- Step 4: Paid / Selesai -->
            <div class="timeline-step <?= ($order['status'] === 'paid') ? 'active completed' : '' ?>" id="step-paid">
                <div class="step-icon-wrap">4</div>
                <div class="step-content">
                    <div class="step-title">Selesai & Lunas</div>
                    <div class="step-desc">Pesanan selesai diterima & lunas</div>
                </div>
            </div>

        </div>

        <!-- Order Items & Delivery Info Detail -->
        <div class="order-details-card">
            <div class="card-heading">
                <span>📋 Rincian Pesanan</span>
                <span style="font-size:12px;color:var(--text-muted);font-weight:600"><?= count($order['items'] ?? []) ?> Item</span>
            </div>

            <?php if (!empty($order['delivery_address'])): ?>
                <div style="background:#0F172A;border:1px solid var(--border);border-radius:12px;padding:12px;margin-bottom:12px;font-size:12.5px">
                    <div style="font-weight:800;color:#FB923C;margin-bottom:4px">📍 Alamat Pengiriman:</div>
                    <div style="color:#F1F5F9"><?= nl2br(esc($order['delivery_address'])) ?></div>
                    <?php if (!empty($order['delivery_notes'])): ?>
                        <div style="color:var(--text-muted);margin-top:4px">🏡 Patokan: <?= esc($order['delivery_notes']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div id="itemsContainer">
                <?php foreach (($order['items'] ?? []) as $it): ?>
                    <div class="order-item-line">
                        <div class="item-line-left">
                            <div class="item-line-name"><?= esc($it['product_name']) ?> <span style="color:var(--primary)">x<?= $it['qty'] ?></span></div>
                            <?php if (!empty($it['selected_variants'])): ?>
                                <div style="font-size:11.5px;color:#93C5FD;margin-top:2px">✨ <?= esc($it['selected_variants']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($it['notes'])): ?>
                                <div class="item-line-note">📝 Catatan: <?= esc($it['notes']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="item-line-price">
                            <?= esc($symbol) ?> <?= number_format($it['subtotal'], 0, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="total-summary-box">
                <?php if ((float)($order['delivery_fee'] ?? 0) > 0): ?>
                    <div class="total-row">
                        <span>Ongkos Kirim (Delivery)</span>
                        <span><?= esc($symbol) ?> <?= number_format((float)$order['delivery_fee'], 0, ',', '.') ?></span>
                    </div>
                <?php endif; ?>
                <?php if ((float)($order['discount_amount'] ?? 0) > 0): ?>
                    <div class="total-row" style="color:#10B981;font-weight:700">
                        <span>Diskon Kupon Promo (<?= esc($order['voucher_code'] ?: 'PROMO') ?>)</span>
                        <span>-<?= esc($symbol) ?> <?= number_format((float)$order['discount_amount'], 0, ',', '.') ?></span>
                    </div>
                <?php endif; ?>
                <div class="total-row" style="margin-top:4px">
                    <span class="total-label-final">Total Tagihan (<?= strtoupper(esc($order['payment_method'] ?? 'COD')) ?>)</span>
                    <span class="total-value-final"><?= esc($symbol) ?> <?= number_format($order['total_amount'], 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Loyalty Stamp Box if store enabled -->
            <?php if (!empty($order['customer_phone']) && ($store['store_loyalty_enabled'] ?? false)): ?>
                <?php
                    $loyaltyModel = new \App\Models\PosLoyaltyModel();
                    $stampRec = $loyaltyModel->getCustomerStamps((int)$store['user_id'], $order['customer_phone']);
                    $stampsCount = $stampRec ? (int)$stampRec['stamps_count'] : 0;
                    $target = (int)($store['store_loyalty_target'] ?? 10);
                    $pct = min(100, round(($stampsCount / max(1, $target)) * 100));
                ?>
                <div style="background:rgba(234,88,12,0.1);border:1px dashed rgba(234,88,12,0.4);border-radius:12px;padding:12px;margin-top:14px">
                    <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:800;color:#FB923C;margin-bottom:6px">
                        <span>⭐ Stamp Loyalitas Anda</span>
                        <span><?= $stampsCount ?> / <?= $target ?> Stamp</span>
                    </div>
                    <div class="progress" style="height:6px;border-radius:4px;background:rgba(255,255,255,0.1)">
                        <div class="progress-bar bg-warning" style="width:<?= $pct ?>%"></div>
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:6px">
                        Kumpulkan <?= $target ?> stamp untuk mendapatkan reward: <strong><?= esc($store['store_loyalty_reward'] ?? 'Gratis 1 Menu Favorit') ?></strong>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Contact Store WhatsApp -->
        <?php if (!empty($store['store_phone'])): ?>
            <?php
                $cleanPhone = preg_replace('/[^0-9]/', '', $store['store_phone']);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
                $waMsg = urlencode("Halo {$store['store_name']}, saya ingin menanyakan status pesanan #{$order['order_number']}. Terima kasih!");
            ?>
            <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $waMsg ?>" target="_blank" class="btn-wa-store">
                <span>💬</span> Hubungi Toko via WhatsApp
            </a>
        <?php endif; ?>

        <!-- Actions -->
        <a href="/menu/<?= urlencode($slug) ?>" class="btn-order-more">
            <span>+</span> Belanja / Pesan Lagi
        </a>

        <div style="text-align:center;font-size:11px;color:var(--text-muted);margin-top:8px">
            Halaman ini otomatis diperbarui secara realtime saat toko memproses pesanan Anda.
        </div>

    </div>

    <script>
        const STORE_SLUG = "<?= esc($slug) ?>";
        const ORDER_NUMBER = "<?= esc($order['order_number']) ?>";
        let currentStatus = "<?= esc($order['status']) ?>";

        const statusConfig = {
            'pending': {
                'icon': '🔔',
                'label': 'Menunggu Konfirmasi Toko',
                'desc': 'Pesanan Anda sudah masuk ke sistem dan segera disiapkan.'
            },
            'processing': {
                'icon': '🍳',
                'label': 'Sedang Disiapkan / Dikemas',
                'desc': 'Toko sedang meracik, memasak, atau mengemas produk pesanan Anda.'
            },
            'delivering': {
                'icon': '🛵',
                'label': 'Sedang Dikirim Kurir',
                'desc': 'Kurir sedang dalam perjalanan menuju alamat Anda. Siapkan uang pas jika bayar COD.'
            },
            'served_unpaid': {
                'icon': '🍽️',
                'label': 'Sudah Disajikan (Belum Bayar)',
                'desc': 'Pesanan sudah disajikan ke meja Anda. Silakan menuju kasir untuk melakukan pembayaran.'
            },
            'delivered_unpaid': {
                'icon': '📦',
                'label': 'Pesanan Telah Sampai (COD)',
                'desc': 'Pesanan sudah diterima. Silakan serahkan pembayaran COD kepada kurir toko.'
            },
            'paid': {
                'icon': '✅',
                'label': 'Pesanan Selesai & Lunas',
                'desc': 'Terima kasih telah berbelanja di <?= addslashes(esc($store['store_name'])) ?>!'
            },
            'cancelled': {
                'icon': '❌',
                'label': 'Pesanan Dibatalkan',
                'desc': 'Pesanan ini telah dibatalkan oleh pihak toko.'
            }
        };

        // Realtime Polling
        setInterval(async () => {
            try {
                const res = await fetch('/menu/' + encodeURIComponent(STORE_SLUG) + '/status-poll/' + encodeURIComponent(ORDER_NUMBER));
                const data = await res.json();
                if (data.success && data.status !== currentStatus) {
                    currentStatus = data.status;
                    updateLiveUI(data.status);
                }
            } catch (e) {
                console.warn('Polling error:', e);
            }
        }, 4000);

        function updateLiveUI(status) {
            const card = document.getElementById('liveStatusCard');
            const conf = statusConfig[status] || statusConfig['pending'];

            card.className = 'status-highlight-card status-' + status;
            document.getElementById('statusBigIcon').textContent = conf.icon;
            document.getElementById('statusBigLabel').textContent = conf.label;
            document.getElementById('statusBigDesc').textContent = conf.desc;

            // Update Steps
            const allSteps = ['pending', 'processing', 'transit', 'paid'];
            allSteps.forEach(s => {
                const el = document.getElementById('step-' + s);
                if (el) el.className = 'timeline-step';
            });

            if (status === 'pending') {
                document.getElementById('step-pending').classList.add('active');
            } else if (status === 'processing') {
                document.getElementById('step-pending').classList.add('completed');
                document.getElementById('step-processing').classList.add('active');
            } else if (status === 'delivering' || status === 'served_unpaid' || status === 'delivered_unpaid') {
                document.getElementById('step-pending').classList.add('completed');
                document.getElementById('step-processing').classList.add('completed');
                document.getElementById('step-transit').classList.add('active');
            } else if (status === 'paid') {
                document.getElementById('step-pending').classList.add('completed');
                document.getElementById('step-processing').classList.add('completed');
                document.getElementById('step-transit').classList.add('completed');
                document.getElementById('step-paid').classList.add('active', 'completed');
            }
        }
    </script>
</body>
</html>
