<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="market-conversations-page">
    <div class="conv-header-wrap">
        <div class="conv-title-row">
            <div>
                <h2 class="conv-page-title">Pesan & Obrolan</h2>
                <p class="conv-page-sub">Kelola diskusi jual beli & sewa secara instan</p>
            </div>
            <?php if (!empty($totalUnread) && $totalUnread > 0): ?>
                <div class="conv-unread-pill">
                    <span class="conv-pulse-dot"></span>
                    <span><?= $totalUnread ?> Pesan Baru</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($conversations)): ?>
        <div class="conv-empty-state">
            <div class="conv-empty-icon">💬</div>
            <h3 class="conv-empty-title">Belum Ada Obrolan</h3>
            <p class="conv-empty-desc">
                Mulai percakapan dengan penjual atau pembeli di Marketplace DuitKu untuk bertransaksi dengan aman.
            </p>
            <a href="/marketplace" class="conv-empty-btn">
                <span>🛍️ Jelajahi Marketplace</span>
            </a>
        </div>
    <?php else: ?>
        <div class="conv-list" id="convList">
            <?php foreach ($conversations as $conv): ?>
                <?php
                    $isSeller   = ((int)$conv['seller_id'] === (int)$userId);
                    $partnerName = $isSeller ? ($conv['buyer_name'] ?: 'Calon Pembeli') : ($conv['seller_name'] ?: 'Penjual');
                    $partnerRole = $isSeller ? 'Calon Pembeli' : 'Penjual';
                    $unread      = (int)($conv['unread_count'] ?? 0);
                    $initial     = strtoupper(mb_substr($partnerName, 0, 1));
                    $isMyLastMsg = ((int)($conv['last_sender_id'] ?? 0) === (int)$userId);

                    // Formatted time
                    $timeStr = '';
                    if (!empty($conv['last_message_time'])) {
                        $time = strtotime($conv['last_message_time']);
                        $today = strtotime('today');
                        if ($time >= $today) {
                            $timeStr = date('H:i', $time);
                        } elseif ($time >= strtotime('yesterday')) {
                            $timeStr = 'Kemarin';
                        } else {
                            $timeStr = date('d/m/y', $time);
                        }
                    }

                    $listingImg = $conv['listing_image'] ?? '';
                    $targetUrl  = '/marketplace/item/' . (int)$conv['listing_id'] . '?open_chat=1&chat_buyer_id=' . (int)$conv['buyer_id'];
                ?>
                <div class="conv-item <?= $unread > 0 ? 'unread' : '' ?>" onclick="openChatFromConv(<?= (int)$conv['listing_id'] ?>, <?= (int)$conv['buyer_id'] ?>, '<?= esc(addslashes($conv['listing_title'])) ?>', '<?= esc(addslashes($partnerName)) ?>', '<?= esc(addslashes($conv['seller_phone'] ?? '')) ?>')">
                    <!-- Partner Avatar -->
                    <div class="conv-avatar">
                        <span><?= esc($initial) ?></span>
                        <span class="conv-status-badge"></span>
                    </div>

                    <!-- Conversation Details -->
                    <div class="conv-content">
                        <div class="conv-top-line">
                            <div class="conv-partner-name-wrap">
                                <span class="conv-partner-name"><?= esc($partnerName) ?></span>
                                <span class="conv-role-tag <?= $isSeller ? 'buyer' : 'seller' ?>"><?= esc($partnerRole) ?></span>
                            </div>
                            <span class="conv-time <?= $unread > 0 ? 'unread' : '' ?>"><?= esc($timeStr) ?></span>
                        </div>

                        <!-- Listing Summary Pill -->
                        <div class="conv-listing-pill">
                            <span class="conv-listing-icon">🏷️</span>
                            <span class="conv-listing-title"><?= esc($conv['listing_title']) ?></span>
                            <span class="conv-listing-price">Rp <?= number_format((float)$conv['listing_price'], 0, ',', '.') ?></span>
                        </div>

                        <!-- Last Message Preview & Unread Badge -->
                        <div class="conv-bottom-line">
                            <p class="conv-snippet <?= $unread > 0 ? 'bold' : '' ?>">
                                <?php if ($isMyLastMsg): ?>
                                    <span class="conv-tick-icon">✓✓</span>
                                <?php endif; ?>
                                <?= esc($conv['last_message']) ?>
                            </p>
                            <?php if ($unread > 0): ?>
                                <span class="conv-badge"><?= $unread > 99 ? '99+' : $unread ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- REAL-TIME IN-APP MARKETPLACE CHAT MODAL (WHATSAPP STYLE) -->
