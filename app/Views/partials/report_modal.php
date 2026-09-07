<!-- REPORT / BLOCK MODAL (Reusable across PWA pages) -->
<style>
.report-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.72);
    backdrop-filter: blur(6px);
    z-index: 99998;
    display: none;
    align-items: flex-end;
    justify-content: center;
    animation: reportFadeIn 0.25s ease;
}
.report-overlay.show { display: flex; }
@media (min-width: 520px) {
    .report-overlay { align-items: center; padding: 16px; }
}
@keyframes reportFadeIn { from { opacity: 0; } to { opacity: 1; } }
.report-sheet {
    background: var(--bg-card, #ffffff);
    border-radius: 24px 24px 0 0;
    max-width: 480px;
    width: 100%;
    padding: 10px 18px 20px;
    box-shadow: 0 -10px 40px rgba(0,0,0,0.2);
    animation: reportUp 0.3s cubic-bezier(0.16,1,0.3,1);
}
@media (min-width: 520px) {
    .report-sheet { border-radius: 24px; }
}
@keyframes reportUp {
    from { transform: translateY(40px); }
    to { transform: translateY(0); }
}
.report-handle {
    width: 44px; height: 5px; border-radius: 4px; background: var(--border, #E2E8F0);
    margin: 4px auto 12px;
}
.report-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 8px;
}
.report-title { font-size: 16px; font-weight: 900; color: var(--text-primary, #0F172A); }
.report-close {
    background: var(--bg, #F1F5F9); border: none; width: 30px; height: 30px;
    border-radius: 50%; font-size: 15px; font-weight: 800; cursor: pointer; color: var(--text-secondary, #475569);
}
.report-sub { font-size: 12px; color: var(--text-muted, #64748B); margin: 0 0 12px; line-height: 1.4; }
.report-target {
    display: flex; align-items: center; gap: 10px;
    background: var(--bg, #F8FAFC); border: 1px solid var(--border, #E2E8F0);
    border-radius: 14px; padding: 10px 12px; margin-bottom: 14px;
}
.report-target-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 900; font-size: 15px; flex-shrink: 0;
}
.report-target-name { font-size: 13px; font-weight: 800; color: var(--text-primary, #0F172A); line-height: 1.2; }
.report-target-sub { font-size: 11px; color: var(--text-muted, #64748B); }
.report-reasons {
    display: flex; flex-direction: column; gap: 8px;
    margin-bottom: 14px; max-height: 40vh; overflow-y: auto;
}
.report-reason {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 13px; border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 13px; cursor: pointer; transition: all 0.15s ease;
    background: var(--bg-card, #ffffff); font-size: 13px; font-weight: 700;
    color: var(--text-primary, #1E293B);
}
.report-reason:hover { border-color: var(--primary, #059669); background: var(--bg, #F8FAFC); }
.report-reason.selected {
    border-color: var(--primary, #059669);
    background: color-mix(in srgb, var(--primary, #059669) 8%, #ffffff);
    box-shadow: inset 0 0 0 1px var(--primary, #059669);
}
.report-desc {
    width: 100%; box-sizing: border-box; resize: none;
    border: 1.5px solid var(--border, #E2E8F0); border-radius: 13px;
    padding: 11px 13px; font-size: 13px; font-family: inherit;
    background: var(--bg-card, #ffffff); color: var(--text-primary, #0F172A);
    min-height: 68px; margin-bottom: 14px;
}
.report-desc:focus { outline: none; border-color: var(--primary, #059669); }
.report-actions { display: flex; gap: 10px; }
.report-btn {
    flex: 1; padding: 12px; border-radius: 14px; border: none;
    font-size: 13px; font-weight: 800; cursor: pointer; transition: all 0.15s ease;
}
.report-btn-cancel { background: var(--bg, #F1F5F9); color: var(--text-secondary, #475569); }
.report-btn-submit {
    background: #DC2626; color: #fff;
    box-shadow: 0 4px 14px rgba(220,38,38,0.3);
}
.report-btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
.report-block-link {
    text-align: center; font-size: 12px; color: #DC2626; font-weight: 700;
    margin-top: 12px; cursor: pointer;
}
.report-block-link:hover { text-decoration: underline; }
.report-note { font-size: 11px; color: var(--text-muted, #94A3B8); text-align: center; margin-top: 10px; line-height: 1.4; }
.report-note a { color: var(--primary, #059669); font-weight: 700; text-decoration: none; }
</style>

<div class="report-overlay" id="reportModalOverlay" onclick="if(event.target===this)closeReportModal()">
    <div class="report-sheet">
        <div class="report-handle"></div>
        <div class="report-head">
            <div class="report-title">🚩 Laporkan</div>
            <button type="button" class="report-close" onclick="closeReportModal()" aria-label="Tutup">✕</button>
        </div>
        <p class="report-sub">Bantu jaga keamanan komunitas DuitKu. Laporan Anda dirahasiakan &amp; akan ditinjau admin.</p>
        <div class="report-target">
            <div class="report-target-avatar" id="reportTargetAvatar" style="background:#059669">?</div>
            <div>
                <div class="report-target-name" id="reportTargetName">Target</div>
                <div class="report-target-sub" id="reportTargetSub">Pilih alasan di bawah</div>
            </div>
        </div>
        <div class="report-reasons" id="reportReasonList">
            <div class="report-reason" data-reason="spam" onclick="selectReportReason(this)">📣 Spam / Pesan berulang</div>
            <div class="report-reason" data-reason="scam" onclick="selectReportReason(this)">🪤 Penipuan / Scam</div>
            <div class="report-reason" data-reason="inappropriate" onclick="selectReportReason(this)">🚫 Konten tidak pantas</div>
            <div class="report-reason" data-reason="harassment" onclick="selectReportReason(this)">⚠️ Pelecehan / Ancaman</div>
            <div class="report-reason" data-reason="fake" onclick="selectReportReason(this)">🎭 Iklan / Akun palsu</div>
            <div class="report-reason" data-reason="illegal" onclick="selectReportReason(this)">⚖️ Barang / konten ilegal</div>
            <div class="report-reason" data-reason="other" onclick="selectReportReason(this)">➡️ Lainnya</div>
        </div>
        <textarea class="report-desc" id="reportDesc" placeholder="Tulis detail singkat (opsional)..."></textarea>
        <div class="report-actions">
            <button type="button" class="report-btn report-btn-cancel" onclick="closeReportModal()">Batal</button>
            <button type="button" class="report-btn report-btn-submit" id="reportSubmitBtn" onclick="submitReport()">Kirim Laporan</button>
        </div>
        <div class="report-block-link" id="reportBlockLink" onclick="blockReportTarget()">🚫 Blokir pengguna ini</div>
        <p class="report-note">Laporan akan ditinjau oleh tim moderasi DuitKu sesuai <a href="/moderation" onclick="closeReportModal()">Kebijakan Konten</a>.</p>
    </div>
</div>

<script>
(function() {
    let reportCtx = null;

    window.openReportModal = function(targetType, targetId, targetName, targetSub, avatarColor) {
        reportCtx = { targetType: targetType, targetId: targetId };
        document.getElementById('reportTargetName').textContent = targetName || (targetType === 'listing' ? 'Iklan Marketplace' : 'Pengguna');
        document.getElementById('reportTargetAvatar').textContent = (targetName || 'U').charAt(0).toUpperCase();
        document.getElementById('reportTargetAvatar').style.background = avatarColor || '#059669';
        document.getElementById('reportTargetSub').textContent = targetSub || ((targetType === 'listing') ? 'Laporkan konten iklan' : 'Laporkan pengguna ini');
        // Block link only for user targets
        document.getElementById('reportBlockLink').style.display = (targetType === 'user') ? 'block' : 'none';
        // Reset form
        document.querySelectorAll('.report-reason').forEach(el => el.classList.remove('selected'));
        document.getElementById('reportDesc').value = '';
        document.getElementById('reportSubmitBtn').disabled = true;
        setReportTypeLabel();
        document.getElementById('reportModalOverlay').classList.add('show');
    };

    window.closeReportModal = function() {
        document.getElementById('reportModalOverlay').classList.remove('show');
        reportCtx = null;
    };

    window.selectReportReason = function(el) {
        document.querySelectorAll('.report-reason').forEach(r => r.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('reportSubmitBtn').disabled = false;
    };

    function setReportTypeLabel() {
        // no-op placeholder for consistency
    }

    window.submitReport = function() {
        if (!reportCtx) return;
        const selected = document.querySelector('.report-reason.selected');
        const reason = selected ? selected.getAttribute('data-reason') : 'other';
        const desc = document.getElementById('reportDesc').value.trim();
        const btn = document.getElementById('reportSubmitBtn');
        btn.disabled = true;
        btn.textContent = 'Mengirim...';

        const fd = new FormData();
        fd.append('target_type', reportCtx.targetType);
        fd.append('target_id', reportCtx.targetId);
        fd.append('reason', reason);
        fd.append('description', desc);

        const headers = { 'X-Requested-With': 'XMLHttpRequest' };
        if (window.DUITKU && window.DUITKU.csrfToken) headers['X-CSRF-TOKEN'] = window.DUITKU.csrfToken;

        fetch('/marketplace/report', { method: 'POST', headers: headers, body: fd })
        .then(r => r.json())
        .then(res => {
            btn.textContent = 'Kirim Laporan';
            btn.disabled = false;
            if (res.success) {
                closeReportModal();
                alert('✅ ' + (res.message || 'Terima kasih atas laporan Anda.'));
            } else {
                alert(res.message || 'Gagal mengirim laporan.');
            }
        })
        .catch(err => {
            btn.textContent = 'Kirim Laporan';
            btn.disabled = false;
            alert('Gagal menghubungi server: ' + (err.message || 'Kesalahan jaringan.'));
        });
    };

    window.blockReportTarget = function() {
        if (!reportCtx || reportCtx.targetType !== 'user') return;
        const uId = reportCtx.targetId;
        const name = document.getElementById('reportTargetName').textContent;
        closeReportModal();
        if (!confirm('Yakin ingin memblokir "' + name + '"?\nAnda tidak akan lagi melihat konten dari pengguna ini.')) return;

        const fd = new FormData();
        fd.append('user_id', uId);
        const headers = { 'X-Requested-With': 'XMLHttpRequest' };
        if (window.DUITKU && window.DUITKU.csrfToken) headers['X-CSRF-TOKEN'] = window.DUITKU.csrfToken;

        fetch('/marketplace/block', { method: 'POST', headers: headers, body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) alert('🚫 ' + (res.message || 'Pengguna diblokir.'));
            else alert(res.message || 'Gagal memblokir.');
        })
        .catch(err => alert('Gagal menghubungi server: ' + (err.message || 'Kesalahan jaringan.')));
    };
})();
</script>