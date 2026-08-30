<?= $this->extend('admin/layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Player Tester Box (Live Stream Preview) -->
<div id="playerBox" class="admin-card" style="display: none; background: #000; border-color: #333; color: #fff; margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-block; width: 10px; height: 10px; background: #EF4444; border-radius: 50%; animation: pulse 1.5s infinite;"></span>
            <strong id="playerChannelName" style="font-size: 14.5px;">Live Stream Preview</strong>
        </div>
        <button type="button" onclick="closePlayer()" class="admin-btn admin-btn-outline admin-btn-sm" style="color: #fff; border-color: #555;">✕ Tutup Preview</button>
    </div>
    <div style="position: relative; padding-top: 45%; background: #111; border-radius: 10px; overflow: hidden;">
        <video id="liveVideo" controls autoplay playsinline style="position: absolute; top:0; left:0; width:100%; height:100%;"></video>
    </div>
</div>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 20px; align-items: start;">
    <!-- Form Tambah Channel & Import M3U -->
    <div>
        <!-- Single Channel Form -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">📺 Tambah Channel TV Baru</h3>
            </div>

            <form action="/admin/tv/store" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="admin-form-group">
                    <label class="admin-form-label">Nama Channel / Siaran TV *</label>
                    <input type="text" name="name" class="admin-input" placeholder="Contoh: TVRI, Kompas TV, CNBC Indonesia" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Kategori Saluran</label>
                    <input type="text" name="category" class="admin-input" list="catList" placeholder="Nasional / Berita / Olahraga / Religi / Hiburan" value="Nasional">
                    <datalist id="catList">
                        <option value="Nasional">
                        <option value="Berita">
                        <option value="Hiburan">
                        <option value="Olahraga">
                        <option value="Religi">
                        <option value="Edukasi & Anak">
                        <option value="Internasional">
                        <option value="Musik">
                        <option value="Lokal">
                    </datalist>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Alamat Live Streaming (M3U / M3U8 URL) *</label>
                    <input type="url" name="stream_url" class="admin-input" placeholder="https://.../live.m3u8 atau .m3u stream URL" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">URL Logo / Icon TV (Opsional)</label>
                    <input type="url" name="logo_url" class="admin-input" placeholder="https://.../logo.png">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Atau Upload Logo File (PNG / JPG / SVG)</label>
                    <input type="file" name="logo_file" accept="image/*" class="admin-input" style="padding: 6px;">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Deskripsi Singkat</label>
                    <textarea name="description" rows="2" class="admin-textarea" placeholder="Siaran TV nasional berkualitas tinggi..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Urutan Sort</label>
                        <input type="number" name="sort_order" class="admin-input" value="0">
                    </div>
                    <div class="admin-form-group" style="display: flex; align-items: center; gap: 8px; margin-top: 24px;">
                        <input type="checkbox" name="is_active" id="chIsActiveNew" value="1" checked style="width: 16px; height: 16px;">
                        <label for="chIsActiveNew" style="font-size: 12.5px; font-weight: 700; cursor: pointer;">Status Aktif</label>
                    </div>
                </div>

                <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; margin-top: 8px;">
                    💾 Simpan Channel TV
                </button>
            </form>
        </div>

        <!-- M3U Playlist Batch Importer -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">⚡ Import Playlist M3U Batch</h3>
            </div>
            <p style="font-size: 11.5px; color: var(--admin-text-secondary); margin-top: 0;">
                Impor banyak channel sekaligus dari file <code>.m3u</code> atau teks format <code>#EXTINF</code>.
            </p>

            <form action="/admin/tv/import-m3u" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="admin-form-group">
                    <label class="admin-form-label">Upload File .m3u / .m3u8</label>
                    <input type="file" name="m3u_file" accept=".m3u,.m3u8,.txt" class="admin-input" style="padding: 6px;">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Atau Tempel Teks M3U Playlist</label>
                    <textarea name="m3u_text" rows="3" class="admin-textarea" placeholder="#EXTINF:-1 tvg-logo=&quot;http://...&quot; group-title=&quot;Berita&quot;,Kompas TV&#10;https://.../live.m3u8"></textarea>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Default Kategori</label>
                    <input type="text" name="default_category" class="admin-input" value="Nasional">
                </div>

                <button type="submit" class="admin-btn admin-btn-outline" style="width: 100%; font-weight: 700;">
                    📥 Proses Batch Import M3U
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Channel TV Table -->
    <div class="admin-card">
        <div class="admin-card-header" style="flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 class="admin-card-title">📺 Daftar Siaran TV (<span id="tvTotalCount"><?= count($channels) ?></span>)</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <input type="text" id="tvSearchInput" onkeyup="filterTvTable()" placeholder="🔍 Cari channel..." class="admin-input" style="width: 180px; padding: 6px 10px; font-size: 12px;">
                <select id="tvCatFilter" onchange="filterTvTable()" class="admin-select" style="width: 130px; padding: 6px 10px; font-size: 12px;">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="/tv" target="_blank" class="admin-btn admin-btn-primary admin-btn-sm" title="Buka Player Fullscreen">
                    ▶️ Player ↗
                </a>
            </div>
        </div>

        <?php if (empty($channels)): ?>
            <div style="text-align: center; padding: 40px; color: var(--admin-text-secondary);">
                Belum ada channel TV streaming yang didaftarkan. Silakan tambahkan atau import dari M3U.
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table" id="tvTable">
                    <thead>
                        <tr>
                            <th style="width: 44px; text-align: center;">Logo</th>
                            <th>Nama Channel</th>
                            <th>Kategori</th>
                            <th>Stream URL (M3U8)</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right; width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($channels as $c): ?>
                            <tr data-name="<?= esc(strtolower($c['name'])) ?>" data-cat="<?= esc($c['category'] ?? 'Nasional') ?>">
                                <td style="text-align: center;">
                                    <?php if (!empty($c['logo_url'])): ?>
                                        <img src="<?= esc($c['logo_url']) ?>" alt="Logo" style="width: 32px; height: 32px; object-fit: contain; border-radius: 6px; background: var(--admin-bg); padding: 2px; border: 1px solid var(--admin-border); margin: 0 auto;">
                                    <?php else: ?>
                                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #0284C7; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; margin: 0 auto;">
                                            📺
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="font-size: 13px; color: var(--admin-text);"><?= esc($c['name']) ?></strong>
                                    <?php if (!empty($c['description'])): ?>
                                        <div style="font-size: 11px; color: var(--admin-text-secondary);"><?= esc(mb_strimwidth($c['description'], 0, 35, '...')) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-admin info"><?= esc($c['category'] ?? 'Nasional') ?></span>
                                </td>
                                <td>
                                    <div style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: monospace; font-size: 11px; color: var(--admin-text-secondary);" title="<?= esc($c['stream_url']) ?>">
                                        <?= esc($c['stream_url']) ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <form action="/admin/tv/toggle/<?= $c['id'] ?>" method="post" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="badge-admin <?= $c['is_active'] ? 'success' : 'danger' ?>" style="cursor: pointer; border: none;" title="Klik untuk ubah status">
                                            <?= $c['is_active'] ? '● AKTIF' : '○ NON-AKTIF' ?>
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; align-items: center; gap: 4px; justify-content: flex-end;">
                                        <button type="button" onclick="playStream('<?= esc(addslashes($c['name'])) ?>', '<?= esc(addslashes($c['stream_url'])) ?>')" class="admin-btn admin-btn-outline admin-btn-sm" style="padding: 4px 8px;" title="Test Stream">
                                            ▶️
                                        </button>
                                        <button type="button" onclick='openEditModal(<?= json_encode($c) ?>)' class="admin-btn admin-btn-primary admin-btn-sm" style="padding: 4px 10px;" title="Edit Channel">
                                            ✏️ Edit
                                        </button>
                                        <form action="/admin/tv/delete/<?= $c['id'] ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus channel <?= esc(addslashes($c['name'])) ?>?')" style="display: inline; margin: 0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" style="padding: 4px 8px;" title="Hapus Channel">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- POPUP MODAL: EDIT CHANNEL TV STREAMING                           -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div id="editTvModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
    <div class="admin-card" style="width: 100%; max-width: 480px; margin: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.3); animation: fadeIn 0.2s ease;">
        <div class="admin-card-header">
            <h3 class="admin-card-title" id="editModalTitle">✏️ Edit Channel TV</h3>
            <button type="button" onclick="closeEditModal()" class="admin-btn admin-btn-outline admin-btn-sm" style="padding: 4px 8px;">✕</button>
        </div>

        <form id="editTvForm" action="" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="admin-form-group">
                <label class="admin-form-label">Nama Channel / Siaran TV *</label>
                <input type="text" name="name" id="editName" class="admin-input" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Kategori Saluran</label>
                <input type="text" name="category" id="editCategory" class="admin-input" list="catList">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Alamat Live Streaming (M3U / M3U8 URL) *</label>
                <input type="url" name="stream_url" id="editStreamUrl" class="admin-input" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">URL Logo / Icon TV (Opsional)</label>
                <input type="url" name="logo_url" id="editLogoUrl" class="admin-input">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Ganti Logo File (PNG / JPG / SVG)</label>
                <input type="file" name="logo_file" accept="image/*" class="admin-input" style="padding: 6px;">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Deskripsi Singkat</label>
                <textarea name="description" id="editDescription" rows="2" class="admin-textarea"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Urutan Sort</label>
                    <input type="number" name="sort_order" id="editSortOrder" class="admin-input" value="0">
                </div>
                <div class="admin-form-group" style="display: flex; align-items: center; gap: 8px; margin-top: 24px;">
                    <input type="checkbox" name="is_active" id="editIsActive" value="1" style="width: 16px; height: 16px;">
                    <label for="editIsActive" style="font-size: 12.5px; font-weight: 700; cursor: pointer;">Status Aktif</label>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 18px;">
                <button type="button" onclick="closeEditModal()" class="admin-btn admin-btn-outline" style="flex: 1;">
                    Batal
                </button>
                <button type="submit" class="admin-btn admin-btn-primary" style="flex: 2;">
                    💾 Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let hlsInstance = null;

    function playStream(name, url) {
        document.getElementById('playerBox').style.display = 'block';
        document.getElementById('playerChannelName').innerText = 'Live Stream: ' + name;
        const video = document.getElementById('liveVideo');

        if (hlsInstance) {
            hlsInstance.destroy();
            hlsInstance = null;
        }

        if (Hls.isSupported() && (url.includes('.m3u8') || url.includes('.m3u'))) {
            hlsInstance = new Hls({ enableWorker: true, lowLatencyMode: true });
            hlsInstance.loadSource(url);
            hlsInstance.attachMedia(video);
            hlsInstance.on(Hls.Events.MANIFEST_PARSED, function() {
                video.play().catch(e => console.log('Autoplay blocked', e));
            });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = url;
            video.play().catch(e => console.log('Autoplay blocked', e));
        } else {
            video.src = url;
            video.play().catch(e => console.log('Autoplay blocked', e));
        }

        document.getElementById('playerBox').scrollIntoView({ behavior: 'smooth' });
    }

    function closePlayer() {
        const video = document.getElementById('liveVideo');
        video.pause();
        if (hlsInstance) {
            hlsInstance.destroy();
            hlsInstance = null;
        }
        document.getElementById('playerBox').style.display = 'none';
    }

    // ── Edit Modal Functions ─────────────────────────────────────────
    function openEditModal(ch) {
        document.getElementById('editModalTitle').innerText = '✏️ Edit Channel: ' + ch.name;
        document.getElementById('editTvForm').action = '/admin/tv/update/' + ch.id;
        document.getElementById('editName').value = ch.name;
        document.getElementById('editCategory').value = ch.category || 'Nasional';
        document.getElementById('editStreamUrl').value = ch.stream_url;
        document.getElementById('editLogoUrl').value = ch.logo_url || '';
        document.getElementById('editDescription').value = ch.description || '';
        document.getElementById('editSortOrder').value = ch.sort_order || 0;
        document.getElementById('editIsActive').checked = ch.is_active == 1;

        document.getElementById('editTvModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editTvModal').style.display = 'none';
    }

    // ── Search & Filter Table ────────────────────────────────────────
    function filterTvTable() {
        const q = (document.getElementById('tvSearchInput')?.value || '').toLowerCase().trim();
        const cat = (document.getElementById('tvCatFilter')?.value || '').trim();
        const rows = document.querySelectorAll('#tvTable tbody tr');
        let visibleCount = 0;

        rows.forEach(r => {
            const name = r.dataset.name || '';
            const rCat = r.dataset.cat || '';
            const matchQuery = !q || name.includes(q);
            const matchCat = !cat || rCat === cat;

            if (matchQuery && matchCat) {
                r.style.display = '';
                visibleCount++;
            } else {
                r.style.display = 'none';
            }
        });

        const totalEl = document.getElementById('tvTotalCount');
        if (totalEl) totalEl.innerText = visibleCount;
    }
</script>
<?= $this->endSection() ?>