<div class="market-chat-modal-overlay" id="marketChatModal" style="display:none;" onclick="if(event.target===this)closeMarketChat()">
    <div class="market-chat-modal-box wa-style">
        <!-- Header -->
        <div class="market-chat-header wa-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <button type="button" class="market-chat-back-btn" onclick="closeMarketChat()" title="Kembali">←</button>
                <div class="market-chat-header-info">
                    <div class="market-chat-header-title" id="chatModalPartnerName">Obrolan Marketplace</div>
                    <div class="market-chat-header-sub">
                        <span class="market-chat-status-dot"></span>
                        <span id="chatModalListingTitle">Memuat info...</span>
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <a href="#" id="chatModalWaBtn" target="_blank" class="chat-wa-header-btn" style="display:none;" title="Lanjutkan di WhatsApp">
                    🟢 WA
                </a>
                <button type="button" class="market-chat-close-btn" onclick="closeMarketChat()" title="Tutup Chat">✕</button>
            </div>
        </div>

        <!-- Sticky Listing Header Preview Bar -->
        <div class="chat-pinned-listing" id="chatPinnedListing" style="display:none;">
            <div class="chat-pinned-info">
                <span class="chat-pinned-tag">Produk</span>
                <span class="chat-pinned-title" id="pinnedListingTitle">-</span>
            </div>
            <a href="#" id="pinnedListingLink" class="chat-pinned-btn" target="_blank">Lihat Detail &gt;</a>
        </div>

        <!-- Messages List (WhatsApp Warm Wallpaper Background) -->
        <div class="market-chat-body wa-wallpaper" id="chatModalMessages">
            <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:12px;">
                Memuat riwayat percakapan...
            </div>
        </div>

        <!-- Input Footer (WhatsApp Pill Input with Detached Circular Send Button) -->
        <form class="market-chat-footer wa-footer" id="marketChatForm" onsubmit="sendChatMessage(event)">
            <div class="wa-input-pill">
                <span class="wa-input-icon">😊</span>
                <input type="text" id="chatInputMessage" class="market-chat-input wa-input" placeholder="Ketik pesan..." autocomplete="off" required>
                <span class="wa-input-icon">📎</span>
            </div>
            <button type="submit" id="btnSendChatMsg" class="market-chat-send-btn wa-send-btn" title="Kirim Pesan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<style>
/* ══════════════════════════════════════════════════════════════
   PWA CONVERSATIONS LIST STYLES (WhatsApp / Telegram Style)
══════════════════════════════════════════════════════════════ */
.market-conversations-page {
    max-width: 680px;
    margin: 0 auto;
    padding: 16px 16px 90px;
}

.conv-header-wrap {
    margin-bottom: 16px;
}
.conv-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.conv-page-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 2px;
    letter-spacing: -0.3px;
}
.conv-page-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}
.conv-unread-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #FEF2F2;
    color: #EF4444;
    border: 1px solid #FCA5A5;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}
.conv-pulse-dot {
    width: 6px;
    height: 6px;
    background: #EF4444;
    border-radius: 50%;
    animation: pulseDot 1.5s infinite;
}
@keyframes pulseDot {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.4); opacity: 0.6; }
}

/* Empty State */
.conv-empty-state {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 48px 24px;
    text-align: center;
    margin-top: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.conv-empty-icon {
    font-size: 48px;
    margin-bottom: 14px;
}
.conv-empty-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 8px;
}
.conv-empty-desc {
    font-size: 13px;
    color: var(--text-muted);
    max-width: 380px;
    margin: 0 auto 20px;
    line-height: 1.5;
}
.conv-empty-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #059669;
    color: #fff;
    padding: 11px 22px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
    transition: transform 0.15s ease;
}
.conv-empty-btn:active { transform: scale(0.97); }

