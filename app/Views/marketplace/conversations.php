<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="market-conversations-page">
    <!-- Header -->
    <div class="conv-header-wrap">
        <div class="conv-title-row">
            <div>
                <h2 class="conv-page-title">Pesan & Obrolan</h2>
                <p class="conv-page-sub">Kirim pesan langsung ke teman & obrolan marketplace</p>
            </div>
            <div class="conv-actions-group">
                <button type="button" class="btn-add-friend-top" onclick="openAddFriendModal()" title="Tambah Teman via Username">
                    <span>➕ Tambah Teman</span>
                </button>
                <button type="button" class="btn-friends-list-top" onclick="openFriendsListModal()" title="Daftar Teman">
                    <span>👥 Kontak</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Incoming Friend Requests Banner / Box -->
    <?php if (!empty($incomingRequests)): ?>
        <div class="friend-requests-card" id="friendRequestsBox">
            <div class="friend-req-header">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="friend-req-badge-pulse"></span>
                    <h3 class="friend-req-title">Permintaan Pertemanan Masuk (<?= count($incomingRequests) ?>)</h3>
                </div>
                <span style="font-size:11px;color:var(--text-muted)">Perlu persetujuan Anda</span>
            </div>
            <div class="friend-req-list">
                <?php foreach ($incomingRequests as $req): ?>
                    <?php
                        $rInit = strtoupper(mb_substr($req['requester_name'] ?: ($req['requester_username'] ?: 'U'), 0, 1));
                    ?>
                    <div class="friend-req-item" id="friendReqItem-<?= (int)$req['request_id'] ?>">
                        <div class="friend-req-user-info">
                            <div class="conv-avatar sm">
                                <span><?= esc($rInit) ?></span>
                            </div>
                            <div class="friend-req-names">
                                <div class="friend-req-name"><?= esc($req['requester_name']) ?></div>
                                <div class="friend-req-user">@<?= esc($req['requester_username']) ?></div>
                            </div>
                        </div>
                        <div class="friend-req-buttons">
                            <button type="button" class="btn-req-accept" onclick="respondFriendRequest(<?= (int)$req['request_id'] ?>, 'accept')">
                                Terima
                            </button>
                            <button type="button" class="btn-req-reject" onclick="respondFriendRequest(<?= (int)$req['request_id'] ?>, 'reject')">
                                Tolak
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Segmented Filter Tabs (WhatsApp Style) -->
    <div class="conv-tabs-bar">
        <button type="button" class="conv-tab-btn active" data-filter="all" onclick="filterConversations('all', this)">
            Semua
        </button>
        <button type="button" class="conv-tab-btn" data-filter="direct" onclick="filterConversations('direct', this)">
            Teman (Direct)
        </button>
        <button type="button" class="conv-tab-btn" data-filter="marketplace" onclick="filterConversations('marketplace', this)">
            Marketplace
        </button>
        <button type="button" class="conv-tab-btn" data-filter="archived" onclick="filterConversations('archived', this)" id="tabBtnArchived">
            📦 Diarsipkan <span id="archivedBadgeCount" class="conv-tab-badge" style="<?= empty($archivedCount) ? 'display:none;' : '' ?>"><?= (int)($archivedCount ?? 0) ?></span>
        </button>
    </div>

    <!-- Conversations List -->
    <?php if (empty($conversations)): ?>
        <div class="conv-empty-state">
            <div class="conv-empty-icon">💬</div>
            <h3 class="conv-empty-title">Belum Ada Obrolan</h3>
            <p class="conv-empty-desc">
                Tambahkan teman via username atau mulai transaksi jual beli di Marketplace untuk mengobrol seperti WhatsApp.
            </p>
            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <button type="button" class="conv-empty-btn" onclick="openAddFriendModal()">
                    <span>➕ Tambah Teman</span>
                </button>
                <a href="/marketplace" class="conv-empty-btn secondary">
                    <span>🛍️ Marketplace</span>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="conv-list" id="convList">
            <?php foreach ($conversations as $conv): ?>
                <?php
                    $isDirect = (($conv['type'] ?? '') === 'direct');
                    $unread   = (int)($conv['unread_count'] ?? 0);
                    $partnerName = $conv['partner_name'] ?: 'Pengguna';
                    $initial  = strtoupper(mb_substr($partnerName, 0, 1));
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
                ?>

                <?php if ($isDirect): ?>
                    <!-- DIRECT CHAT (TEMAN) -->
                    <div class="conv-item direct-item <?= $unread > 0 ? 'unread' : '' ?> <?= !empty($conv['is_pinned']) ? 'pinned' : '' ?>" 
                         data-type="direct" 
                         data-target-id="<?= (int)$conv['target_id'] ?>" 
                         data-target-sub-id="0" 
                         data-pinned="<?= !empty($conv['is_pinned']) ? '1' : '0' ?>" 
                         data-archived="<?= !empty($conv['is_archived']) ? '1' : '0' ?>" 
                         id="convCard_direct_<?= (int)$conv['target_id'] ?>_0"
                         onclick="openDirectChat(<?= (int)$conv['partner_id'] ?>, '<?= esc(addslashes($partnerName)) ?>', '<?= esc(addslashes($conv['partner_username'] ?? '')) ?>', '<?= esc(addslashes($conv['partner_avatar'] ?? '')) ?>')">
                        <div class="conv-avatar">
                            <span><?= esc($initial) ?></span>
                            <span class="conv-status-badge direct"></span>
                        </div>
                        <div class="conv-content">
                            <div class="conv-top-line">
                                <div class="conv-partner-name-wrap">
                                    <span class="conv-partner-name"><?= esc($partnerName) ?></span>
                                    <span class="conv-role-tag friend">Teman</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <?php if (!empty($conv['is_pinned'])): ?>
                                        <span class="conv-pin-badge" title="Disematkan ke atas">📌</span>
                                    <?php endif; ?>
                                    <span class="conv-time <?= $unread > 0 ? 'unread' : '' ?>"><?= esc($timeStr) ?></span>
                                    <button type="button" class="conv-menu-btn" onclick="openConvOptions(event, 'direct', <?= (int)$conv['target_id'] ?>, 0, <?= !empty($conv['is_pinned']) ? '1' : '0' ?>, <?= !empty($conv['is_archived']) ? '1' : '0' ?>, '<?= esc(addslashes($partnerName)) ?>')" title="Opsi Obrolan">⋮</button>
                                </div>
                            </div>

                            <div class="conv-user-handle">@<?= esc($conv['partner_username'] ?: 'user') ?></div>

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

                <?php else: ?>
                    <!-- MARKETPLACE CHAT -->
                    <?php
                        $isSeller   = ((int)$conv['seller_id'] === (int)$userId);
                        $partnerRole = $isSeller ? 'Calon Pembeli' : 'Penjual';
                    ?>
                    <div class="conv-item market-item <?= $unread > 0 ? 'unread' : '' ?> <?= !empty($conv['is_pinned']) ? 'pinned' : '' ?>" 
                         data-type="marketplace" 
                         data-target-id="<?= (int)$conv['target_id'] ?>" 
                         data-target-sub-id="<?= (int)($conv['target_sub_id'] ?? 0) ?>" 
                         data-pinned="<?= !empty($conv['is_pinned']) ? '1' : '0' ?>" 
                         data-archived="<?= !empty($conv['is_archived']) ? '1' : '0' ?>" 
                         id="convCard_marketplace_<?= (int)$conv['target_id'] ?>_<?= (int)($conv['target_sub_id'] ?? 0) ?>"
                         onclick="openChatFromConv(<?= (int)$conv['listing_id'] ?>, <?= (int)$conv['buyer_id'] ?>, '<?= esc(addslashes($conv['listing_title'])) ?>', '<?= esc(addslashes($partnerName)) ?>', '<?= esc(addslashes($conv['partner_phone'] ?? '')) ?>')">
                        <div class="conv-avatar">
                            <span><?= esc($initial) ?></span>
                            <span class="conv-status-badge"></span>
                        </div>
                        <div class="conv-content">
                            <div class="conv-top-line">
                                <div class="conv-partner-name-wrap">
                                    <span class="conv-partner-name"><?= esc($partnerName) ?></span>
                                    <span class="conv-role-tag <?= $isSeller ? 'buyer' : 'seller' ?>"><?= esc($partnerRole) ?></span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <?php if (!empty($conv['is_pinned'])): ?>
                                        <span class="conv-pin-badge" title="Disematkan ke atas">📌</span>
                                    <?php endif; ?>
                                    <span class="conv-time <?= $unread > 0 ? 'unread' : '' ?>"><?= esc($timeStr) ?></span>
                                    <button type="button" class="conv-menu-btn" onclick="openConvOptions(event, 'marketplace', <?= (int)$conv['target_id'] ?>, <?= (int)($conv['target_sub_id'] ?? 0) ?>, <?= !empty($conv['is_pinned']) ? '1' : '0' ?>, <?= !empty($conv['is_archived']) ? '1' : '0' ?>, '<?= esc(addslashes($partnerName)) ?>')" title="Opsi Obrolan">⋮</button>
                                </div>
                            </div>

                            <div class="conv-listing-pill">
                                <span class="conv-listing-icon">🏷️</span>
                                <span class="conv-listing-title"><?= esc($conv['listing_title']) ?></span>
                                <span class="conv-listing-price">Rp <?= number_format((float)$conv['listing_price'], 0, ',', '.') ?></span>
                            </div>

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
                <?php endif; ?>

            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL 1: DIRECT CHAT (WHATSAPP STYLE ROOM)
