<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div style="max-width: 900px; margin: 0 auto; padding-bottom: 80px;">

    <!-- Top Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <a href="/pos/orders" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">← Pesanan Masuk</a>
                <h1 style="font-size: 20px; font-weight: 800; margin: 0;">🏷️ Kupon Diskon & Promo Toko</h1>
            </div>
            <p style="font-size: 13px; color: var(--text-muted); margin: 4px 0 0 0;">
                Buat kode voucher promo untuk diskon persen, potongan nominal, atau gratis ongkos kirim.
            </p>
        </div>

        <button class="btn btn-primary" onclick="openVoucherModal()" style="border-radius: 12px; font-weight: 800; padding: 10px 18px;">
            + Tambah Kupon Promo
        </button>
    </div>

    <!-- Voucher Cards Grid -->
    <?php if (empty($vouchers)): ?>
        <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 48px 20px; text-align: center;">
            <div style="font-size: 44px; margin-bottom: 12px;">🏷️</div>
            <h3 style="font-size: 17px; font-weight: 800;">Belum Ada Kupon Promo</h3>
            <p style="font-size: 13px; color: var(--text-muted); max-width: 420px; margin: 6px auto 16px;">
                Tingkatkan penjualan dengan memberikan diskon promo atau gratis ongkir untuk pelanggan toko online Anda.
            </p>
            <button class="btn btn-primary btn-sm" onclick="openVoucherModal()" style="border-radius: 10px; font-weight: 700;">
                Buat Kupon Pertama
            </button>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px;">
            <?php foreach ($vouchers as $v): ?>
                <?php
                    $isExpired = !empty($v['expires_at']) && $v['expires_at'] < date('Y-m-d');
                    $isLimitReached = (int)$v['usage_limit'] > 0 && (int)$v['used_count'] >= (int)$v['usage_limit'];
                ?>
                <div style="background: var(--card-bg); border: 2px dashed <?= $v['is_active'] ? 'rgba(234,88,12,0.4)' : 'var(--border-color)' ?>; border-radius: 16px; padding: 16px; position: relative; overflow: hidden;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <div>
                            <span style="font-family: monospace; font-size: 16px; font-weight: 900; background: rgba(234,88,12,0.12); color: #EA580C; padding: 4px 8px; border-radius: 6px; letter-spacing: 1px;">
                                <?= esc($v['code']) ?>
                            </span>
                        </div>
                        <div>
                            <?php if (!$v['is_active']): ?>
                                <span class="badge bg-secondary">Nonaktif</span>
                            <?php elseif ($isExpired): ?>
                                <span class="badge bg-danger">Kedaluwarsa</span>
                            <?php elseif ($isLimitReached): ?>
                                <span class="badge bg-warning text-dark">Kuota Habis</span>
                            <?php else: ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h4 style="font-size: 14px; font-weight: 800; margin: 0 0 6px;"><?= esc($v['title']) ?></h4>

                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 10px; line-height: 1.4;">
                        <?php if ($v['type'] === 'percent'): ?>
                            🎉 Diskon <strong><?= (float)$v['value'] ?>%</strong>
                            <?php if ((float)$v['max_discount'] > 0): ?>
                                (Maks. <?= $symbol ?> <?= number_format($v['max_discount'], 0, ',', '.') ?>)
                            <?php endif; ?>
                        <?php elseif ($v['type'] === 'free_shipping'): ?>
                            🛵 <strong>Gratis Ongkir</strong>
                        <?php else: ?>
                            💵 Potongan <strong><?= $symbol ?> <?= number_format($v['value'], 0, ',', '.') ?></strong>
                        <?php endif; ?>

                        <?php if ((float)$v['min_order'] > 0): ?>
                            <br><small>Min. belanja <?= $symbol ?> <?= number_format($v['min_order'], 0, ',', '.') ?></small>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 10px; font-size: 11.5px; color: var(--text-muted);">
                        <div>Dipakai: <strong><?= (int)$v['used_count'] ?></strong><?= ((int)$v['usage_limit'] > 0) ? ' / ' . (int)$v['usage_limit'] : '' ?> kali</div>
                        <div style="display: flex; gap: 6px;">
                            <button onclick='editVoucher(<?= json_encode($v) ?>)' class="btn btn-xs btn-outline-secondary" style="border-radius: 6px;">Edit</button>
                            <button onclick="deleteVoucher(<?= $v['id'] ?>)" class="btn btn-xs btn-outline-danger" style="border-radius: 6px;">Hapus</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Modal Form Voucher -->
