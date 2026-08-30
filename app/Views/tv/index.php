<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<style>
    .tv-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px 16px 120px;
        position: relative;
    }
    .tv-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .tv-header h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .tv-badge-live {
        background: #EF4444;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 6px;
        animation: pulse 1.5s infinite;
        letter-spacing: 0.5px;
    }
    
    /* ── Player Wrapper & PiP Mode ─────────────────────────────────── */
    .tv-player-anchor {
        position: relative;
        margin-bottom: 24px;
    }
    .tv-player-wrap {
        background: #000;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, opacity 0.2s ease;
    }
    .tv-video-box {
        position: relative;
        padding-top: 56.25%; /* 16:9 Aspect Ratio */
        background: #0B0F19;
    }
    .tv-video-box video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    .tv-player-info {
        padding: 14px 18px;
        background: #111827;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid rgba(255,255,255,0.08);
    }
    
    /* ── Floating Mini PiP on Scroll ──────────────────────────────── */
    .tv-player-wrap.is-pip {
        position: fixed;
        bottom: 80px;
        right: 20px;
        width: 320px;
        max-width: calc(100vw - 32px);
        z-index: 990;
        border-radius: 16px;
        box-shadow: 0 18px 45px rgba(0,0,0,0.55), 0 0 0 2px var(--primary);
        animation: pipSlideIn 0.25s ease forwards;
    }
    @keyframes pipSlideIn {
        from { opacity: 0; transform: translateY(30px) scale(0.92); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .tv-pip-bar {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        padding: 6px 10px;
        background: linear-gradient(180deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);
        z-index: 10;
        justify-content: space-between;
        align-items: center;
    }
    .tv-player-wrap.is-pip .tv-pip-bar {
        display: flex;
    }
    .tv-player-wrap.is-pip .tv-player-info {
        padding: 8px 12px;
    }
    .tv-player-wrap.is-pip .tv-player-info h4 {
        font-size: 13px !important;
    }
    .tv-player-wrap.is-pip .tv-player-info small {
        display: none;
    }
    .tv-player-wrap.is-pip .btn-reload {
        display: none;
    }
    .tv-pip-btn {
        background: rgba(0,0,0,0.6);
        border: 1px solid rgba(255,255,255,0.25);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.15s ease;
    }
    .tv-pip-btn:hover {
        background: var(--primary);
        border-color: var(--primary);
    }
    
    .tv-player-placeholder {
        display: none;
        padding-top: 56.25%;
        margin-bottom: 24px;
    }
    .tv-player-wrap.is-pip + .tv-player-placeholder {
        display: block;
    }

    /* ── Category Filter Tabs ─────────────────────────────────────── */
    .tv-cat-tabs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 12px;
        margin-bottom: 18px;
        scrollbar-width: none;
    }
    .tv-cat-tabs::-webkit-scrollbar { display: none; }
    .tv-cat-tab {
        padding: 8px 16px;
        border-radius: 20px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        color: var(--text-primary);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.15s ease;
    }
    .tv-cat-tab:hover {
        border-color: var(--primary);
    }
    .tv-cat-tab.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }

    /* ── Channel Cards Grid ───────────────────────────────────────── */
    .tv-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
    }
    .tv-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        cursor: pointer;
        transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        text-decoration: none;
        color: var(--text-primary);
        position: relative;
    }
    .tv-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    .tv-card.playing {
        border-color: #EF4444;
        background: rgba(239, 68, 68, 0.04);
    }
    .tv-logo-box {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        background: var(--bg);
        border: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        padding: 6px;
        box-sizing: border-box;
    }
    .tv-logo-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .tv-card-name {
        font-size: 13.5px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .tv-card-cat {
        font-size: 11px;
        color: var(--text-secondary);
    }
    .tv-card-live-dot {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10B981;
    }

    /* ── Floating Live Chat Button (TV Only) ──────────────────────── */
    .tv-floating-chat-btn {
        position: fixed;
        bottom: 85px;
        left: 20px;
        z-index: 950;
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: #fff;
        border: none;
        border-radius: 30px;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4), 0 2px 6px rgba(0,0,0,0.15);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .tv-floating-chat-btn:hover {
        transform: scale(1.04);
        box-shadow: 0 10px 28px rgba(16, 185, 129, 0.5);
    }
    .tv-floating-chat-btn:active {
        transform: scale(0.96);
    }
    .tv-chat-pulse {
        width: 8px;
        height: 8px;
        background: #fff;
        border-radius: 50%;
        animation: pulse 1.2s infinite;
    }

    /* ── Live TV Chat Drawer / Modal ──────────────────────────────── */
    .tv-chat-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.45);
        backdrop-filter: blur(2px);
        z-index: 998;
        opacity: 0;
        visibility: hidden;
        transition: all 0.22s ease;
    }
    .tv-chat-backdrop.open {
        opacity: 1;
        visibility: visible;
    }
    .tv-chat-drawer {
        position: fixed;
        bottom: 0;
        right: 0;
        width: 380px;
        max-width: 100vw;
        height: 520px;
        max-height: 85vh;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px 20px 0 0;
        z-index: 999;
        display: flex;
        flex-direction: column;
        box-shadow: 0 -10px 40px rgba(0,0,0,0.3);
        transform: translateY(105%);
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @media (min-width: 768px) {
        .tv-chat-drawer {
            bottom: 24px;
            right: 24px;
            height: 560px;
            border-radius: 20px;
            transform: translateY(30px) scale(0.95);
            opacity: 0;
            visibility: hidden;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .tv-chat-drawer.open {
            transform: translateY(0) scale(1);
            opacity: 1;
            visibility: visible;
        }
    }
    .tv-chat-drawer.open {
        transform: translateY(0);
    }
    .tv-chat-header {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg);
        border-radius: 20px 20px 0 0;
    }
    .tv-chat-header-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 800;
    }
    .tv-chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .tv-chat-msg {
        display: flex;
        flex-direction: column;
        background: var(--bg);
        border: 1px solid var(--border-light);
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 12.5px;
        max-width: 90%;
        word-break: break-word;
    }
    .tv-chat-msg.mine {
        align-self: flex-end;
        background: rgba(16, 185, 129, 0.12);
        border-color: rgba(16, 185, 129, 0.3);
    }
    .tv-chat-msg-user {
        font-weight: 800;
        font-size: 11px;
        color: var(--primary);
        margin-bottom: 2px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .tv-chat-msg-time {
        font-size: 9.5px;
        color: var(--text-muted);
        font-weight: 400;
    }
    .tv-chat-emojis {
        display: flex;
        gap: 6px;
        padding: 6px 12px;
        overflow-x: auto;
        border-top: 1px solid var(--border-light);
        background: var(--bg);
        scrollbar-width: none;
    }
    .tv-chat-emojis::-webkit-scrollbar { display: none; }
    .tv-emoji-btn {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 3px 7px;
        font-size: 14px;
        cursor: pointer;
        transition: transform 0.1s ease;
    }
    .tv-emoji-btn:active { transform: scale(1.2); }
    .tv-chat-input-wrap {
        padding: 10px 14px;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 8px;
        background: var(--bg-card);
        border-radius: 0 0 20px 20px;
    }
    .tv-chat-input {
        flex: 1;
        border: 1px solid var(--border);
        background: var(--bg);
        border-radius: 12px;
        padding: 8px 12px;
        font-size: 13px;
        color: var(--text-primary);
        outline: none;
    }
    .tv-chat-input:focus {
        border-color: var(--primary);
    }
    .tv-chat-send-btn {
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 0 14px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.4; }
        100% { opacity: 1; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="tv-container">
    <div class="tv-header">
        <div>
            <h1>📺 TV &amp; Live Streaming <span class="tv-badge-live">LIVE</span></h1>
            <p style="margin: 4px 0 0 0; color: var(--text-secondary); font-size: 13px;">
                Tonton siaran TV nasional, berita, olahraga &amp; hiburan favorit secara langsung di DuitKu.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <?php if (in_array(strtolower((string)session()->get('user_role')), ['administrator', 'admin'])): ?>
                <a href="/admin/tv" class="btn btn-outline" style="font-size: 12.5px;">⚙️ Kelola Saluran (Admin)</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Player with Auto PiP capability -->
    <?php if ($currentChannel): ?>
        <div class="tv-player-anchor" id="tvPlayerAnchor">
            <div class="tv-player-wrap" id="tvPlayerWrap">
                <!-- Mini PiP Controls Bar -->
                <div class="tv-pip-bar">
                    <span style="color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;gap:4px">
                        <span class="tv-badge-live" style="padding:1px 5px;font-size:9px">LIVE</span>
                        <?= esc($currentChannel['name']) ?>
                    </span>
                    <div style="display:flex;gap:6px">
                        <button type="button" class="tv-pip-btn" onclick="restoreFromPip()" title="Perbesar / Kembalikan">
                            🗖 Kembalikan
                        </button>
                        <button type="button" class="tv-pip-btn" onclick="closePip()" title="Tutup Mini Player">
                            ✕
                        </button>
                    </div>
                </div>

                <div class="tv-video-box">
                    <video id="tvPlayer" controls autoplay playsinline></video>
                </div>
                <div class="tv-player-info">
                    <div style="display: flex; align-items: center; gap: 12px; min-width:0;">
                        <?php if (!empty($currentChannel['logo_url'])): ?>
                            <img src="<?= esc($currentChannel['logo_url']) ?>" alt="Logo" style="width: 36px; height: 36px; object-fit: contain; border-radius: 8px; background: #fff; padding: 2px; flex-shrink:0;">
                        <?php endif; ?>
                        <div style="min-width:0;">
                            <h4 style="margin:0;font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <?= esc($currentChannel['name']) ?>
                                <span class="tv-badge-live">LIVE</span>
                            </h4>
                            <small style="font-size: 12px; color: #9CA3AF; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;">
                                <?= esc($currentChannel['category'] ?? 'Nasional') ?> <?= !empty($currentChannel['description']) ? '• ' . esc($currentChannel['description']) : '' ?>
                            </small>
                        </div>
                    </div>
                    <div>
                        <button onclick="reloadStream()" class="btn btn-outline btn-reload" style="color: #fff; border-color: rgba(255,255,255,0.2); padding: 6px 12px; font-size: 12px; white-space:nowrap;">
                            🔄 Muat Ulang
                        </button>
                    </div>
                </div>
            </div>
            <div class="tv-player-placeholder" id="tvPlayerPlaceholder"></div>
        </div>
    <?php endif; ?>

    <!-- Category Filter Tabs -->
    <div class="tv-cat-tabs">
        <a href="/tv" class="tv-cat-tab <?= empty($selectedCat) || $selectedCat === 'Semua' ? 'active' : '' ?>">
            Semua Saluran (<?= count($channels) ?>)
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="/tv?category=<?= urlencode($cat) ?>" class="tv-cat-tab <?= $selectedCat === $cat ? 'active' : '' ?>">
                <?= esc($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Channels Grid -->
    <?php if (empty($channels)): ?>
        <div class="empty-state" style="padding: 60px 20px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 12px;">📺</div>
            <h3 style="margin: 0 0 6px 0;">Belum Ada Channel TV</h3>
            <p style="color: var(--text-secondary); font-size: 13px;">Belum ada saluran streaming TV yang aktif pada kategori ini.</p>
        </div>
    <?php else: ?>
        <div class="tv-grid">
            <?php foreach ($channels as $ch): ?>
                <?php $isPlaying = $currentChannel && (int)$currentChannel['id'] === (int)$ch['id']; ?>
                <div class="tv-card <?= $isPlaying ? 'playing' : '' ?>" onclick="switchChannel(<?= (int)$ch['id'] ?>)">
                    <div class="tv-card-live-dot"></div>
                    <div class="tv-logo-box">
                        <?php if (!empty($ch['logo_url'])): ?>
                            <img src="<?= esc($ch['logo_url']) ?>" alt="<?= esc($ch['name']) ?>">
                        <?php else: ?>
                            <span style="font-size: 24px;">📺</span>
                        <?php endif; ?>
                    </div>
                    <div class="tv-card-name"><?= esc($ch['name']) ?></div>
                    <div class="tv-card-cat"><?= esc($ch['category'] ?? 'Nasional') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- ── Floating Live Chat Button (Exclusive to TV) ───────────── -->
    <button type="button" class="tv-floating-chat-btn" id="btnOpenTvChat" onclick="toggleTvChat()">
        <span class="tv-chat-pulse"></span>
        <span>💬 Obrolan TV</span>
    </button>

    <!-- ── Live TV Chat Drawer / Modal ────────────────────────────── -->
    <div class="tv-chat-backdrop" id="tvChatBackdrop" onclick="closeTvChat()"></div>
    <div class="tv-chat-drawer" id="tvChatDrawer">
        <div class="tv-chat-header">
            <div class="tv-chat-header-title">
                <span>💬</span>
                <span>Obrolan Siaran TV Live</span>
                <span class="tv-badge-live" style="font-size:9px;padding:2px 6px">ROOM AKTIF</span>
            </div>
            <button type="button" onclick="closeTvChat()" style="background:none;border:none;color:var(--text-secondary);font-size:16px;cursor:pointer;padding:4px 8px">✕</button>
        </div>

        <div class="tv-chat-messages" id="tvChatMessages">
            <div style="text-align:center;color:var(--text-muted);font-size:12px;padding:20px;">
                Memuat obrolan siaran...
            </div>
        </div>

        <!-- Emoji Quick Reactions -->
        <div class="tv-chat-emojis">
            <button type="button" class="tv-emoji-btn" onclick="sendQuickEmoji('🔥')">🔥</button>
            <button type="button" class="tv-emoji-btn" onclick="sendQuickEmoji('👏')">👏</button>
            <button type="button" class="tv-emoji-btn" onclick="sendQuickEmoji('⚽')">⚽</button>
            <button type="button" class="tv-emoji-btn" onclick="sendQuickEmoji('🤣')">🤣</button>
            <button type="button" class="tv-emoji-btn" onclick="sendQuickEmoji('👍')">👍</button>
            <button type="button" class="tv-emoji-btn" onclick="sendQuickEmoji('❤️')">❤️</button>
            <button type="button" class="tv-emoji-btn" onclick="sendQuickEmoji('🎉')">🎉</button>
            <button type="button" class="tv-emoji-btn" onclick="sendQuickEmoji('⭐')">⭐</button>
        </div>

        <form id="tvChatForm" class="tv-chat-input-wrap" onsubmit="handleSendChat(event)">
            <input type="text" id="tvChatInput" class="tv-chat-input" placeholder="Tulis komentar siaran TV..." autocomplete="off" maxlength="300">
            <button type="submit" class="tv-chat-send-btn">➤</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let currentHls = null;
    let isPipDismissed = false;
    let lastChatId = 0;
    let chatPollInterval = null;
    const currentUserId = <?= (int)(session()->get('user_id') ?? 0) ?>;
    const currentUserName = '<?= esc(addslashes(session()->get('user_name') ?? 'Pengguna')) ?>';
    const currentStreamUrl = '<?= $currentChannel ? esc(addslashes($currentChannel['stream_url'])) : '' ?>';

    /* ── Player Initialization ────────────────────────────────────── */
    function initPlayer(streamUrl) {
        if (!streamUrl) return;
        const video = document.getElementById('tvPlayer');
        if (!video) return;

        if (currentHls) {
            currentHls.destroy();
            currentHls = null;
        }

        if (Hls.isSupported() && (streamUrl.includes('.m3u8') || streamUrl.includes('.m3u') || !streamUrl.endsWith('.mp4'))) {
            currentHls = new Hls({
                enableWorker: true,
                lowLatencyMode: true,
                backBufferLength: 30
            });
            currentHls.loadSource(streamUrl);
            currentHls.attachMedia(video);
            currentHls.on(Hls.Events.MANIFEST_PARSED, function() {
                video.play().catch(e => console.log('Autoplay gesture waiting'));
            });
            currentHls.on(Hls.Events.ERROR, function(event, data) {
                if (data.fatal) {
                    switch(data.type) {
                        case Hls.ErrorTypes.NETWORK_ERROR:
                            currentHls.startLoad();
                            break;
                        case Hls.ErrorTypes.MEDIA_ERROR:
                            currentHls.recoverMediaError();
                            break;
                        default:
                            currentHls.destroy();
                            break;
                    }
                }
            });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = streamUrl;
            video.play().catch(e => console.log('Autoplay error', e));
        } else {
            video.src = streamUrl;
            video.play().catch(e => console.log('Autoplay error', e));
        }
    }

    function switchChannel(channelId) {
        window.location.href = '/tv?play=' + channelId + (window.location.search.includes('category=') ? '&' + window.location.search.substring(1).split('&').filter(p => p.startsWith('category=')).join('&') : '');
    }

    function reloadStream() {
        if (currentStreamUrl) {
            initPlayer(currentStreamUrl);
        }
    }

    /* ── Auto PiP on Scroll Logic ──────────────────────────────────── */
    function handleScrollPiP() {
        const anchor = document.getElementById('tvPlayerAnchor');
        const playerWrap = document.getElementById('tvPlayerWrap');
        if (!anchor || !playerWrap || isPipDismissed) return;

        const rect = anchor.getBoundingClientRect();
        // If bottom of player scrolled past top of screen
        const isOutOfView = rect.bottom < 80;

        if (isOutOfView && !playerWrap.classList.contains('is-pip')) {
            playerWrap.classList.add('is-pip');
        } else if (!isOutOfView && playerWrap.classList.contains('is-pip')) {
            playerWrap.classList.remove('is-pip');
        }
    }

    function restoreFromPip() {
        isPipDismissed = false;
        const playerWrap = document.getElementById('tvPlayerWrap');
        if (playerWrap) playerWrap.classList.remove('is-pip');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function closePip() {
        isPipDismissed = true;
        const playerWrap = document.getElementById('tvPlayerWrap');
        if (playerWrap) playerWrap.classList.remove('is-pip');
    }

    /* ── Floating Live Chat & Polling ──────────────────────────────── */
    function toggleTvChat() {
        const drawer = document.getElementById('tvChatDrawer');
        const backdrop = document.getElementById('tvChatBackdrop');
        if (!drawer) return;

        const isOpen = drawer.classList.contains('open');
        if (isOpen) {
            closeTvChat();
        } else {
            drawer.classList.add('open');
            if (backdrop) backdrop.classList.add('open');
            loadChats();
            startChatPolling();
            setTimeout(() => {
                document.getElementById('tvChatInput')?.focus();
            }, 250);
        }
    }

    function closeTvChat() {
        document.getElementById('tvChatDrawer')?.classList.remove('open');
        document.getElementById('tvChatBackdrop')?.classList.remove('open');
        stopChatPolling();
    }

    async function loadChats() {
        try {
            const res = await fetch('/tv/chats?after_id=' + lastChatId);
            if (res.ok) {
                const json = await res.json();
                const messages = json.data || [];
                if (messages.length > 0) {
                    renderMessages(messages);
                    lastChatId = messages[messages.length - 1].id;
                } else if (lastChatId === 0) {
                    const container = document.getElementById('tvChatMessages');
                    if (container && container.children.length === 0) {
                        container.innerHTML = '<div style="text-align:center;color:var(--text-muted);font-size:12px;padding:20px;">Belum ada obrolan. Jadilah yang pertama mengirim pesan! 🎉</div>';
                    }
                }
            }
        } catch (e) {
            console.log('Chat fetch error', e);
        }
    }

    function renderMessages(messages) {
        const container = document.getElementById('tvChatMessages');
        if (!container) return;

        if (lastChatId === 0) {
            container.innerHTML = '';
        }

        messages.forEach(msg => {
            const isMine = parseInt(msg.user_id, 10) === currentUserId;
            const timeStr = msg.created_at ? msg.created_at.substring(11, 16) : '';
            
            const div = document.createElement('div');
            div.className = 'tv-chat-msg' + (isMine ? ' mine' : '');
            div.innerHTML = `
                <div class="tv-chat-msg-user">
                    <span>${isMine ? 'Anda' : escapeHtml(msg.user_name)}</span>
                    <span class="tv-chat-msg-time">${timeStr}</span>
                </div>
                <div>${escapeHtml(msg.message)}</div>
            `;
            container.appendChild(div);
        });

        container.scrollTop = container.scrollHeight;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    async function handleSendChat(e) {
        e.preventDefault();
        const input = document.getElementById('tvChatInput');
        if (!input) return;
        const text = input.value.trim();
        if (!text) return;

        input.value = '';

        // Optimistic append
        renderMessages([{
            id: lastChatId + 1,
            user_id: currentUserId,
            user_name: currentUserName,
            message: text,
            created_at: new Date().toISOString()
        }]);

        try {
            await fetch('/tv/chats', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: text })
            });
            loadChats();
        } catch (err) {
            console.log('Failed to send chat', err);
        }
    }

    function sendQuickEmoji(emoji) {
        const input = document.getElementById('tvChatInput');
        if (input) {
            input.value = (input.value + ' ' + emoji).trim();
            document.getElementById('tvChatForm')?.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    }

    function startChatPolling() {
        stopChatPolling();
        chatPollInterval = setInterval(loadChats, 3000);
    }

    function stopChatPolling() {
        if (chatPollInterval) {
            clearInterval(chatPollInterval);
            chatPollInterval = null;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (currentStreamUrl) {
            initPlayer(currentStreamUrl);
        }

        // Attach scroll listener for PiP mode
        window.addEventListener('scroll', handleScrollPiP, { passive: true });
    });

    window.addEventListener('beforeunload', function() {
        stopChatPolling();
    });
</script>
<?= $this->endSection() ?>