══════════════════════════════════════════════════════════════ -->
<div class="market-chat-modal-overlay" id="directChatModal" style="display:none;" onclick="if(event.target===this)closeDirectChat()">
    <div class="market-chat-modal-box wa-style">
        <!-- Header -->
        <div class="market-chat-header wa-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <button type="button" class="market-chat-back-btn" onclick="closeDirectChat()" title="Kembali">←</button>
                <div class="conv-avatar sm" id="directChatAvatar" style="width:34px;height:34px;font-size:14px;">
                    <span>T</span>
                </div>
                <div class="market-chat-header-info">
                    <div class="market-chat-header-title" id="directChatPartnerName">Nama Teman</div>
                    <div class="market-chat-header-sub">
                        <span class="market-chat-status-dot"></span>
                        <span id="directChatPartnerUser">Online</span>
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <button type="button" class="market-chat-close-btn" onclick="openActiveChatOptions('direct')" title="Opsi Obrolan" style="font-size:16px;font-weight:bold;">⋮</button>
                <button type="button" class="market-chat-close-btn" onclick="closeDirectChat()" title="Tutup">✕</button>
            </div>
        </div>

        <!-- Messages Feed -->
        <div class="market-chat-body wa-wallpaper" id="directChatMessages">
            <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:12px;">
                Memuat riwayat chat...
            </div>
        </div>

        <!-- Integrated Emoji Tray (WhatsApp style) -->
        <div class="wa-emoji-tray" id="directChatEmojiTray" style="display:none;">
            <div class="wa-emoji-tray-header">
                <span class="wa-emoji-tray-title">Pilih Emoji</span>
                <button type="button" class="wa-emoji-tray-close" onclick="closeChatEmojiTray('direct')">✕</button>
            </div>
            <div class="wa-emoji-tray-grid" id="directEmojiGrid"></div>
        </div>

        <!-- Input Footer -->
        <form class="market-chat-footer wa-footer" id="directChatForm" onsubmit="sendDirectMessage(event)">
            <div class="wa-input-pill">
                <span class="wa-input-icon" onclick="toggleChatEmojiTray('direct')" title="Pilih Emoji" role="button" style="cursor:pointer;">😊</span>
                <input type="text" id="directChatInputMsg" class="market-chat-input wa-input" placeholder="Ketik pesan..." autocomplete="off">
                <span class="wa-input-icon" onclick="triggerPwaChatUpload('file')" title="Lampirkan Berkas" role="button" style="cursor:pointer;">📎</span>
                <span class="wa-input-icon" onclick="triggerPwaChatUpload('photo')" title="Kirim Foto" role="button" style="cursor:pointer;">📷</span>
            </div>
            <button type="submit" id="btnSendDirectMsg" class="market-chat-send-btn wa-send-btn" title="Kirim Pesan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL 2: MARKETPLACE CHAT (PRE-EXISTING)
══════════════════════════════════════════════════════════════ -->
<div class="market-chat-modal-overlay" id="marketChatModal" style="display:none;" onclick="if(event.target===this)closeMarketChat()">
    <div class="market-chat-modal-box wa-style">
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
            <div style="display:flex;align-items:center;gap:6px;">
                <a href="#" id="chatModalWaBtn" target="_blank" class="chat-wa-header-btn" style="display:none;" title="Lanjutkan di WhatsApp">
                    🟢 WA
                </a>
                <button type="button" class="market-chat-close-btn" onclick="openActiveChatOptions('marketplace')" title="Opsi Obrolan" style="font-size:16px;font-weight:bold;">⋮</button>
                <button type="button" class="market-chat-close-btn" onclick="closeMarketChat()" title="Tutup Chat">✕</button>
            </div>
        </div>

        <div class="chat-pinned-listing" id="chatPinnedListing" style="display:none;">
            <div class="chat-pinned-info">
                <span class="chat-pinned-tag">Produk</span>
                <span class="chat-pinned-title" id="pinnedListingTitle">-</span>
            </div>
            <a href="#" id="pinnedListingLink" class="chat-pinned-btn" target="_blank">Lihat Detail &gt;</a>
        </div>

        <div class="market-chat-body wa-wallpaper" id="chatModalMessages">
            <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:12px;">
                Memuat riwayat percakapan...
            </div>
        </div>

        <!-- Integrated Emoji Tray (WhatsApp style) -->
        <div class="wa-emoji-tray" id="marketChatEmojiTray" style="display:none;">
            <div class="wa-emoji-tray-header">
                <span class="wa-emoji-tray-title">Pilih Emoji</span>
                <button type="button" class="wa-emoji-tray-close" onclick="closeChatEmojiTray('market')">✕</button>
            </div>
            <div class="wa-emoji-tray-grid" id="marketEmojiGrid"></div>
        </div>

        <form class="market-chat-footer wa-footer" id="marketChatForm" onsubmit="sendChatMessage(event)">
            <div class="wa-input-pill">
                <span class="wa-input-icon" onclick="toggleChatEmojiTray('market')" title="Pilih Emoji" role="button" style="cursor:pointer;">😊</span>
                <input type="text" id="chatInputMessage" class="market-chat-input wa-input" placeholder="Ketik pesan..." autocomplete="off">
                <span class="wa-input-icon" onclick="triggerPwaChatUpload('file')" title="Lampirkan Berkas" role="button" style="cursor:pointer;">📎</span>
                <span class="wa-input-icon" onclick="triggerPwaChatUpload('photo')" title="Kirim Foto" role="button" style="cursor:pointer;">📷</span>
            </div>
            <button type="submit" id="btnSendChatMsg" class="market-chat-send-btn wa-send-btn" title="Kirim Pesan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL 3: TAMBAH TEMAN (SEARCH USERNAME & REQUEST)