/* Conversation List */
.conv-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.conv-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 14px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.conv-item:hover {
    border-color: rgba(37, 99, 235, 0.35);
    box-shadow: 0 4px 16px rgba(37, 99, 235, 0.08);
    transform: translateY(-1px);
}
.conv-item.unread {
    background: rgba(37, 99, 235, 0.03);
    border-color: rgba(37, 99, 235, 0.25);
}

.conv-avatar {
    position: relative;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
    color: #fff;
    font-size: 16px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(37, 99, 235, 0.25);
}
.conv-status-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 11px;
    height: 11px;
    background: #10B981;
    border: 2px solid var(--card);
    border-radius: 50%;
}

.conv-content {
    flex: 1;
    min-width: 0;
}
.conv-top-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
}
.conv-partner-name-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
}
.conv-partner-name {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.conv-role-tag {
    font-size: 9.5px;
    font-weight: 700;
    padding: 1.5px 6px;
    border-radius: 6px;
    flex-shrink: 0;
}
.conv-role-tag.buyer {
    background: #EFF6FF;
    color: #2563EB;
}
.conv-role-tag.seller {
    background: #ECFDF5;
    color: #059669;
}
.conv-time {
    font-size: 11px;
    color: var(--text-muted);
    flex-shrink: 0;
    font-weight: 500;
}
.conv-time.unread {
    color: #2563EB;
    font-weight: 700;
}

.conv-listing-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 3px 8px;
    margin-bottom: 6px;
    max-width: 100%;
}
.conv-listing-icon { font-size: 11px; }
.conv-listing-title {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 220px;
}
.conv-listing-price {
    font-size: 11px;
    font-weight: 800;
    color: #059669;
    flex-shrink: 0;
}

.conv-bottom-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.conv-snippet {
    font-size: 12.5px;
    color: var(--text-muted);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}
.conv-snippet.bold {
    color: var(--text-primary);
    font-weight: 700;
}
.conv-tick-icon {
    color: #53BDEB;
    font-size: 11px;
    font-weight: 900;
    margin-right: 3px;
}
.conv-badge {
    background: #2563EB;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 999px;
    flex-shrink: 0;
}

/* ══════════════════════════════════════════════════════════════
   WHATSAPP / TELEGRAM CHAT MODAL STYLING
══════════════════════════════════════════════════════════════ */
.market-chat-modal-box.wa-style {
    background: #EFEAE2;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 20px;
}
[data-theme="dark"] .market-chat-modal-box.wa-style {
    background: #0B141A;
    border-color: rgba(255,255,255,0.1);
}

.market-chat-header.wa-header {
    background: #075E54;
    color: #fff;
    padding: 10px 16px;
}
[data-theme="dark"] .market-chat-header.wa-header {
    background: #202C33;
}

