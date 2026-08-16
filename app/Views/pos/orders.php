<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.orders-page {
    padding: 12px 14px 110px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Header & Store Profile Bar */
.orders-top-bar {
    background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.store-meta-box {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.store-name-text {
    font-size: 16px;
    font-weight: 900;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 6px;
}
.store-slug-badge {
    font-size: 11px;
    color: var(--primary);
    background: rgba(234, 88, 12, 0.12);
    padding: 2px 8px;
    border-radius: 8px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.top-action-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.btn-top-pos {
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-primary);
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-top-pos:hover { border-color: var(--primary); color: var(--primary); }

/* Tab Filter Scroll */
.order-tabs-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: none;
}
.order-tabs-scroll::-webkit-scrollbar { display: none; }

.tab-order-pill {
    padding: 7px 14px;
    border-radius: 20px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}
.tab-order-pill.active {
    background: #EA580C;
    border-color: #EA580C;
    color: #fff;
}
.tab-order-pill.served-tab {
    border-color: #F59E0B;
}
.tab-order-pill.served-tab.active {
    background: #D97706;
    border-color: #D97706;
}
.tab-count-badge {
    background: rgba(0, 0, 0, 0.25);
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10.5px;
    font-weight: 800;
}

/* Orders Grid / List */
.orders-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.order-manage-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: transform 0.1s ease, box-shadow 0.15s ease;
    position: relative;
    overflow: hidden;
}
.order-manage-card.status-served-unpaid {
    border: 2px solid #F59E0B;
    background: linear-gradient(180deg, var(--bg-card) 0%, rgba(245, 158, 11, 0.05) 100%);
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.15);
}
.order-manage-card.status-pending {
    border: 2px solid #EA580C;
    animation: flashOrder 2s infinite ease-in-out;
}

.order-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}
.order-table-badge {
    background: linear-gradient(135deg, #EA580C 0%, #FB923C 100%);
    color: #fff;
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 900;
    letter-spacing: -0.2px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.order-source-pill {
    font-size: 10.5px;
    padding: 2px 8px;
    border-radius: 6px;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-muted);
    font-weight: 700;
}

.status-badge-pill {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.status-pill-pending { background: rgba(234, 88, 12, 0.15); color: #FB923C; border: 1px solid rgba(234, 88, 12, 0.4); }
.status-pill-processing { background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.4); }
.status-pill-served { background: #FEF3C7; color: #B45309; border: 1.5px solid #F59E0B; font-weight: 900; }
.status-pill-paid { background: rgba(16, 185, 129, 0.15); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.4); }
.status-pill-cancelled { background: rgba(239, 68, 68, 0.15); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.4); }

/* Items in Order Card */
.order-items-box {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.order-item-row-pos {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    font-size: 13px;
    gap: 8px;
}
.item-name-pos { font-weight: 700; color: var(--text-primary); }
.item-note-pos { font-size: 11px; color: #EA580C; margin-top: 1px; font-weight: 600; }
.item-sub-pos { font-weight: 800; color: var(--text-secondary); white-space: nowrap; }

.order-footer-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 4px;
}
.order-total-price {
    font-size: 16px;
    font-weight: 900;
    color: #EA580C;
}

/* Actions Footer */
.order-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 8px;
    margin-top: 4px;
}
.btn-action-pos {
    padding: 9px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    transition: transform 0.1s ease;
}
.btn-action-pos:active { transform: scale(0.96); }

.btn-act-process { background: #2563EB; color: #fff; }
.btn-act-serve { background: #D97706; color: #fff; }
.btn-act-pay { background: #10B981; color: #fff; }
.btn-act-cancel { background: var(--bg); border: 1px solid var(--border); color: #EF4444; }
.btn-act-print { background: var(--bg); border: 1px solid var(--border); color: var(--text-primary); }

/* Quick Settlement Modal */
.modal-settle-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(6px);
    z-index: 100;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal-settle-overlay.active { display: flex; }
.settle-dialog {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    width: 100%;
    max-width: 440px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: zoomModal 0.2s ease-out;
}
.settle-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.settle-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.settle-pay-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
.pay-pill-opt {
    padding: 10px;
    border-radius: 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-primary);
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}
.pay-pill-opt.selected {
    border-color: var(--primary);
    background: var(--primary-dim);
    color: var(--primary);
}

@keyframes flashOrder {
    0%, 100% { border-color: #EA580C; box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.4); }
    50% { border-color: #FB923C; box-shadow: 0 0 16px 4px rgba(234, 88, 12, 0.35); }
}
@keyframes zoomModal {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="orders-page">

    <!-- Store Profile & Fast Navigation Bar -->
    <div class="orders-top-bar">
        <div class="store-meta-box">
            <div class="store-name-text">
                <span>🏪</span> <?= esc($store['store_name']) ?>
            </div>
            <div>
                <a href="/menu/<?= esc($store['store_slug']) ?>" target="_blank" class="store-slug-badge">
                    <span>🔗</span> /menu/<?= esc($store['store_slug']) ?> ↗
                </a>
            </div>
        </div>

        <div class="top-action-group">
            <a href="/pos/shifts" class="btn-top-pos">
                <span>💼</span> Shift
            </a>
            <a href="/pos/kds" target="_blank" class="btn-top-pos" style="background:#1E293B;color:#38BDF8;border-color:#38BDF8">
                <span>👨‍🍳</span> KDS Dapur
            </a>
            <a href="/pos/vouchers" class="btn-top-pos">
                <span>🏷️</span> Kupon
            </a>
            <a href="/pos/loyalty" class="btn-top-pos">
                <span>⭐</span> Stamp
            </a>
            <a href="/pos/qr" class="btn-top-pos">
                <span>📱</span> Cetak QR
            </a>
            <button class="btn-top-pos" onclick="openStoreSettingModal()">
                <span>⚙️</span> Toko
            </button>
            <a href="/pos" class="btn-top-pos" style="background:#EA580C;color:#fff;border-color:#EA580C">
                <span>💻</span> Kasir
            </a>
        </div>
    </div>

    <!-- Filter Tabs Scroll -->
    <div class="order-tabs-scroll" id="orderTabs">
        <a href="/pos/orders?status=all" class="tab-order-pill <?= $currentTab === 'all' ? 'active' : '' ?>">
            <span>Semua</span>
            <span class="tab-count-badge"><?= $counts['all'] ?? 0 ?></span>
        </a>
        <a href="/pos/orders?status=pending" class="tab-order-pill <?= $currentTab === 'pending' ? 'active' : '' ?>">
            <span>🔔 Baru</span>
            <span class="tab-count-badge"><?= $counts['pending'] ?? 0 ?></span>
        </a>
        <a href="/pos/orders?status=processing" class="tab-order-pill <?= $currentTab === 'processing' ? 'active' : '' ?>">
            <span>🍳 Diproses / Dikemas</span>
            <span class="tab-count-badge"><?= $counts['processing'] ?? 0 ?></span>
        </a>
        <a href="/pos/orders?status=delivering" class="tab-order-pill <?= $currentTab === 'delivering' ? 'active' : '' ?>">
            <span>🛵 Sedang Dikirim / Diambil</span>
            <span class="tab-count-badge"><?= $counts['delivering'] ?? 0 ?></span>
        </a>
        <!-- Served Unpaid & Delivered Unpaid Tab with distinct highlight -->
        <a href="/pos/orders?status=served_unpaid" class="tab-order-pill served-tab <?= ($currentTab === 'served_unpaid' || $currentTab === 'delivered_unpaid') ? 'active' : '' ?>">
            <span>⚠️ Belum Bayar (COD / Meja)</span>
            <span class="tab-count-badge"><?= ($counts['served_unpaid'] ?? 0) + ($counts['delivered_unpaid'] ?? 0) ?></span>
        </a>
        <a href="/pos/orders?status=paid" class="tab-order-pill <?= $currentTab === 'paid' ? 'active' : '' ?>">
            <span>✅ Selesai</span>
            <span class="tab-count-badge"><?= $counts['paid'] ?? 0 ?></span>
        </a>
        <a href="/pos/orders?status=cancelled" class="tab-order-pill <?= $currentTab === 'cancelled' ? 'active' : '' ?>">
            <span>❌ Batal</span>
            <span class="tab-count-badge"><?= $counts['cancelled'] ?? 0 ?></span>
        </a>
    </div>

    <!-- Active Orders Container -->
    <div class="orders-grid" id="ordersGrid">
        <?php if (empty($orders)): ?>
            <div style="text-align:center;padding:50px 20px;background:var(--bg-card);border:1px dashed var(--border);border-radius:18px">
                <div style="font-size:40px;margin-bottom:10px">🛒</div>
                <div style="font-size:16px;font-weight:800;color:var(--text-primary);margin-bottom:4px">Belum Ada Pesanan</div>
                <div style="font-size:12.5px;color:var(--text-muted);max-width:340px;margin:0 auto 16px">
                    Konsumen dapat memesan online delivery dari rumah atau scan QR meja untuk dine-in secara mandiri.
                </div>
                <a href="/pos/qr" class="btn-top-pos" style="background:#EA580C;color:#fff;border-color:#EA580C;padding:8px 16px">
                    📱 Bagikan Link Menu / Cetak QR
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $ord): 
                $statusPillClass = 'status-pill-' . str_replace('_', '-', $ord['status']);
                $cardStatusClass = 'status-' . str_replace('_', '-', $ord['status']);
                $orderType = $ord['order_type'] ?? 'dine_in';
                $statusLabels = [
                    'pending'          => '🔔 Pesanan Baru',
                    'processing'       => '🍳 Sedang Disiapkan',
                    'delivering'       => '🛵 Sedang Dikirim',
                    'served_unpaid'    => '⚠️ DISAJIKAN (BELUM BAYAR)',
                    'delivered_unpaid' => '⚠️ SAMPAI / COD (BELUM BAYAR)',
                    'paid'             => '✅ Selesai & Lunas',
                    'cancelled'        => '❌ Dibatalkan',
                ];
            ?>
                <div class="order-manage-card <?= $cardStatusClass ?>" id="order-card-<?= $ord['id'] ?>" data-id="<?= $ord['id'] ?>">
                    
                    <!-- Card Top -->
                    <div class="order-card-header">
                        <div>
                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;flex-wrap:wrap">
                                <?php if ($orderType === 'delivery'): ?>
                                    <div class="order-table-badge" style="background:linear-gradient(135deg, #9333EA 0%, #C084FC 100%)">
                                        <span>🛵</span> DELIVERY RUMAH
                                    </div>
                                <?php elseif ($orderType === 'takeaway'): ?>
                                    <div class="order-table-badge" style="background:linear-gradient(135deg, #2563EB 0%, #60A5FA 100%)">
                                        <span>🛍️</span> TAKEAWAY / AMBIL
                                    </div>
                                <?php else: ?>
                                    <div class="order-table-badge">
                                        <span>🪑</span> <?= esc($ord['table_no'] ?: 'Makan di Tempat') ?>
                                    </div>
                                <?php endif; ?>

                                <span class="order-source-pill">
                                    <?= $ord['order_source'] === 'public_menu' ? '🌐 Toko Online / QR' : '💻 Kasir POS' ?>
                                </span>
                            </div>
                            <div style="font-size:12.5px;color:var(--text-muted)">
                                <strong>#<?= esc($ord['order_number']) ?></strong> • <span style="color:var(--text-primary);font-weight:700"><?= esc($ord['customer_name'] ?: 'Pelanggan') ?></span>
                                <?php if (!empty($ord['customer_phone'])): ?>
                                    <?php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $ord['customer_phone']);
                                        if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);
                                    ?>
                                    • <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= urlencode("Halo {$ord['customer_name']}, mengenai pesanan #{$ord['order_number']} di {$store['store_name']}...") ?>" target="_blank" style="color:#10B981;text-decoration:none;font-weight:800">💬 WA: <?= esc($ord['customer_phone']) ?></a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="text-align:right">
                            <span class="status-badge-pill <?= $statusPillClass ?>">
                                <?= $statusLabels[$ord['status']] ?? $ord['status'] ?>
                            </span>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
                                <?= date('H:i', strtotime($ord['created_at'] ?? 'now')) ?> WIB
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Address Box if Delivery -->
                    <?php if ($orderType === 'delivery' && !empty($ord['delivery_address'])): ?>
                        <div style="background:rgba(147, 51, 234, 0.08);border:1px solid rgba(147, 51, 234, 0.3);border-radius:12px;padding:10px 12px;font-size:12.5px">
                            <div style="font-weight:800;color:#C084FC;display:flex;align-items:center;gap:4px;margin-bottom:2px">
                                <span>📍</span> Alamat Pengantaran:
                            </div>
                            <div style="color:var(--text-primary);line-height:1.4"><?= nl2br(esc($ord['delivery_address'])) ?></div>
                            <?php if (!empty($ord['delivery_notes'])): ?>
                                <div style="color:var(--text-muted);font-size:11.5px;margin-top:4px">🏡 Patokan: <?= esc($ord['delivery_notes']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($orderType === 'takeaway' && !empty($ord['pickup_time'])): ?>
                        <div style="background:rgba(59, 130, 246, 0.08);border:1px solid rgba(59, 130, 246, 0.3);border-radius:12px;padding:8px 12px;font-size:12px;color:#93C5FD">
                            ⏰ Jam Pengambilan: <strong><?= esc($ord['pickup_time']) ?></strong>
                        </div>
                    <?php endif; ?>

                    <!-- Items List -->
                    <div class="order-items-box">
                        <?php foreach (($ord['items'] ?? []) as $it): ?>
                            <div class="order-item-row-pos">
                                <div style="flex:1">
                                    <span class="item-name-pos"><?= esc($it['product_name']) ?></span>
                                    <span style="color:#EA580C;font-weight:800">x<?= $it['qty'] ?></span>
                                    <?php if (!empty($it['selected_variants'])): ?>
                                        <div style="font-size:11px;color:#38BDF8;margin-top:2px">✨ <?= esc($it['selected_variants']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($it['notes'])): ?>
                                        <div class="item-note-pos">📝 <?= esc($it['notes']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-sub-pos">
                                    <?= esc($symbol) ?> <?= number_format($it['subtotal'], 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ((float)($ord['delivery_fee'] ?? 0) > 0): ?>
                            <div class="order-item-row-pos" style="border-top:1px dashed var(--border);padding-top:4px;color:var(--text-muted)">
                                <span>🛵 Ongkos Kirim (Delivery)</span>
                                <span><?= esc($symbol) ?> <?= number_format((float)$ord['delivery_fee'], 0, ',', '.') ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ((float)($ord['discount_amount'] ?? 0) > 0): ?>
                            <div class="order-item-row-pos" style="border-top:1px dashed var(--border);padding-top:4px;color:#10B981;font-weight:700">
                                <span>🏷️ Diskon Promo (<?= esc($ord['voucher_code'] ?: 'KUPON') ?>)</span>
                                <span>-<?= esc($symbol) ?> <?= number_format((float)$ord['discount_amount'], 0, ',', '.') ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Total & Actions -->
                    <div class="order-footer-row">
                        <div>
                            <div style="font-size:11px;color:var(--text-muted)">Total (<?= strtoupper(esc($ord['payment_method'] ?? 'COD')) ?>)</div>
                            <div class="order-total-price"><?= esc($symbol) ?> <?= number_format($ord['total_amount'], 0, ',', '.') ?></div>
                        </div>

                        <!-- Action Buttons according to status & type -->
                        <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
                            <?php if (!empty($ord['customer_phone'])): ?>
                                <?php
                                    $cPhone = preg_replace('/[^0-9]/', '', $ord['customer_phone']);
                                    if (str_starts_with($cPhone, '0')) $cPhone = '62' . substr($cPhone, 1);
                                    $trackingUrl = base_url('/menu/' . esc($store['store_slug']) . '/status/' . esc($ord['order_number']));
                                    
                                    // Predefined WA messages
                                    $msgConfirm = urlencode("Halo Kak {$ord['customer_name']}, pesanan #{$ord['order_number']} di {$store['store_name']} telah kami terima & sedang disiapkan! Total: Rp " . number_format($ord['total_amount'], 0, ',', '.') . ".\n\nPantau status pesanan live di:\n{$trackingUrl}\n\nTerima kasih!");
                                    $msgCourier = urlencode("Halo Kak {$ord['customer_name']}, 🛵 Kurir kami sedang jalan mengantarkan pesanan #{$ord['order_number']} ke alamat Anda. Mohon siapkan uang pas Rp " . number_format($ord['total_amount'], 0, ',', '.') . " jika bayar COD ya. Terima kasih!");
                                    $msgReady = urlencode("Halo Kak {$ord['customer_name']}, pesanan #{$ord['order_number']} Anda di {$store['store_name']} sudah SIAP! Silakan dinikmati / diambil di kasir ya. Terima kasih!");
                                    $msgThanks = urlencode("Terima kasih Kak {$ord['customer_name']} telah berbelanja di {$store['store_name']}! Transaksi #{$ord['order_number']} telah selesai. Semoga berkah & puas dengan layanan kami! ⭐");

                                    $targetWaMsg = ($ord['status'] === 'pending') ? $msgConfirm : (($ord['status'] === 'delivering') ? $msgCourier : (($ord['status'] === 'paid') ? $msgThanks : $msgReady));
                                ?>
                                <a href="https://wa.me/<?= $cPhone ?>?text=<?= $targetWaMsg ?>" target="_blank" class="btn-action-pos" style="background:#15803D;color:#fff;text-decoration:none" title="Kirim notifikasi pesan WhatsApp ke pelanggan">
                                    💬 Notif WA
                                </a>
                            <?php endif; ?>

                            <?php if ($ord['status'] === 'pending'): ?>
                                <button class="btn-action-pos btn-act-process" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'processing')">
                                    🍳 Terima & Siapkan
                                </button>
                                <button class="btn-action-pos btn-act-cancel" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'cancelled')">
                                    Tolak
                                </button>
                            <?php elseif ($ord['status'] === 'processing'): ?>
                                <?php if ($orderType === 'delivery'): ?>
                                    <button class="btn-action-pos" style="background:#9333EA;color:#fff" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'delivering')">
                                        🛵 Kirim (Kurir Jalan)
                                    </button>
                                <?php elseif ($orderType === 'takeaway'): ?>
                                    <button class="btn-action-pos" style="background:#2563EB;color:#fff" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'delivering')">
                                        🛍️ Siap Diambil
                                    </button>
                                <?php else: ?>
                                    <button class="btn-action-pos btn-act-serve" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'served_unpaid')">
                                        🍽️ Sajikan (Belum Bayar)
                                    </button>
                                <?php endif; ?>
                                <button class="btn-action-pos btn-act-pay" onclick="openSettleModal(<?= $ord['id'] ?>, '<?= esc($ord['order_number']) ?>', <?= (float)$ord['total_amount'] ?>)">
                                    💳 Bayar Kasir
                                </button>
                            <?php elseif ($ord['status'] === 'delivering'): ?>
                                <?php if ($orderType === 'delivery'): ?>
                                    <button class="btn-action-pos" style="background:#D97706;color:#fff" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'delivered_unpaid')">
                                        📦 Sampai (Belum Setor COD)
                                    </button>
                                <?php endif; ?>
                                <button class="btn-action-pos btn-act-pay" style="background:#10B981;color:#fff" onclick="openSettleModal(<?= $ord['id'] ?>, '<?= esc($ord['order_number']) ?>', <?= (float)$ord['total_amount'] ?>)">
                                    💳 Lunas & Selesai
                                </button>
                            <?php elseif ($ord['status'] === 'served_unpaid' || $ord['status'] === 'delivered_unpaid'): ?>
                                <button class="btn-action-pos btn-act-pay" style="padding:10px 16px;font-size:13px;background:#10B981" onclick="openSettleModal(<?= $ord['id'] ?>, '<?= esc($ord['order_number']) ?>', <?= (float)$ord['total_amount'] ?>)">
                                    💳 Terima Bayar & Lunas
                                </button>
                                <button class="btn-action-pos btn-act-cancel" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'cancelled')">
                                    Batal
                                </button>
                            <?php endif; ?>

                            <a href="/pos/order/receipt/<?= $ord['id'] ?>?autoprint=1" target="_blank" class="btn-action-pos" style="background:#0284C7;color:#fff;text-decoration:none" title="Cetak Struk Thermal 58mm/80mm">
                                🖨️ Struk
                            </a>

                            <?php if ($ord['status'] === 'paid'): ?>
                                <div style="font-size:12px;font-weight:800;color:#10B981;display:flex;align-items:center;gap:4px">
                                    <span>✅</span> Selesai
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- Settlement (Pay) Modal -->
<div class="modal-settle-overlay" id="settleModal">
    <div class="settle-dialog">
        <div class="settle-header">
            <div style="font-weight:900;font-size:16px;color:var(--text-primary)" id="settleOrderTitle">Pembayaran Kasir</div>
            <button style="background:none;border:none;color:var(--text-muted);font-size:18px;cursor:pointer" onclick="closeSettleModal()">✕</button>
        </div>
        <div class="settle-body">
            <input type="hidden" id="settleOrderId">
            
            <div style="background:var(--bg);border:1px solid var(--border);border-radius:14px;padding:14px;text-align:center">
                <div style="font-size:11.5px;color:var(--text-muted);text-transform:uppercase;font-weight:700">Total Yang Harus Dibayar</div>
                <div style="font-size:24px;font-weight:900;color:#EA580C;margin-top:2px" id="settleTotalDisplay"><?= esc($symbol) ?> 0</div>
            </div>

            <div>
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px">Metode Pembayaran</label>
                <div class="settle-pay-grid">
                    <div class="pay-pill-opt selected" onclick="selectPayOpt('cash', this)">
                        <span>💵</span> Tunai (Cash)
                    </div>
                    <div class="pay-pill-opt" onclick="selectPayOpt('qris', this)">
                        <span>📱</span> QRIS
                    </div>
                    <div class="pay-pill-opt" onclick="selectPayOpt('transfer', this)">
                        <span>🏦</span> Transfer Bank
                    </div>
                    <div class="pay-pill-opt" onclick="selectPayOpt('kasbon', this)">
                        <span>📝</span> Kasbon (Piutang)
                    </div>
                </div>
            </div>

            <!-- Wallet deposit selector -->
            <div id="walletSelectWrap">
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px">Masuk ke Rekening / Kas</label>
                <select id="settleWalletId" style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text-primary);font-size:13px;outline:none">
                    <?php foreach ($wallets as $w): ?>
                        <option value="<?= $w['id'] ?>" <?= !empty($w['is_default']) ? 'selected' : '' ?>>
                            <?= esc($w['name']) ?> (<?= esc($symbol) ?> <?= number_format($w['balance'] ?? 0, 0, ',', '.') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Cash Received input (for cash method) -->
            <div id="cashInputWrap">
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px">Uang Diterima</label>
                <input type="text" id="settleCashReceived" style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text-primary);font-size:14px;font-weight:700;outline:none" placeholder="0" oninput="calculateChange()">
                <div style="font-size:12px;color:#10B981;font-weight:800;margin-top:6px" id="settleChangeDisplay">Kembalian: Rp 0</div>
            </div>

            <button class="btn-action-pos btn-act-pay" style="width:100%;padding:13px;font-size:14px" id="btnConfirmPay" onclick="confirmPayment()">
                <span>✅</span> Selesaikan & Simpan Transaksi
            </button>
        </div>
    </div>