══════════════════════════════════════════════════════════════ -->
<div class="mini-modal-overlay" id="addFriendModalOverlay" onclick="if(event.target===this)closeAddFriendModal()">
    <div class="mini-modal" style="max-width:440px;padding:22px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:22px;">➕</span>
                <h3 style="margin:0;font-size:17px;font-weight:800;">Tambah Teman</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeAddFriendModal()">✕</button>
        </div>
        <p style="font-size:12px;color:var(--text-muted);margin:0 0 16px;">
            Cari akun pengguna lain berdasarkan <strong>@username</strong> untuk mengirimkan permintaan pertemanan.
        </p>

        <form onsubmit="searchUserFriends(event)" style="display:flex;gap:8px;margin-bottom:16px;">
            <input type="text" id="inputSearchUsername" class="form-input" placeholder="Masukkan username, misal: budi..." style="flex:1;" autocomplete="off" required>
            <button type="submit" class="btn-save-small" style="padding:10px 16px;flex:none;">Cari</button>
        </form>

        <div id="addFriendSearchResult" style="min-height:80px;">
            <div style="text-align:center;padding:24px 10px;color:var(--text-muted);font-size:12px;">
                Ketikkan username di atas lalu tekan Cari.
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL 4: DAFTAR KONTAK TEMAN (START DIRECT CHAT)
══════════════════════════════════════════════════════════════ -->
<div class="mini-modal-overlay" id="friendsListModalOverlay" onclick="if(event.target===this)closeFriendsListModal()">
    <div class="mini-modal" style="max-width:440px;padding:22px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:22px;">👥</span>
                <h3 style="margin:0;font-size:17px;font-weight:800;">Kontak Teman (<?= count($friends ?? []) ?>)</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeFriendsListModal()">✕</button>
        </div>

        <?php if (empty($friends)): ?>
            <div style="text-align:center;padding:32px 10px;color:var(--text-muted);font-size:13px;">
                <div style="font-size:32px;margin-bottom:8px;">🤝</div>
                Belum ada teman yang disetujui.<br>
                Gunakan fitur <strong>Tambah Teman</strong> untuk mulai berteman!
            </div>
        <?php else: ?>
            <div style="max-height:360px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($friends as $f): ?>
                    <?php $fInit = strtoupper(mb_substr($f['name'] ?: ($f['username'] ?: 'T'), 0, 1)); ?>
                    <div class="friend-contact-item" onclick="startChatWithFriend(<?= (int)$f['friend_id'] ?>, '<?= esc(addslashes($f['name'])) ?>', '<?= esc(addslashes($f['username'])) ?>', '<?= esc(addslashes($f['avatar'] ?? '')) ?>')">
                        <div class="conv-avatar sm">
                            <span><?= esc($fInit) ?></span>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:14px;color:var(--text-primary);"><?= esc($f['name']) ?></div>
                            <div style="font-size:11px;color:var(--text-muted);">@<?= esc($f['username']) ?></div>
                        </div>
                        <button type="button" class="btn-chat-now" onclick="event.stopPropagation(); startChatWithFriend(<?= (int)$f['friend_id'] ?>, '<?= esc(addslashes($f['name'])) ?>', '<?= esc(addslashes($f['username'])) ?>', '<?= esc(addslashes($f['avatar'] ?? '')) ?>')">💬 Chat</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL 5: CONVERSATION ACTION SHEET (PIN, ARCHIVE, DELETE)
══════════════════════════════════════════════════════════════ -->
<div class="mini-modal-overlay" id="convActionModalOverlay" onclick="if(event.target===this)closeConvActionModal()">
    <div class="mini-modal" style="max-width:380px;padding:20px;border-radius:22px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                <div class="conv-avatar sm" id="convActionAvatar" style="width:36px;height:36px;font-size:14px;flex-shrink:0;">
                    <span>T</span>
                </div>
                <div style="min-width:0;">
                    <h4 id="convActionName" style="margin:0;font-size:15px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Nama Pengguna</h4>
                    <span id="convActionSub" style="font-size:11px;color:var(--text-muted);">Opsi Obrolan</span>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeConvActionModal()">✕</button>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;">
            <button type="button" id="btnActionPin" onclick="executeConvAction('pin')" class="conv-sheet-action-btn">
                <span class="conv-action-icon" id="iconActionPin">📌</span>
                <span class="conv-action-text" id="textActionPin">Sematkan ke Atas</span>
            </button>
            <button type="button" id="btnActionArchive" onclick="executeConvAction('archive')" class="conv-sheet-action-btn">
                <span class="conv-action-icon" id="iconActionArchive">📦</span>
                <span class="conv-action-text" id="textActionArchive">Arsipkan Obrolan</span>
            </button>
            <button type="button" id="btnActionDelete" onclick="executeConvAction('delete')" class="conv-sheet-action-btn danger">
                <span class="conv-action-icon">🗑️</span>
                <span class="conv-action-text">Hapus Obrolan</span>
            </button>
        </div>
    </div>
</div>

<!-- Hidden file inputs for PWA Chat Uploads -->
<input type="file" id="pwaChatFileInput" style="display:none;" onchange="handlePwaFileSelected(this)">
<input type="file" id="pwaChatPhotoInput" accept="image/*" capture="environment" style="display:none;" onchange="handlePwaFileSelected(this)">

<!-- Image Lightbox Modal for PWA -->
<div id="pwaChatLightbox" class="market-chat-modal-overlay" style="display:none;z-index:99999;background:rgba(0,0,0,0.88);cursor:zoom-out;align-items:center;justify-content:center;" onclick="closePwaLightbox()">
    <div style="position:relative;max-width:92vw;max-height:90vh;display:flex;align-items:center;justify-content:center;" onclick="event.stopPropagation()">
        <img id="pwaLightboxImg" src="" style="max-width:100%;max-height:85vh;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,0.6);" alt="Foto Pratinjau">
        <button type="button" onclick="closePwaLightbox()" style="position:absolute;top:-38px;right:0;background:rgba(255,255,255,0.25);color:#fff;border:none;border-radius:50%;width:34px;height:34px;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;">✕</button>
    </div>
</div>

<style>
/* ══════════════════════════════════════════════════════════════
   PWA WHATSAPP CONVERSATIONS & FRIEND SYSTEM STYLES
══════════════════════════════════════════════════════════════ */
.market-conversations-page {
    max-width: 680px;
    margin: 0 auto;
    padding: 16px 16px 100px;
}

.conv-header-wrap {
    margin-bottom: 14px;
}
.conv-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
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