<div class="modal fade" id="voucherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="voucherModalTitle">🏷️ Tambah Kupon Promo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="voucherForm" onsubmit="saveVoucher(event)">
                    <input type="hidden" name="id" id="v_id" value="0">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Promo (Kupon) *</label>
                        <input type="text" name="code" id="v_code" class="form-control" placeholder="Contoh: HEMAT10, BEBASONGKIR" style="text-transform: uppercase; font-weight: 800; font-family: monospace;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul / Keterangan Promo</label>
                        <input type="text" name="title" id="v_title" class="form-control" placeholder="Contoh: Diskon Kemerdekaan 10%">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Tipe Diskon</label>
                            <select name="type" id="v_type" class="form-select" onchange="toggleVoucherFields()">
                                <option value="nominal">Potongan Nominal (Rp)</option>
                                <option value="percent">Persentase (%)</option>
                                <option value="free_shipping">Gratis Ongkir Flat</option>
                            </select>
                        </div>
                        <div class="col-6" id="valContainer">
                            <label class="form-label fw-bold" id="valLabel">Nilai Potongan (Rp)</label>
                            <input type="number" name="value" id="v_value" class="form-control" value="5000" min="0">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Min. Belanja (Rp)</label>
                            <input type="number" name="min_order" id="v_min_order" class="form-control" value="0">
                        </div>
                        <div class="col-6" id="maxDiscContainer">
                            <label class="form-label fw-bold">Maks. Diskon (Rp)</label>
                            <input type="number" name="max_discount" id="v_max_discount" class="form-control" value="0" placeholder="0 = tanpa batas">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Batas Kuota Pemakaian</label>
                            <input type="number" name="usage_limit" id="v_usage_limit" class="form-control" value="0" placeholder="0 = tak terbatas">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Tanggal Berakhir</label>
                            <input type="date" name="expires_at" id="v_expires_at" class="form-control">
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="v_is_active" value="1" checked>
                        <label class="form-check-label fw-bold" for="v_is_active">Kupon Aktif & Bisa Dipakai</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 12px; font-weight: 800; padding: 12px;">
                        Simpan Kupon Promo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let voucherModalInstance = null;
    document.addEventListener('DOMContentLoaded', () => {
        voucherModalInstance = new bootstrap.Modal(document.getElementById('voucherModal'));
    });

    function openVoucherModal() {
        document.getElementById('voucherForm').reset();
        document.getElementById('v_id').value = '0';
        document.getElementById('voucherModalTitle').textContent = '🏷️ Tambah Kupon Promo';
        document.getElementById('v_is_active').checked = true;
        toggleVoucherFields();
        voucherModalInstance.show();
    }

    function editVoucher(v) {
        document.getElementById('v_id').value = v.id;
        document.getElementById('v_code').value = v.code;
        document.getElementById('v_title').value = v.title || '';
        document.getElementById('v_type').value = v.type;
        document.getElementById('v_value').value = parseFloat(v.value) || 0;
        document.getElementById('v_min_order').value = parseFloat(v.min_order) || 0;
        document.getElementById('v_max_discount').value = parseFloat(v.max_discount) || 0;
        document.getElementById('v_usage_limit').value = parseInt(v.usage_limit) || 0;
        document.getElementById('v_expires_at').value = v.expires_at || '';
        document.getElementById('v_is_active').checked = (v.is_active == 1);
        document.getElementById('voucherModalTitle').textContent = '✏️ Edit Kupon ' + v.code;
        toggleVoucherFields();
        voucherModalInstance.show();
    }

    function toggleVoucherFields() {
        const type = document.getElementById('v_type').value;
        const valContainer = document.getElementById('valContainer');
        const valLabel = document.getElementById('valLabel');
        const maxDiscContainer = document.getElementById('maxDiscContainer');

        if (type === 'free_shipping') {
            valContainer.style.display = 'none';
            maxDiscContainer.style.display = 'none';
        } else if (type === 'percent') {
            valContainer.style.display = 'block';
            valLabel.textContent = 'Persentase Diskon (%)';
            maxDiscContainer.style.display = 'block';
        } else {
            valContainer.style.display = 'block';
            valLabel.textContent = 'Nilai Potongan (Rp)';
            maxDiscContainer.style.display = 'none';
        }
    }

    async function saveVoucher(e) {
        e.preventDefault();
        const form = document.getElementById('voucherForm');
        const formData = new FormData(form);

        try {
            const res = await fetch('/pos/vouchers/store', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan voucher.');
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
        }
    }

    async function deleteVoucher(id) {
        if (!confirm('Hapus kupon promo ini?')) return;
        try {
            const res = await fetch('/pos/vouchers/delete/' + id, { method: 'POST' });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal menghapus');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
    }
</script>

<?= $this->endSection() ?>
