<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.notif-page {
    max-width: 680px;
    margin: 0 auto;
    padding: 16px 16px 120px;
}

.notif-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.notif-card {
    background: var(--card);
    border-radius: 18px;
    border: 1px solid var(--border);
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
    display: flex;
    gap: 14px;
    align-items: flex-start;
    transition: transform 0.15s ease, border-color 0.15s ease;
    text-decoration: none;
    color: inherit;
}

.notif-card.unread {
    border-color: rgba(16, 185, 129, 0.4);
    background: rgba(16, 185, 129, 0.03);
}

.notif-card.pinned {
    border-color: rgba(245, 158, 11, 0.5);
    border-width: 1.5px;
}

.notif-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.type-info { background: #E0F2FE; color: #0284C7; }
.type-announcement { background: #DCFCE7; color: #16A34A; }
.type-promo { background: #F3E8FF; color: #9333EA; }
.type-warning { background: #FEE2E2; color: #DC2626; }
.type-system { background: #FEF3C7; color: #D97706; }

.notif-body {
    flex: 1;
}

.notif-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}

.notif-badge {
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}

.notif-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 4px;
}

.notif-msg {
    font-size: 12.5px;
    color: var(--text-secondary);
    line-height: 1.45;
    margin: 0 0 8px;
}

.notif-action-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
}
.notif-action-link:hover {
    text-decoration: underline;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="notif-page">
    
    <div class="notif-header">
        <div>
            <h2 style="margin: 0 0 2px; font-size: 20px; font-weight: 800; color: var(--text-primary);">📢 Pemberitahuan & Pesan</h2>
            <p style="margin: 0; font-size: 12px; color: var(--text-secondary);">Pusat informasi resmi, pengumuman & promo DuitKu.</p>
        </div>
        <?php if (!empty($notifications) && ($unreadCount ?? 0) > 0): ?>
            <a href="/notifications/read-all" class="zp-tab-btn" style="padding: 8px 12px; border: 1px solid var(--border); font-size: 11.5px; text-decoration: none; border-radius: 10px;">
                ✓ Tandai Semua Dibaca
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="zp-card" style="text-align: center; padding: 48px 20px;">
            <div style="font-size: 48px; margin-bottom: 12px;">📭</div>
            <h4 style="margin: 0 0 6px; font-weight: 800;">Belum Ada Pemberitahuan</h4>
            <p style="margin: 0; font-size: 12px; color: var(--text-secondary);">Notifikasi broadcast dan pengumuman sistem akan tampil di sini.</p>
        </div>
    <?php else: ?>
        <div>
            <?php foreach ($notifications as $n): ?>
                <?php
                    $typeClass = 'type-info';
                    $typeIcon = 'ℹ️';
                    $typeLabel = 'INFO';
                    switch ($n['type']) {
                        case 'announcement':
                            $typeClass = 'type-announcement';
                            $typeIcon = '📢';
                            $typeLabel = 'PENGUMUMAN';
                            break;
                        case 'promo':
                            $typeClass = 'type-promo';
                            $typeIcon = '🎁';
                            $typeLabel = 'PROMO';
                            break;
                        case 'warning':
                            $typeClass = 'type-warning';
                            $typeIcon = '⚠️';
                            $typeLabel = 'PERINGATAN';
                            break;
                        case 'system':
                            $typeClass = 'type-system';
                            $typeIcon = '⚙️';
                            $typeLabel = 'SISTEM';
                            break;
                    }
                    $isUnread = empty($n['is_read']);
                    $isPinned = !empty($n['is_pinned']);
                ?>
                <div class="notif-card <?= $isUnread ? 'unread' : '' ?> <?= $isPinned ? 'pinned' : '' ?>" onclick="markRead(<?= $n['id'] ?>)">
                    <div class="notif-icon-box <?= $typeClass ?>">
                        <?= $typeIcon ?>
                    </div>
                    <div class="notif-body">
                        <div class="notif-meta">
                            <span class="notif-badge <?= $typeClass ?>"><?= $typeLabel ?></span>
                            <?php if ($isPinned): ?>
                                <span class="notif-badge" style="background: #FEF3C7; color: #D97706;">📌 PINNED</span>
                            <?php endif; ?>
                            <?php if ($isUnread): ?>
                                <span class="notif-badge" style="background: #DCFCE7; color: #16A34A;">BARU</span>
                            <?php endif; ?>
                            <span style="font-size: 11px; color: var(--text-muted); margin-left: auto;">
                                <?= date('d M H:i', strtotime($n['created_at'])) ?>
                            </span>
                        </div>
                        <h4 class="notif-title"><?= esc($n['title']) ?></h4>
                        <p class="notif-msg"><?= nl2br(esc($n['message'])) ?></p>
                        <?php if (!empty($n['action_url'])): ?>
                            <a href="<?= esc($n['action_url']) ?>" class="notif-action-link" target="_blank">
                                Buka Tautan Terkait ↗
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
function markRead(id) {
    fetch('/notifications/read/' + id, { credentials: 'same-origin' });
}
</script>
<?= $this->endSection() ?>