.market-chat-back-btn {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 18px;
    font-weight: 900;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-pinned-listing {
    background: #fff;
    border-bottom: 1px solid #E2E8F0;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
[data-theme="dark"] .chat-pinned-listing {
    background: #182229;
    border-bottom-color: #2A3942;
}
.chat-pinned-info {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
}
.chat-pinned-tag {
    background: #EFF6FF;
    color: #2563EB;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
    flex-shrink: 0;
}
.chat-pinned-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.chat-pinned-btn {
    font-size: 11px;
    font-weight: 800;
    color: #059669;
    text-decoration: none;
    flex-shrink: 0;
}

/* WhatsApp Wallpaper */
.market-chat-body.wa-wallpaper {
    background-color: #EFEAE2;
    background-image: 
        radial-gradient(#d6cfbe 0.75px, transparent 0.75px);
    background-size: 16px 16px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
[data-theme="dark"] .market-chat-body.wa-wallpaper {
    background-color: #0B141A;
    background-image: 
        radial-gradient(#1f2c34 0.75px, transparent 0.75px);
}

/* Date Separator Pill */
.chat-date-separator {
    align-self: center;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(0,0,0,0.06);
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    color: #54656F;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    padding: 3px 12px;
    border-radius: 8px;
    margin: 6px 0;
}
[data-theme="dark"] .chat-date-separator {
    background: rgba(32, 44, 51, 0.9);
    border-color: rgba(255,255,255,0.06);
    color: #8696A0;
}

/* WhatsApp Message Bubbles */
.chat-bubble.wa-me {
    align-self: flex-end;
    background: #E7FFDB;
    color: #111B21;
    border-radius: 12px 12px 2px 12px;
    padding: 7px 12px;
    max-width: 80%;
    font-size: 13.5px;
    line-height: 1.45;
    box-shadow: 0 1px 2px rgba(0,0,0,0.12);
    position: relative;
    word-break: break-word;
}
[data-theme="dark"] .chat-bubble.wa-me {
    background: #005C4B;
    color: #E9EDEF;
}

.chat-bubble.wa-other {
    align-self: flex-start;
    background: #FFFFFF;
    color: #111B21;
    border-radius: 12px 12px 12px 2px;
    padding: 7px 12px;
    max-width: 80%;
    font-size: 13.5px;
    line-height: 1.45;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    position: relative;
    word-break: break-word;
}
[data-theme="dark"] .chat-bubble.wa-other {
    background: #202C33;
    color: #E9EDEF;
}

.chat-meta-row {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    margin-top: 2px;
}
.chat-bubble-time {
    font-size: 10px;
    color: #667781;
    font-weight: 500;
}
[data-theme="dark"] .chat-bubble-time {
    color: #8696A0;
}
.chat-read-tick {
    font-size: 10.5px;
    font-weight: 900;
    color: #8696A0;
}
.chat-read-tick.read {
    color: #53BDEB;
}

/* WhatsApp Input Footer */
.market-chat-footer.wa-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #F0F2F5;
    border-top: 1px solid #E2E8F0;
}
[data-theme="dark"] .market-chat-footer.wa-footer {
    background: #202C33;
    border-top-color: #2A3942;
}

.wa-input-pill {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border-radius: 28px;
    padding: 4px 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
[data-theme="dark"] .wa-input-pill {
    background: #2A3942;
}
.wa-input-icon {
    font-size: 16px;
    cursor: pointer;
    opacity: 0.65;
    user-select: none;
}
.wa-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 13.5px;
    color: #111B21;
    background: transparent;
    padding: 6px 0;
}
[data-theme="dark"] .wa-input {
    color: #E9EDEF;
}
.wa-send-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #00A884;
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 168, 132, 0.4);
    transition: transform 0.15s ease, background 0.15s ease;
    flex-shrink: 0;
}
.wa-send-btn:hover {
    background: #008f70;
}
.wa-send-btn:active {
    transform: scale(0.92);
}
</style>

<script>
let currentChatListingId = null;
let currentChatBuyerId   = null;
let chatPollTimer        = null;
let isSendingMsg         = false;

function openChatFromConv(listingId, buyerId, listingTitle, partnerName, partnerPhone) {
    currentChatListingId = listingId;
    currentChatBuyerId   = buyerId;

    const modal       = document.getElementById('marketChatModal');
    const partnerEl   = document.getElementById('chatModalPartnerName');
    const listingEl   = document.getElementById('chatModalListingTitle');
    const pinnedWrap  = document.getElementById('chatPinnedListing');
    const pinnedTitle = document.getElementById('pinnedListingTitle');
    const pinnedLink  = document.getElementById('pinnedListingLink');
    const waBtn       = document.getElementById('chatModalWaBtn');
    const bodyEl      = document.getElementById('chatModalMessages');

    if (partnerEl) partnerEl.textContent = partnerName || 'Obrolan Marketplace';
    if (listingEl) listingEl.textContent = listingTitle || 'Produk Marketplace';
    
    if (pinnedWrap && pinnedTitle) {
        pinnedWrap.style.display = 'flex';
        pinnedTitle.textContent = listingTitle || 'Produk';
        if (pinnedLink) pinnedLink.href = '/marketplace/item/' + listingId;
    }

    if (waBtn) {
        if (partnerPhone) {
            let phone = partnerPhone.replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) phone = '62' + phone.substring(1);
            waBtn.href = 'https://wa.me/' + phone + '?text=' + encodeURIComponent('Halo ' + partnerName + ', saya menghubungi Anda dari Marketplace DuitKu terkait: ' + listingTitle);
            waBtn.style.display = 'inline-flex';
        } else {
            waBtn.style.display = 'none';
        }
    }

    if (bodyEl) {
        bodyEl.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted);font-size:12px;">Memuat pesan...</div>';
    }

    modal.style.display = 'flex';
    requestAnimationFrame(() => modal.classList.add('show'));

    loadChatMessages(true);

    if (chatPollTimer) clearInterval(chatPollTimer);
    chatPollTimer = setInterval(() => loadChatMessages(false), 3500);

    setTimeout(() => {
        const inp = document.getElementById('chatInputMessage');
        if (inp) inp.focus();
    }, 200);
}

