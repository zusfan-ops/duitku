<?= $this->extend('admin/layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Player Tester Modal/Box (Live Preview) -->
<div id="playerBox" class="admin-card" style="display: none; background: #000; border-color: #333; color: #fff; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-block; width: 10px; height: 10px; background: #EF4444; border-radius: 50%; animation: pulse 1.5s infinite;"></span>
            <strong id="playerChannelName" style="font-size: 15px;">Live Stream Preview</strong>
        </div>
        <button onclick="closePlayer()" class="admin-btn admin-btn-outline admin-btn-sm" style="color: #fff; border-color: #555;">✕ Tutup Preview</button>
    </div>
    <div style="position: relative; padding-top: 45%; background: #111; border-radius: 10px; overflow: hidden;">
        <video id="liveVideo" controls autoplay playsinline style="position: absolute; top:0; left:0; width:100%; height:100%;"></video>
    </div>
</div>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">
    <!-- Form Tambah Channel & Import M3U -->
    <div>
        <!-- Single Channel Form -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title" id="formTitle">📺 Tambah Channel TV Baru</h3>
            </div>

            <form id="channelForm" action="/admin/tv/store" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="admin-form-group">
                    <label class="admin-form-label">Nama Channel / Siaran TV *</label>
                    <input type="text" name="name" id="chName" class="admin-input" placeholder="Contoh: TVRI, Kompas TV, CNBC Indonesia" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Kategori Saluran</label>
                    <input type="text" name="category" id="chCategory" class="admin-input" list="catList" placeholder="Nasional / Berita / Olahraga / Religi / Hiburan" value="Nasional">
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
                    <input type="url" name="stream_url" id="chStreamUrl" class="admin-input" placeholder="https://.../live.m3u8 atau .m3u stream URL" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">URL Logo / Icon TV (Opsional)</label>
                    <input type="url" name="logo_url" id="chLogoUrl" class="admin-input" placeholder="https://.../logo.png">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Atau Upload Logo File (PNG / JPG / SVG)</label>
                    <input type="file" name="logo_file" accept="image/*" class="admin-input" style="padding: 6px;">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Deskripsi Singkat</label>
                    <textarea name="description" id="chDescription" rows="2" class="admin-textarea" placeholder="Siaran TV nasional berkualitas tinggi..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Urutan Sort</label>
                        <input type="number" name="sort_order" id="chSortOrder" class="admin-input" value="0">
                    </div>
                    <div class="admin-form-group" style="display: flex; align-items: center; gap: 8px; margin-top: 24px;">
                        <input type="checkbox" name="is_active" id="chIsActive" value="1" checked style="width: 16px; height: 16px;">
                        <label for="chIsActive" style="font-size: 13px; font-weight: 600; cursor: pointer;">Status Aktif</label>
                    </div>
                </div>

                <button type="submit" id="btnSubmit" class="admin-btn admin-btn-primary" style="width: 100%; margin-top: 10px;">
                    💾 Simpan Channel TV
                </button>
                <button type="button" id="btnCancelEdit" onclick="cancelEdit()" class="admin-btn admin-btn-outline" style="width: 100%; margin-top: 8px; display: none;">
                    Batal Edit
                </button>
            </form>
        </div>

        <!-- M3U Playlist Batch Importer -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">⚡ Import Playlist M3U Batch</h3>
            </div>
            <p style="font-size: 12px; color: var(--text-secondary); margin-top: 0;">
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
                    <textarea name="m3u_text" rows="4" class="admin-textarea" placeholder="#EXTINF:-1 tvg-logo=&quot;http://...&quot; group-title=&quot;Berita&quot;,Kompas TV&#10;https://.../live.m3u8"></textarea>
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

    <!-- Daftar Channel TV -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">📺 Daftar Siaran TV (<?= count($channels) ?>)</h3>
            <a href="/tv" target="_blank" class="admin-btn admin-btn-primary admin-btn-sm">
                ▶️ Buka Web Player Fullscreen ↗
            </a>
        </div>

        <?php if (empty($channels)): ?>
            <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                Belum ada channel TV streaming yang didaftarkan. Silakan tambahkan atau import dari M3U.
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Logo</th>
                            <th>Nama Channel</th>
                            <th>Kategori</th>
                            <th>Stream URL (M3U/M3U8)</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($channels as $c): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($c['logo_url'])): ?>
                                        <img src="<?= esc($c['logo_url']) ?>" alt="Logo" style="width: 38px; height: 38px; object-fit: contain; border-radius: 8px; background: var(--bg); padding: 2px; border: 1px solid var(--border);">
                                    <?php else: ?>
                                        <div style="width: 38px; height: 38px; border-radius: 8px; background: #0284C7; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">
                                            📺
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="font-size: 14px;"><?= esc($c['name']) ?></strong>
                                    <?php if (!empty($c['description'])): ?>
                                        <div style="font-size: 11px; color: var(--text-secondary);"><?= esc(mb_strimwidth($c['description'], 0, 40, '...')) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-admin info"><?= esc($c['category'] ?? 'Nasional') ?></span>
                                </td>
                                <td>
                                    <div style="max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: monospace; font-size: 11.5px; color: var(--text-secondary);">
                                        <?= esc($c['stream_url']) ?>
                                    </div>
                                </td>
                                <td>
                                    <form action="/admin/tv/toggle/<?= $c['id'] ?>" method="post" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="badge-admin <?= $c['is_active'] ? 'success' : 'danger' ?>" style="cursor: pointer; border: none;">
                                            <?= $c['is_active'] ? '● AKTIF' : '○ NON-AKTIF' ?>
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <button onclick="playStream('<?= esc(addslashes($c['name'])) ?>', '<?= esc(addslashes($c['stream_url'])) ?>')" class="admin-btn admin-btn-primary admin-btn-sm" title="Test Live Stream">
                                        ▶️ Test
                                    </button>
                                    <button onclick='editChannel(<?= json_encode($c) ?>)' class="admin-btn admin-btn-outline admin-btn-sm" title="Edit">
                                        ✏️ Edit
                                    </button>
                                    <form action="/admin/tv/delete/<?= $c['id'] ?>" method="post" onsubmit="return confirm('Hapus channel <?= esc(addslashes($c['name'])) ?>?')" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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

    function editChannel(ch) {
        document.getElementById('formTitle').innerText = '✏️ Edit Channel: ' + ch.name;
        document.getElementById('channelForm').action = '/admin/tv/update/' + ch.id;
        document.getElementById('chName').value = ch.name;
        document.getElementById('chCategory').value = ch.category || 'Nasional';
        document.getElementById('chStreamUrl').value = ch.stream_url;
        document.getElementById('chLogoUrl').value = ch.logo_url || '';
        document.getElementById('chDescription').value = ch.description || '';
        document.getElementById('chSortOrder').value = ch.sort_order || 0;
        document.getElementById('chIsActive').checked = ch.is_active == 1;

        document.getElementById('btnSubmit').innerText = '💾 Perbarui Channel TV';
        document.getElementById('btnCancelEdit').style.display = 'block';
        document.getElementById('channelForm').scrollIntoView({ behavior: 'smooth' });
    }

    function cancelEdit() {
        document.getElementById('formTitle').innerText = '📺 Tambah Channel TV Baru';
        document.getElementById('channelForm').action = '/admin/tv/store';
        document.getElementById('channelForm').reset();
        document.getElementById('chIsActive').checked = true;
        document.getElementById('btnSubmit').innerText = '💾 Simpan Channel TV';
        document.getElementById('btnCancelEdit').style.display = 'none';
    }
</script>
<?= $this->endSection() ?>