</div>

<!-- Store Settings Modal -->
<div class="modal-settle-overlay" id="storeSettingModal">
    <div class="settle-dialog" style="max-width:520px;max-height:85vh;overflow-y:auto">
        <div class="settle-header">
            <div style="font-weight:900;font-size:16px;color:var(--text-primary)">⚙️ Profil Toko, QR & Online Delivery</div>
            <button style="background:none;border:none;color:var(--text-muted);font-size:18px;cursor:pointer" onclick="closeStoreSettingModal()">✕</button>
        </div>
        <div class="settle-body">
            <div class="input-group">
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary)">Nama Toko / Outlet POS <span style="color:#EF4444">*</span></label>
                <input type="text" id="cfgStoreName" style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text-primary);font-size:13.5px;font-family:inherit" value="<?= esc($store['store_name']) ?>">
            </div>

            <div class="input-group">
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary)">URL Slug Alamat Menu / Toko <span style="color:#EF4444">*</span></label>
                <div style="display:flex;align-items:center;gap:6px">
                    <span style="font-size:12px;color:var(--text-muted)">/menu/</span>
                    <input type="text" id="cfgStoreSlug" style="flex:1;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text-primary);font-size:13.5px;font-family:inherit" value="<?= esc($store['store_slug']) ?>">
                </div>
            </div>

            <div class="input-group">
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary)">No. WhatsApp Toko (untuk dihubungi konsumen)</label>
                <input type="tel" id="cfgStorePhone" style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text-primary);font-size:13px;font-family:inherit" placeholder="08xxxxxxxxxx" value="<?= esc($store['store_phone']) ?>">
            </div>

            <div class="input-group">
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary)">Slogan / Keterangan Singkat</label>
                <input type="text" id="cfgStoreTagline" style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text-primary);font-size:13px;font-family:inherit" value="<?= esc($store['store_tagline']) ?>">
            </div>

            <div class="input-group">
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary)">Alamat Outlet / Toko</label>
                <input type="text" id="cfgStoreAddress" style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text-primary);font-size:13px;font-family:inherit" value="<?= esc($store['store_address']) ?>">
            </div>

            <!-- Delivery Settings -->
            <div style="background:rgba(234, 88, 12, 0.08);border:1px dashed var(--primary);border-radius:12px;padding:12px;display:flex;flex-direction:column;gap:10px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:13px;font-weight:800;color:var(--text-primary)">🛵 Layanan Antar (Delivery)</span>
                    <input type="checkbox" id="cfgStoreDeliveryEnabled" <?= ($store['store_delivery_enabled'] ?? true) ? 'checked' : '' ?> style="transform:scale(1.2)">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:var(--text-secondary)">Tarif Ongkos Kirim Flat (Rp)</label>
                    <input type="number" id="cfgStoreDeliveryFee" style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:var(--text-primary);font-size:13px;outline:none" value="<?= (float)($store['store_delivery_fee'] ?? 0) ?>">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:var(--text-secondary)">Info Rekening Bank & QRIS (untuk pembayaran transfer)</label>
                    <textarea id="cfgStoreBankInfo" rows="2" style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:var(--text-primary);font-size:12px;outline:none;resize:none" placeholder="Cth: BCA 1234567890 a/n Toko"><?= esc($store['store_bank_info']) ?></textarea>
                </div>
            </div>

            <div class="input-group">
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary)">Teks Keterangan di Bawah QR Code Standee</label>
                <textarea id="cfgStoreQrFooter" rows="2" style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text-primary);font-size:13px;font-family:inherit;resize:none"><?= esc($store['store_qr_footer']) ?></textarea>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 0">
                <span style="font-size:13px;font-weight:700;color:var(--text-primary)">Buka Penerimaan Pesanan Online</span>
                <input type="checkbox" id="cfgStoreIsOpen" <?= $store['store_is_open'] ? 'checked' : '' ?> style="transform:scale(1.3)">
            </div>

            <button class="btn-action-pos btn-act-pay" style="width:100%;padding:12px;font-size:13.5px" onclick="saveStoreProfile()">
                Simpan Profil & Pengaturan
            </button>
        </div>
    </div>