function closeMarketChat() {
    const modal = document.getElementById('marketChatModal');
    if (!modal) return;
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        currentChatListingId = null;
        currentChatBuyerId   = null;
        if (chatPollTimer) {
            clearInterval(chatPollTimer);
            chatPollTimer = null;
        }
    }, 250);
}

function loadChatMessages(autoScroll) {
    if (!currentChatListingId || !currentChatBuyerId) return;

    fetch('/marketplace/chat/messages?listing_id=' + currentChatListingId + '&buyer_id=' + currentChatBuyerId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) return;
        const messages = res.messages || [];
        const bodyEl   = document.getElementById('chatModalMessages');
        if (!bodyEl) return;

        if (messages.length === 0) {
            bodyEl.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted);font-size:12px;">Belum ada pesan. Mulai kirim penawaran atau pertanyaan!</div>';
            return;
        }

        let html = '';
        let lastDateStr = '';

        messages.forEach(msg => {
            const isMe = (msg.is_me === true);
            
            // Format sticky date
            let msgDate = '';
            if (msg.created_at) {
                const parts = msg.created_at.split(' ');
                msgDate = parts[0] || '';
            }

            if (msgDate && msgDate !== lastDateStr) {
                lastDateStr = msgDate;
                html += '<div class="chat-date-separator">' + formatDateBadge(msgDate) + '</div>';
            }

            const bubbleClass = isMe ? 'chat-bubble wa-me' : 'chat-bubble wa-other';
            const tickHtml = isMe ? `<span class="chat-read-tick ${msg.is_read == 1 ? 'read' : ''}">✓✓</span>` : '';

            html += `
                <div class="${bubbleClass}">
                    <div>${escapeHtml(msg.message)}</div>
                    <div class="chat-meta-row">
                        <span class="chat-bubble-time">${msg.time_formatted || ''}</span>
                        ${tickHtml}
                    </div>
                </div>
            `;
        });

        bodyEl.innerHTML = html;
        if (autoScroll) {
            bodyEl.scrollTop = bodyEl.scrollHeight;
        }
    })
    .catch(err => console.error('Error polling chat:', err));
}

function formatDateBadge(dateStr) {
    const today = new Date().toISOString().slice(0, 10);
    const yest = new Date(Date.now() - 86400000).toISOString().slice(0, 10);
    if (dateStr === today) return 'HARI INI';
    if (dateStr === yest) return 'KEMARIN';
    return dateStr;
}

function sendChatMessage(e) {
    e.preventDefault();
    if (isSendingMsg || !currentChatListingId || !currentChatBuyerId) return;

    const inputEl = document.getElementById('chatInputMessage');
    const text    = (inputEl?.value || '').trim();
    if (!text) return;

    isSendingMsg = true;
    const btn = document.getElementById('btnSendChatMsg');
    if (btn) btn.disabled = true;

    const formData = new FormData();
    formData.append('listing_id', currentChatListingId);
    formData.append('buyer_id', currentChatBuyerId);
    formData.append('message', text);

    fetch('/marketplace/chat/send', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        isSendingMsg = false;
        if (btn) btn.disabled = false;

        if (res.success) {
            if (inputEl) inputEl.value = '';
            loadChatMessages(true);
        } else {
            alert(res.message || 'Gagal mengirim pesan');
        }
    })
    .catch(err => {
        isSendingMsg = false;
        if (btn) btn.disabled = false;
        console.error('Send error:', err);
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, function(m) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[m];
    });
}

// Auto-poll total unread count on PWA bottom nav
setInterval(() => {
    fetch('/marketplace/chat/unread-count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            const badge = document.getElementById('navChatBadge');
            if (badge) {
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    })
    .catch(() => {});
}, 6000);
</script>
<?= $this->endSection() ?>