/* Action Buttons Header */
.conv-actions-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-add-friend-top {
    background: #2563EB;
    color: #fff;
    border: none;
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(37, 99, 235, 0.3);
    transition: transform 0.15s ease, background 0.15s ease;
}
.btn-add-friend-top:hover { background: #1D4ED8; transform: translateY(-1px); }
.btn-friends-list-top {
    background: var(--bg-card);
    color: var(--text-primary);
    border: 1px solid var(--border);
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.15s ease, border-color 0.15s ease;
}
.btn-friends-list-top:hover { border-color: #2563EB; transform: translateY(-1px); }

/* Friend Requests Banner */
.friend-requests-card {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%);
    border: 1.5px solid rgba(37, 99, 235, 0.25);
    border-radius: 20px;
    padding: 14px 16px;
    margin-bottom: 16px;
}
.friend-req-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.friend-req-title {
    margin: 0;
    font-size: 13px;
    font-weight: 800;
    color: var(--text-primary);
}
.friend-req-badge-pulse {
    width: 8px;
    height: 8px;
    background: #2563EB;
    border-radius: 50%;
    animation: pulseDot 1.5s infinite;
}
.friend-req-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.friend-req-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 10px 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}
.friend-req-user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.friend-req-names {
    display: flex;
    flex-direction: column;
}
.friend-req-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
}
.friend-req-user {
    font-size: 11px;
    color: var(--text-muted);
}
.friend-req-buttons {
    display: flex;
    align-items: center;
    gap: 6px;
}
.btn-req-accept {
    background: #10B981;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}
.btn-req-accept:hover { background: #059669; }
.btn-req-reject {
    background: transparent;
    color: #EF4444;
    border: 1px solid #FCA5A5;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
.btn-req-reject:hover { background: #FEE2E2; }

/* Filter Tabs Bar (WhatsApp Style) */
.conv-tabs-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--bg);
    padding: 4px;
    border-radius: 12px;
    margin-bottom: 14px;
    border: 1px solid var(--border);
}
.conv-tab-btn {
    flex: 1;
    background: transparent;
    border: none;
    padding: 8px 12px;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.15s ease;
}
.conv-tab-btn.active {
    background: var(--bg-card);
    color: var(--text-primary);
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}

/* Empty State */
.conv-empty-state {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 44px 20px;
    text-align: center;
    margin-top: 14px;
}
.conv-empty-icon { font-size: 44px; margin-bottom: 12px; }
.conv-empty-title { font-size: 16px; font-weight: 800; color: var(--text-primary); margin: 0 0 6px; }
.conv-empty-desc { font-size: 13px; color: var(--text-muted); max-width: 360px; margin: 0 auto 18px; line-height: 1.5; }
.conv-empty-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #2563EB;
    color: #fff;
    padding: 10px 20px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}
.conv-empty-btn.secondary { background: #059669; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3); }

/* Conversation List Items */
.conv-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.conv-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 12px 14px;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}
.conv-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}
.conv-item.unread {
    border-color: rgba(37, 99, 235, 0.4);
    background: var(--primary-dim, rgba(37, 99, 235, 0.03));
}
.conv-item.pinned {
    background: var(--primary-dim, rgba(37, 99, 235, 0.04));
    border-color: rgba(37, 99, 235, 0.3);
}
.conv-pin-badge {
    font-size: 12px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
}
.conv-menu-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 18px;
    font-weight: 800;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 6px;
    line-height: 1;
    transition: background 0.15s ease, color 0.15s ease;
}
.conv-menu-btn:hover {
    background: var(--border);
    color: var(--text-primary);
}
.conv-tab-badge {
    background: #2563EB;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 1px 6px;
    border-radius: 10px;
    margin-left: 4px;
}
.conv-sheet-action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 12px 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: left;
}
.conv-sheet-action-btn:hover {
    background: var(--primary-dim, rgba(37, 99, 235, 0.08));
    border-color: #2563EB;
}
.conv-sheet-action-btn.danger {
    color: #DC2626;
    border-color: rgba(220, 38, 38, 0.2);
}
.conv-sheet-action-btn.danger:hover {
    background: #FEE2E2;
    border-color: #DC2626;
}
.conv-avatar {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
    color: #fff;
    font-size: 17px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    flex-shrink: 0;
}
.conv-avatar.sm { width: 38px; height: 38px; font-size: 14px; }
.conv-status-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 11px;
    height: 11px;
    border-radius: 50%;
    background: #10B981;
    border: 2px solid var(--bg-card);
}
.conv-status-badge.direct { background: #3B82F6; }

.conv-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.conv-top-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
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
.conv-user-handle {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: -2px;
}
.conv-role-tag {
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 6px;
    white-space: nowrap;
}
.conv-role-tag.friend { background: #DBEAFE; color: #1D4ED8; }
.conv-role-tag.buyer  { background: #EDE9FE; color: #6D28D9; }
.conv-role-tag.seller { background: #D1FAE5; color: #047857; }

.conv-time {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 500;
}
.conv-time.unread { color: #2563EB; font-weight: 700; }

.conv-listing-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--bg);
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 11px;
    color: var(--text-secondary);
    max-width: fit-content;
}
.conv-listing-title {
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
}
.conv-listing-price { font-weight: 800; color: #059669; }

.conv-bottom-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.conv-snippet {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.conv-snippet.bold { font-weight: 700; color: var(--text-primary); }
.conv-tick-icon { color: #2563EB; font-weight: 800; margin-right: 3px; }

.conv-badge {
    background: #2563EB;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 999px;
    flex-shrink: 0;
}

/* Contact List Item inside Friends Modal */
.friend-contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    cursor: pointer;
    transition: background 0.15s ease;
}
.friend-contact-item:hover { background: var(--border); }
.btn-chat-now {
    background: #10B981;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}

/* User Search Card in Add Friend Modal */
.search-user-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 10px 12px;
    margin-bottom: 8px;
}

/* ══════════════════════════════════════════════════════════════
   WHATSAPP MODAL ROOM STYLES
══════════════════════════════════════════════════════════════ */
.market-chat-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(8px);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 12px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}
.market-chat-modal-overlay.show,
.market-chat-modal-overlay.open {
    display: flex !important;
    opacity: 1 !important;
    pointer-events: all !important;
}
.market-chat-modal-box.wa-style {
    width: 100%;
    max-width: 480px;
    height: 90dvh;
    background: #EFEAE2;
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
}
[data-theme="dark"] .market-chat-modal-box.wa-style {
    background: #0B141A;
}

.market-chat-header.wa-header {
    background: #075E54;
    color: #fff;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.market-chat-back-btn {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    padding: 0 4px;
}
.market-chat-header-title {
    font-size: 14px;
    font-weight: 800;
    color: #fff;
}
.market-chat-header-sub {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    gap: 5px;
}
.market-chat-status-dot {
    width: 6px;
    height: 6px;
    background: #25D366;
    border-radius: 50%;
}
.market-chat-close-btn {
    background: rgba(255,255,255,0.15);
    border: none;
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 12px;
    cursor: pointer;
}
.chat-wa-header-btn {
    background: #25D366;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 8px;
    border-radius: 8px;
    text-decoration: none;
}

.chat-pinned-listing {
    background: #FFFFFF;
    border-bottom: 1px solid #E2E8F0;
    padding: 6px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
[data-theme="dark"] .chat-pinned-listing {
    background: #1F2C34;
    border-color: #2A3942;
}
.chat-pinned-tag {
    font-size: 10px;
    font-weight: 800;
    background: #E2E8F0;
    padding: 1px 5px;
    border-radius: 4px;
    color: #475569;
}
.chat-pinned-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-primary);
    margin-left: 6px;
}
.chat-pinned-btn {
    font-size: 11px;
    font-weight: 700;
    color: #059669;
    text-decoration: none;
}

/* Chat Wallpaper & Feed */
.market-chat-body.wa-wallpaper {
    flex: 1;
    overflow-y: auto;
    padding: 14px 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background-color: #EFEAE2;
    background-image: radial-gradient(rgba(0,0,0,0.06) 1px, transparent 0);
    background-size: 20px 20px;
}
[data-theme="dark"] .market-chat-body.wa-wallpaper {
    background-color: #0B141A;
    background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 0);
}

.chat-date-separator {
    align-self: center;
    background: rgba(0,0,0,0.25);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 8px;
    margin: 8px 0;
    text-transform: uppercase;
}

.chat-bubble {
    max-width: 78%;
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 13px;
    line-height: 1.4;
    word-break: break-word;
    position: relative;
    box-shadow: 0 1px 2px rgba(0,0,0,0.12);
}
.chat-bubble.wa-me {
    align-self: flex-end;
    background: #D9FDD3;
    color: #111B21;
    border-top-right-radius: 2px;
}
[data-theme="dark"] .chat-bubble.wa-me {
    background: #005C4B;
    color: #E9EDEF;
}

.chat-bubble.wa-other {
    align-self: flex-start;
    background: #FFFFFF;
    color: #111B21;
    border-top-left-radius: 2px;
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
    margin-top: 3px;
}
.chat-bubble-time {
    font-size: 10px;
    color: rgba(0,0,0,0.45);
}
[data-theme="dark"] .chat-bubble-time {
    color: rgba(255,255,255,0.55);
}
.chat-read-tick {
    font-size: 11px;
    color: #667781;
}
.chat-read-tick.read { color: #53BDEB; }

/* Input Footer */
.market-chat-footer.wa-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    background: #F0F2F5;
}
[data-theme="dark"] .market-chat-footer.wa-footer {
    background: #202C33;
}
.wa-input-pill {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border-radius: 24px;
    padding: 6px 14px;
}
[data-theme="dark"] .wa-input-pill {
    background: #2A3942;
}
.wa-input-icon {
    font-size: 18px;
    cursor: pointer;
    user-select: none;
}
.market-chat-input.wa-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 13px;
    background: transparent;
    color: var(--text-primary);
}
.market-chat-send-btn.wa-send-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #00A884;
    color: #fff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: transform 0.15s ease, background 0.15s ease;
}
.market-chat-send-btn.wa-send-btn:hover { background: #059669; transform: scale(1.05); }
.market-chat-send-btn.wa-send-btn:active { transform: scale(0.95); }

/* PWA WhatsApp Emoji Tray Styles */
.wa-emoji-tray {
    background: #FFFFFF;
    border-top: 1px solid #E2E8F0;
    max-height: 200px;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    width: 100%;
    z-index: 10;
}
[data-theme="dark"] .wa-emoji-tray {
    background: #1F2C34;
    border-top-color: #2A3942;
}
.wa-emoji-tray-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px;
    background: rgba(0,0,0,0.03);
    border-bottom: 1px solid rgba(0,0,0,0.06);
}
[data-theme="dark"] .wa-emoji-tray-header {
    background: rgba(255,255,255,0.04);
    border-bottom-color: rgba(255,255,255,0.06);
}
.wa-emoji-tray-title {
    font-size: 11px;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
[data-theme="dark"] .wa-emoji-tray-title {
    color: #8696A0;
}
.wa-emoji-tray-close {
    background: none;
    border: none;
    font-size: 14px;
    color: #64748B;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.wa-emoji-tray-close:hover {
    background: rgba(0,0,0,0.08);
}
.wa-emoji-tray-grid {
    padding: 8px 10px;
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 4px;
    overflow-y: auto;
    max-height: 160px;
}
.wa-emoji-btn {
    font-size: 22px;
    line-height: 1;
    padding: 6px 2px;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 8px;
    user-select: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.1s ease, background 0.1s ease;
}
.wa-emoji-btn:hover {
    background: rgba(0, 168, 132, 0.15);
    transform: scale(1.22);
}
.wa-emoji-btn:active {
    transform: scale(0.95);
}
.chat-bubble-img-wrap {
    margin: 3px 0 5px;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
}
.chat-bubble-img {
    width: 100%;
    max-width: 260px;
    max-height: 220px;
    object-fit: cover;
    border-radius: 10px;
    display: block;
    transition: transform 0.2s ease;
}
.chat-bubble-img:hover {
    transform: scale(1.02);
}
.chat-bubble-file {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    margin: 3px 0 5px;
    text-decoration: none;
    color: var(--text-primary);
    font-weight: 600;
    font-size: 13px;
    transition: background 0.15s;
}
.chat-bubble-file:hover {
    background: rgba(0, 0, 0, 0.1);
}
</style>

<script>
const csrfTokenName = '<?= csrf_token() ?>';
const csrfTokenHash = '<?= csrf_hash() ?>';

function getCsrfToken() {
    const match = document.cookie.match(new RegExp('(^|;\\s*)csrf_cookie_name=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : csrfTokenHash;
}

let currentDirectFriendId = null;
let currentChatListingId  = null;
let currentChatBuyerId    = null;
let chatPollTimer         = null;
let isSendingMsg          = false;

/* Filter tabs (support archived exclusion & inclusion) */
let currentConvFilter = 'all';

function filterConversations(type, btn) {
    currentConvFilter = type;
    document.querySelectorAll('.conv-tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const items = document.querySelectorAll('.conv-item');
    items.forEach(item => {
        const itemType = item.getAttribute('data-type');
        const isArchived = item.getAttribute('data-archived') === '1';

        if (type === 'archived') {
            item.style.display = isArchived ? 'flex' : 'none';
        } else {
            // Main tabs exclude archived
            if (isArchived) {
                item.style.display = 'none';
            } else if (type === 'all' || itemType === type) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        }
    });
}

let activeActionConv = null;

function openConvOptions(e, type, targetId, targetSubId, isPinned, isArchived, partnerName) {
    if (e) e.stopPropagation();
    activeActionConv = {
        type: type,
        targetId: targetId,
        targetSubId: targetSubId,
        isPinned: (isPinned == 1 || isPinned === true),
        isArchived: (isArchived == 1 || isArchived === true),
        name: partnerName
    };

    document.getElementById('convActionName').textContent = partnerName;
    document.getElementById('convActionSub').textContent = (type === 'direct' ? 'Teman (Direct)' : 'Marketplace');
    document.getElementById('convActionAvatar').querySelector('span').textContent = partnerName.charAt(0).toUpperCase();

    // Text & icon based on current state
    document.getElementById('textActionPin').textContent = activeActionConv.isPinned ? 'Lepas Sematan' : 'Sematkan ke Atas';
    document.getElementById('textActionArchive').textContent = activeActionConv.isArchived ? 'Buka dari Arsip' : 'Arsipkan Obrolan';
    document.getElementById('iconActionArchive').textContent = activeActionConv.isArchived ? '📂' : '📦';

    document.getElementById('convActionModalOverlay').classList.add('open');
}

function closeConvActionModal() {
    const modal = document.getElementById('convActionModalOverlay');
    if (modal) modal.classList.remove('open');
    activeActionConv = null;
}

function executeConvAction(action) {
    if (!activeActionConv) return;
    const conv = activeActionConv;

    if (action === 'delete') {
        if (!confirm(`Hapus seluruh riwayat obrolan dengan "${conv.name}"?\nPesan yang dihapus tidak dapat dikembalikan.`)) {
            return;
        }
    }

    const endpoint = '/chat/conversation/' + action;
    const formData = new FormData();
    formData.append('type', conv.type);
    formData.append('target_id', conv.targetId);
    formData.append('target_sub_id', conv.targetSubId);

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        closeConvActionModal();
        if (res.status === 'success') {
            closeDirectChat();
            closeMarketChat();
            window.location.reload();
        } else {
            alert(res.message || 'Gagal memproses aksi.');
        }
    })
    .catch(err => {
        closeConvActionModal();
        alert('Gagal menghubungi server: ' + err);
    });
}

function openActiveChatOptions(type) {
    if (type === 'direct') {
        if (!currentDirectFriendId) return;
        const name = document.getElementById('directChatPartnerName')?.textContent || 'Teman';
        const card = document.getElementById(`convCard_direct_${currentDirectFriendId}`);
        const isPinned = card ? (card.getAttribute('data-pinned') === '1') : 0;
        const isArchived = card ? (card.getAttribute('data-archived') === '1') : 0;
        openConvOptions(null, 'direct', currentDirectFriendId, 0, isPinned, isArchived, name);
    } else if (type === 'marketplace') {
        if (!currentChatListingId) return;
        const name = document.getElementById('chatModalPartnerName')?.textContent || 'Obrolan Marketplace';
        const card = document.getElementById(`convCard_marketplace_${currentChatListingId}_${currentChatBuyerId || 0}`);
        const isPinned = card ? (card.getAttribute('data-pinned') === '1') : 0;
        const isArchived = card ? (card.getAttribute('data-archived') === '1') : 0;
        openConvOptions(null, 'marketplace', currentChatListingId, currentChatBuyerId || 0, isPinned, isArchived, name);
    }
}

/* PWA Chat Media & Emoji Handlers */
const pwaPopularEmojis = [
    '😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😋',
    '😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥','😌',
    '😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵','🥶','🥴','😵','🤯','🤠','🥳','🥸','😎','🤓',
    '🧐','😕','😟','🙁','😮','😯','😲','😳','🥺','😭','😱','👍','👎','👏','🙌','🤝','🙏','✌️','🤞','🤟',
    '👌','👈','👉','👆','👇','✋','👋','💪','👀','❤️','🧡','💛','💚','💙','💜','🖤','💔','❣️','💕','🔥',
    '⭐','✨','⚡','💯','💥','🎉','🎊','🎁','💰','💵','💸','📦','🛒','🏷️','✅','❌','⚠️','🔔','💬','🚀'
];

function toggleChatEmojiTray(type) {
    const isDirect = (type === 'direct');
    const trayId   = isDirect ? 'directChatEmojiTray' : 'marketChatEmojiTray';
    const gridId   = isDirect ? 'directEmojiGrid' : 'marketEmojiGrid';
    const inputId  = isDirect ? 'directChatInputMsg' : 'chatInputMessage';

    const tray = document.getElementById(trayId);
    if (!tray) return;

    if (tray.style.display === 'flex') {
        tray.style.display = 'none';
        return;
    }

    const grid = document.getElementById(gridId);
    if (grid && grid.children.length === 0) {
        grid.innerHTML = pwaPopularEmojis.map(e => `
            <button type="button" class="wa-emoji-btn" onclick="insertEmojiIntoInput('${inputId}', '${e}')">${e}</button>
        `).join('');
    }

    tray.style.display = 'flex';
}

function closeChatEmojiTray(type) {
    const isDirect = (type === 'direct');
    const tray = document.getElementById(isDirect ? 'directChatEmojiTray' : 'marketChatEmojiTray');
    if (tray) tray.style.display = 'none';
}

function insertEmojiIntoInput(inputId, emoji) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const start = (input.selectionStart !== null && input.selectionStart !== undefined) ? input.selectionStart : input.value.length;
    const end = (input.selectionEnd !== null && input.selectionEnd !== undefined) ? input.selectionEnd : input.value.length;
    const text = input.value;

    input.value = text.substring(0, start) + emoji + text.substring(end);
    const newPos = start + emoji.length;
    input.setSelectionRange(newPos, newPos);
    input.focus();
}

function markCardAsRead(type, targetId, subId = 0) {
    const cardId = (type === 'direct')
        ? `convCard_direct_${targetId}`
        : `convCard_marketplace_${targetId}_${subId}`;
    const card = document.getElementById(cardId);
    if (card) {
        card.classList.remove('unread');
        const badge = card.querySelector('.conv-badge');
        if (badge) badge.remove();
        const snippet = card.querySelector('.conv-snippet');
        if (snippet) snippet.classList.remove('bold');
        const time = card.querySelector('.conv-time');
        if (time) time.classList.remove('unread');
    }
}

function openPwaLightbox(url) {
    const box = document.getElementById('pwaChatLightbox');
    const img = document.getElementById('pwaLightboxImg');
    if (box && img) {
        img.src = url;
        box.style.display = 'flex';
    }
}
function closePwaLightbox() {
    const box = document.getElementById('pwaChatLightbox');
    if (box) box.style.display = 'none';
}

function renderPwaMessageContent(rawMsg) {
    if (!rawMsg) return '';
    const str = String(rawMsg).trim();
    if (str.startsWith('[img:')) {
        const start = str.indexOf('[img:');
        const end = str.indexOf(']', start);
        if (end !== -1) {
            const imgUrl = str.substring(start + 5, end).trim();
            const caption = str.substring(end + 1).trim();
            return `
                <div class="chat-bubble-img-wrap" onclick="openPwaLightbox('${escapeHtml(imgUrl)}')">
                    <img src="${escapeHtml(imgUrl)}" class="chat-bubble-img" alt="Foto" loading="lazy" />
                </div>
                ${caption ? `<div style="margin-top:4px;">${escapeHtml(caption)}</div>` : ''}
            `;
        }
    }
    if (str.startsWith('[file:')) {
        const parts = str.substring(6, str.length - 1).split(':');
        const fileUrl = parts[0] || '#';
        const fileName = parts.slice(1).join(':') || 'Dokumen';
        return `
            <a href="${escapeHtml(fileUrl)}" target="_blank" download class="chat-bubble-file">
                <span style="font-size:20px;">📄</span>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;">${escapeHtml(fileName)}</span>
                <span style="font-size:13px;color:var(--text-muted);">⬇️ Unduh</span>
            </a>
        `;
    }
    return `<div>${escapeHtml(str)}</div>`;
}

function triggerPwaChatUpload(mode) {
    if (mode === 'photo') {
        const inp = document.getElementById('pwaChatPhotoInput');
        if (inp) { inp.value = ''; inp.click(); }
    } else {
        const inp = document.getElementById('pwaChatFileInput');
        if (inp) { inp.value = ''; inp.click(); }
    }
}

function handlePwaFileSelected(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    const isDirectModal = (document.getElementById('directChatModal')?.style.display === 'flex');
    const isMarketModal = (document.getElementById('marketChatModal')?.style.display === 'flex');

    if (!isDirectModal && !isMarketModal) {
        alert('Silakan buka ruang obrolan terlebih dahulu.');
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append(csrfTokenName, getCsrfToken());

    const activeInput = isDirectModal ? document.getElementById('directChatInputMsg') : document.getElementById('chatInputMessage');
    const oldPlaceholder = activeInput ? activeInput.placeholder : '';
    if (activeInput) activeInput.placeholder = 'Mengunggah berkas...';

    fetch('/chat/upload', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(r => r.json())
    .then(res => {
        if (activeInput) activeInput.placeholder = oldPlaceholder;
        if (res.status !== 'success' || !res.url) {
            alert(res.message || 'Gagal mengunggah berkas.');
            return;
        }

        let messagePayload = '';
        if (res.is_image) {
            messagePayload = `[img:${res.url}]`;
        } else {
            messagePayload = `[file:${res.url}:${res.filename || file.name}]`;
        }

        if (isDirectModal) {
            sendDirectMessagePayload(messagePayload);
        } else if (isMarketModal) {
            sendMarketMessagePayload(messagePayload);
        }
    })
    .catch(err => {
        if (activeInput) activeInput.placeholder = oldPlaceholder;
        console.error('Upload error:', err);
        alert('Gagal menghubungi server untuk mengunggah file.');
    });
}

function sendDirectMessagePayload(text) {
    if (!currentDirectFriendId) return;
    const formData = new FormData();
    formData.append('friend_id', currentDirectFriendId);
    formData.append('message', text);
    formData.append(csrfTokenName, getCsrfToken());

    fetch('<?= base_url('chat/direct/send') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            loadDirectMessages(true);
        }
    });
}

function sendMarketMessagePayload(text) {
    if (!currentChatListingId || !currentChatBuyerId) return;
    const formData = new FormData();
    formData.append('listing_id', currentChatListingId);
    formData.append('buyer_id', currentChatBuyerId);
    formData.append('message', text);
    formData.append(csrfTokenName, getCsrfToken());

    fetch('/marketplace/chat/send', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            loadChatMessages(true);
        }
    });
}