</div>

<script>
    let currentMethod = 'cash';
    let currentTotal = 0;
    let lastOrderCount = <?= (int)($counts['pending'] ?? 0) ?>;

    // Web Audio Bell Chime for new order arrival
    function playOrderBell() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime); // A5
            osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.15); // E6
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.8);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.8);
        } catch (e) {
            console.log('Audio error:', e);
        }
    }

    async function updateOrderStatus(orderId, status) {
        if (status === 'cancelled' && !confirm('Yakin ingin membatalkan pesanan ini?')) return;

        try {
            const res = await fetch('/pos/orders/update-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ order_id: orderId, status: status })
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal mengubah status');
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan jaringan');
        }
    }

    function openSettleModal(orderId, orderNum, amount) {
        document.getElementById('settleOrderId').value = orderId;
        document.getElementById('settleOrderTitle').textContent = 'Bayar Pesanan #' + orderNum;
        document.getElementById('settleTotalDisplay').textContent = 'Rp ' + Number(amount).toLocaleString('id-ID');
        document.getElementById('settleCashReceived').value = Number(amount).toLocaleString('id-ID');
        currentTotal = amount;
        calculateChange();
        document.getElementById('settleModal').classList.add('active');
    }

    function closeSettleModal() {
        document.getElementById('settleModal').classList.remove('active');
    }

    function selectPayOpt(method, el) {
        currentMethod = method;
        document.querySelectorAll('.pay-pill-opt').forEach(p => p.classList.remove('selected'));
        el.classList.add('selected');

        const cashWrap = document.getElementById('cashInputWrap');
        if (method === 'cash') {
            cashWrap.style.display = 'block';
        } else {
            cashWrap.style.display = 'none';
        }
    }

    function calculateChange() {
        const raw = document.getElementById('settleCashReceived').value.replace(/[^0-9]/g, '');
        const val = parseFloat(raw) || 0;
        const change = Math.max(0, val - currentTotal);
        document.getElementById('settleChangeDisplay').textContent = 'Kembalian: Rp ' + Number(change).toLocaleString('id-ID');
    }

    async function confirmPayment() {
        const orderId = document.getElementById('settleOrderId').value;
        const walletId = document.getElementById('settleWalletId').value;
        const cashRaw = document.getElementById('settleCashReceived').value.replace(/[^0-9]/g, '');
        const cashReceived = parseFloat(cashRaw) || currentTotal;

        const btn = document.getElementById('btnConfirmPay');
        btn.disabled = true;
        btn.innerHTML = 'Memproses...';

        try {
            const res = await fetch('/pos/orders/pay', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    order_id: orderId,
                    payment_method: currentMethod,
                    wallet_id: walletId,
                    cash_received: cashReceived
                })
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal memproses pembayaran.');
                btn.disabled = false;
                btn.innerHTML = '<span>✅</span> Selesaikan & Simpan Transaksi';
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan jaringan');
            btn.disabled = false;
            btn.innerHTML = '<span>✅</span> Selesaikan & Simpan Transaksi';
        }
    }

    function openStoreSettingModal() {
        document.getElementById('storeSettingModal').classList.add('active');
    }
    function closeStoreSettingModal() {
        document.getElementById('storeSettingModal').classList.remove('active');
    }

    async function saveStoreProfile() {
        const name = document.getElementById('cfgStoreName').value.trim();
        const slug = document.getElementById('cfgStoreSlug').value.trim();
        const phone = document.getElementById('cfgStorePhone').value.trim();
        const tagline = document.getElementById('cfgStoreTagline').value.trim();
        const address = document.getElementById('cfgStoreAddress').value.trim();
        const qrFooter = document.getElementById('cfgStoreQrFooter').value.trim();
        const isOpen = document.getElementById('cfgStoreIsOpen').checked ? '1' : '0';
        const deliveryOn = document.getElementById('cfgStoreDeliveryEnabled').checked ? '1' : '0';
        const deliveryFee = document.getElementById('cfgStoreDeliveryFee').value.trim() || '0';
        const bankInfo = document.getElementById('cfgStoreBankInfo').value.trim();

        if (!name) {
            alert('Nama toko wajib diisi.');
            return;
        }

        try {
            const res = await fetch('/pos/store-profile', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    store_name: name,
                    store_slug: slug,
                    store_phone: phone,
                    store_tagline: tagline,
                    store_address: address,
                    store_qr_footer: qrFooter,
                    store_is_open: isOpen,
                    store_delivery_enabled: deliveryOn,
                    store_delivery_fee: deliveryFee,
                    store_bank_info: bankInfo
                })
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan');
            }
        } catch (e) {
            console.error(e);
            alert('Gagal menyimpan profil toko');
        }
    }

    // Realtime Polling for new orders every 5 seconds
    async function pollLiveOrders() {
        try {
            const res = await fetch('/pos/orders/poll?status=<?= esc($currentTab) ?>');
            const data = await res.json();
            if (data.success && data.counts) {
                const pendingCount = data.counts.pending || 0;
                if (pendingCount > lastOrderCount) {
                    playOrderBell();
                    // Auto-refresh if on pending/all tab
                    if ('<?= esc($currentTab) ?>' === 'all' || '<?= esc($currentTab) ?>' === 'pending') {
                        location.reload();
                    }
                }
                lastOrderCount = pendingCount;
            }
        } catch (e) {
            console.log('Poll err:', e);
        }
    }
    setInterval(pollLiveOrders, 5000);
</script>
<?= $this->endSection() ?>
