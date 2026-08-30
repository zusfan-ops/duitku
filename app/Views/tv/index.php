<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<style>
    .tv-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px 16px 100px;
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
    .tv-player-wrap {
        background: #000;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        margin-bottom: 24px;
        position: relative;
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
        padding: 16px 20px;
        background: #111827;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid rgba(255,255,255,0.08);
    }
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
            <h1>📺 TV & Live Streaming <span class="tv-badge-live">LIVE</span></h1>
            <p style="margin: 4px 0 0 0; color: var(--text-secondary); font-size: 13px;">
                Tonton siaran TV nasional, berita, olahraga & hiburan favorit secara langsung di DuitKu.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <?php if (in_array(strtolower((string)session()->get('user_role')), ['administrator', 'admin'])): ?>
                <a href="/admin/tv" class="btn btn-outline" style="font-size: 12.5px;">⚙️ Kelola Saluran (Admin)</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Player -->
    <?php if ($currentChannel): ?>
        <div class="tv-player-wrap">
            <div class="tv-video-box">
                <video id="tvPlayer" controls autoplay playsinline></video>
            </div>
            <div class="tv-player-info">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <?php if (!empty($currentChannel['logo_url'])): ?>
                        <img src="<?= esc($currentChannel['logo_url']) ?>" alt="Logo" style="width: 36px; height: 36px; object-fit: contain; border-radius: 8px; background: #fff; padding: 2px;">
                    <?php endif; ?>
                    <div>
                        <div style="font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                            <?= esc($currentChannel['name']) ?>
                            <span class="tv-badge-live">LIVE</span>
                        </div>
                        <div style="font-size: 12px; color: #9CA3AF;"><?= esc($currentChannel['category'] ?? 'Nasional') ?> <?= !empty($currentChannel['description']) ? '• ' . esc($currentChannel['description']) : '' ?></div>
                    </div>
                </div>
                <div>
                    <button onclick="reloadStream()" class="btn btn-outline" style="color: #fff; border-color: rgba(255,255,255,0.2); padding: 6px 12px; font-size: 12px;">
                        🔄 Muat Ulang
                    </button>
                </div>
            </div>
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
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let currentHls = null;
    const currentStreamUrl = '<?= $currentChannel ? esc(addslashes($currentChannel['stream_url'])) : '' ?>';

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
                video.play().catch(e => console.log('Autoplay waiting for user gesture'));
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

    document.addEventListener('DOMContentLoaded', function() {
        if (currentStreamUrl) {
            initPlayer(currentStreamUrl);
        }
    });
</script>
<?= $this->endSection() ?>