/* Direct Chat WhatsApp Modal */
function openDirectChat(friendId, friendName, friendUser, friendAvatar) {
    currentDirectFriendId = friendId;
    markCardAsRead('direct', friendId);
    closeChatEmojiTray('direct');

    const modal = document.getElementById('directChatModal');
    if (!modal) return;

    document.getElementById('directChatPartnerName').textContent = friendName;
    document.getElementById('directChatPartnerUser').textContent = friendUser ? '@' + friendUser : 'Online';
    
    const avInit = friendName ? friendName.charAt(0).toUpperCase() : 'T';
    document.getElementById('directChatAvatar').innerHTML = `<span>${avInit}</span>`;

    modal.style.display = 'flex';
    modal.classList.add('show', 'open');
    document.body.classList.add('modal-open');

    loadDirectMessages(true);
    clearInterval(chatPollTimer);
    chatPollTimer = setInterval(() => loadDirectMessages(false), 3000);

    setTimeout(() => {
        document.getElementById('directChatInputMsg')?.focus();
    }, 200);
}

function closeDirectChat() {
    currentDirectFriendId = null;
    closeChatEmojiTray('direct');
    clearInterval(chatPollTimer);
    const modal = document.getElementById('directChatModal');
    if (modal) {
        modal.classList.remove('show', 'open');
        modal.style.display = 'none';
    }
    document.body.classList.remove('modal-open');
}

function loadDirectMessages(autoScroll = false) {
    if (!currentDirectFriendId) return;

    fetch(`/chat/direct/messages?friend_id=${currentDirectFriendId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status !== 'success') return;
        const messages = res.messages || [];
        const bodyEl   = document.getElementById('directChatMessages');
        if (!bodyEl) return;

        if (messages.length === 0) {
            bodyEl.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted);font-size:12px;">👋 Mulai percakapan langsung dengan teman! Kirim salam sekarang.</div>';
            return;
        }

        let html = '';
        let lastDateStr = '';

        messages.forEach(msg => {
            const isMe = (parseInt(msg.sender_id) === parseInt(res.my_id));
            let msgDate = msg.created_at ? msg.created_at.split(' ')[0] : '';

            if (msgDate && msgDate !== lastDateStr) {
                lastDateStr = msgDate;
                html += '<div class="chat-date-separator">' + formatDateBadge(msgDate) + '</div>';
            }

            const timeStr = msg.created_at ? msg.created_at.substr(11, 5) : '';
            const bubbleClass = isMe ? 'chat-bubble wa-me' : 'chat-bubble wa-other';
            const tickHtml = isMe ? `<span class="chat-read-tick ${msg.is_read == 1 ? 'read' : ''}">✓✓</span>` : '';

            html += `
                <div class="${bubbleClass}">
                    ${renderPwaMessageContent(msg.message)}
                    <div class="chat-meta-row">
                        <span class="chat-bubble-time">${timeStr}</span>
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
    .catch(err => console.error('Error polling direct chat:', err));
}

function sendDirectMessage(e) {
    e.preventDefault();
    if (isSendingMsg || !currentDirectFriendId) return;

    const inputEl = document.getElementById('directChatInputMsg');
    const text    = (inputEl?.value || '').trim();
    if (!text) return;

    isSendingMsg = true;
    const btn = document.getElementById('btnSendDirectMsg');
    if (btn) btn.disabled = true;

    const formData = new FormData();
    formData.append('friend_id', currentDirectFriendId);
    formData.append('message', text);
    formData.append(csrfTokenName, getCsrfToken());

    fetch('<?= base_url('chat/direct/send') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(r => r.json())
    .then(res => {
        isSendingMsg = false;
        if (btn) btn.disabled = false;

        if (res.status === 'success') {
            if (inputEl) inputEl.value = '';
            loadDirectMessages(true);
        } else {
            alert(res.message || 'Gagal mengirim pesan.');
        }
    })
    .catch(err => {
        isSendingMsg = false;
        if (btn) btn.disabled = false;
        console.error('Send error:', err);
    });
}

/* Marketplace Chat Modal */
function openChatFromConv(listingId, buyerId, listingTitle, partnerName, partnerPhone) {
    currentChatListingId = listingId;
    currentChatBuyerId   = buyerId;
    markCardAsRead('marketplace', listingId, buyerId);
    closeChatEmojiTray('market');

    const modal = document.getElementById('marketChatModal');
    if (!modal) return;

    document.getElementById('chatModalPartnerName').textContent  = partnerName || 'Obrolan Marketplace';
    document.getElementById('chatModalListingTitle').textContent = listingTitle || 'Produk';
    document.getElementById('pinnedListingTitle').textContent    = listingTitle || 'Produk';
    document.getElementById('chatPinnedListing').style.display   = 'flex';

    modal.style.display = 'flex';
    modal.classList.add('show', 'open');
    document.body.classList.add('modal-open');

    loadChatMessages(true);
    clearInterval(chatPollTimer);
    chatPollTimer = setInterval(() => loadChatMessages(false), 3000);

    setTimeout(() => {
        document.getElementById('chatInputMessage')?.focus();
    }, 200);
}

function closeMarketChat() {
    currentChatListingId = null;
    currentChatBuyerId   = null;
    closeChatEmojiTray('market');
    clearInterval(chatPollTimer);
    const modal = document.getElementById('marketChatModal');
    if (modal) {
        modal.classList.remove('show', 'open');
        modal.style.display = 'none';
    }
    document.body.classList.remove('modal-open');
}

function loadChatMessages(autoScroll = false) {
    if (!currentChatListingId || !currentChatBuyerId) return;

    fetch(`<?= base_url('marketplace/chat/messages') ?>?listing_id=${currentChatListingId}&buyer_id=${currentChatBuyerId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const bodyEl = document.getElementById('marketChatModalBody');
        if (!bodyEl) return;

        const messages = (data && data.messages) ? data.messages : [];
        let html = '';
        let lastDateStr = null;

        messages.forEach(msg => {
            const isMe = msg.is_me == 1 || msg.is_me === true;
            const msgDate = msg.created_at ? msg.created_at.slice(0, 10) : '';

            if (msgDate && msgDate !== lastDateStr) {
                lastDateStr = msgDate;
                html += '<div class="chat-date-separator">' + formatDateBadge(msgDate) + '</div>';
            }

            const bubbleClass = isMe ? 'chat-bubble wa-me' : 'chat-bubble wa-other';
            const tickHtml = isMe ? `<span class="chat-read-tick ${msg.is_read == 1 ? 'read' : ''}">✓✓</span>` : '';

            html += `
                <div class="${bubbleClass}">
                    ${renderPwaMessageContent(msg.message)}
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

/* Modals Tambah Teman & Kontak */
function openAddFriendModal() {
    const modal = document.getElementById('addFriendModalOverlay');
    if (modal) modal.classList.add('open');
}
function closeAddFriendModal() {
    const modal = document.getElementById('addFriendModalOverlay');
    if (modal) modal.classList.remove('open');
}

function openFriendsListModal() {
    const modal = document.getElementById('friendsListModalOverlay');
    if (modal) modal.classList.add('open');
}
function closeFriendsListModal() {
    const modal = document.getElementById('friendsListModalOverlay');
    if (modal) modal.classList.remove('open');
}

function startChatWithFriend(friendId, friendName, friendUser, friendAvatar) {
    closeFriendsListModal();
    openDirectChat(friendId, friendName, friendUser, friendAvatar);
}

/* Search User */
function searchUserFriends(e) {
    e.preventDefault();
    const query = document.getElementById('inputSearchUsername')?.value.trim();
    if (!query) return;

    const resultBox = document.getElementById('addFriendSearchResult');
    resultBox.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-muted);font-size:12px;">Mencari pengguna...</div>';

    fetch(`/friends/search?q=${encodeURIComponent(query)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status !== 'success') {
            resultBox.innerHTML = `<div style="text-align:center;padding:20px;color:#EF4444;font-size:12px;">${res.message || 'Gagal mencari'}</div>`;
            return;
        }

        const users = res.users || [];
        if (users.length === 0) {
            resultBox.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-muted);font-size:12px;">Pengguna dengan username tersebut tidak ditemukan.</div>';
            return;
        }

        let html = '';
        users.forEach(u => {
            const init = (u.name || u.username || 'U').charAt(0).toUpperCase();
            let actionBtn = '';

            if (u.friend_status === 'friends') {
                actionBtn = `<button type="button" class="btn-chat-now" onclick="startChatWithFriend(${u.id}, '${escapeHtml(u.name)}', '${escapeHtml(u.username)}')">💬 Chat</button>`;
            } else if (u.friend_status === 'pending_sent') {
                actionBtn = `<span style="font-size:11px;font-weight:700;color:#F59E0B;background:#FEF3C7;padding:4px 8px;border-radius:8px;">Menunggu</span>`;
            } else if (u.friend_status === 'pending_received') {
                actionBtn = `<button type="button" class="btn-req-accept" onclick="respondFriendRequest(${u.request_id}, 'accept', true)">Terima</button>`;
            } else {
                actionBtn = `<button type="button" class="btn-req-accept" style="background:#2563EB;" onclick="sendFriendRequestTo('${escapeHtml(u.username)}')">➕ Tambah</button>`;
            }

            html += `
                <div class="search-user-card">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="conv-avatar sm"><span>${init}</span></div>
                        <div>
                            <div style="font-weight:700;font-size:13px;color:var(--text-primary);">${escapeHtml(u.name)}</div>
                            <div style="font-size:11px;color:var(--text-muted);">@${escapeHtml(u.username)}</div>
                        </div>
                    </div>
                    <div>${actionBtn}</div>
                </div>
            `;
        });
        resultBox.innerHTML = html;
    })
    .catch(err => {
        resultBox.innerHTML = '<div style="text-align:center;padding:20px;color:#EF4444;font-size:12px;">Terjadi kesalahan saat mencari pengguna.</div>';
    });
}

function sendFriendRequestTo(username) {
    const formData = new FormData();
    formData.append('username', username);
    formData.append(csrfTokenName, getCsrfToken());

    fetch('<?= base_url('friends/request') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.status === 'success') {
            closeAddFriendModal();
            location.reload();
        }
    })
    .catch(err => {
        console.error('Add friend error:', err);
        alert('Gagal mengirim permintaan pertemanan.');
    });
}

function respondFriendRequest(requestId, action, reloadAfter = false) {
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('action', action);
    formData.append(csrfTokenName, getCsrfToken());

    fetch('<?= base_url('friends/respond') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            const item = document.getElementById(`friendReqItem-${requestId}`);
            if (item) {
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                    const container = document.getElementById('friendRequestsBox');
                    if (container && container.querySelectorAll('.friend-req-item').length === 0) {
                        container.remove();
                    }
                }, 250);
            }
            if (reloadAfter) {
                location.reload();
            }
        } else {
            alert(res.message || 'Gagal memproses permintaan.');
        }
    })
    .catch(err => {
        console.error('Respond friend request error:', err);
        alert('Terjadi kesalahan jaringan.');
    });
}

function formatDateBadge(dateStr) {
    const today = new Date().toISOString().slice(0, 10);
    const yest = new Date(Date.now() - 86400000).toISOString().slice(0, 10);
    if (dateStr === today) return 'HARI INI';
    if (dateStr === yest) return 'KEMARIN';
    return dateStr;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[m];
    });
}

// Check URL query for direct user chat auto-opening (e.g. /chat?direct_user=123)
document.addEventListener('DOMContentLoaded', () => {
    filterConversations('all');
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('direct_user')) {
        const dUid = urlParams.get('direct_user');
        // Fetch friend details and auto-open
        fetch(`/chat/direct/messages?friend_id=${dUid}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.friend) {
                openDirectChat(res.friend.id, res.friend.name, res.friend.username, res.friend.avatar);
            }
        });
    }
});

// Auto-poll total unread count on PWA bottom nav badge
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
