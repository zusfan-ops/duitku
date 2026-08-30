<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.todo-page {
    padding-bottom: 110px;
    max-width: 680px;
    margin: 0 auto;
}

/* ── Hero Stats Card ────────────────────────────────────────────── */
.todo-hero-card {
    background: linear-gradient(135deg, #3730A3 0%, #4F46E5 50%, #7C3AED 100%);
    border-radius: 24px;
    padding: 20px 22px;
    color: #ffffff;
    margin-bottom: 16px;
    box-shadow: 0 12px 32px rgba(79, 70, 229, 0.3);
    position: relative;
    overflow: hidden;
}
.todo-hero-card::after {
    content: '';
    position: absolute;
    right: -20px;
    bottom: -20px;
    width: 140px;
    height: 140px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    pointer-events: none;
}
.todo-hero-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.todo-hero-title {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.8);
}
.todo-hero-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 800;
}
.todo-hero-count {
    font-size: 28px;
    font-weight: 900;
    letter-spacing: -0.5px;
    line-height: 1.1;
    margin-bottom: 6px;
}
.todo-hero-sub {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.75);
    margin-bottom: 14px;
}
.todo-progress-track {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    height: 8px;
    overflow: hidden;
}
.todo-progress-fill {
    background: #10B981;
    height: 100%;
    border-radius: 10px;
    transition: width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* ── Quick Add Bar ──────────────────────────────────────────────── */
.todo-quick-add {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 12px 14px;
    margin-bottom: 16px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.todo-input-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.todo-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text-primary);
    outline: none;
    font-family: inherit;
}
.todo-input::placeholder {
    color: var(--text-muted);
}
.todo-add-btn {
    background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    transition: transform 0.15s ease, opacity 0.15s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}
.todo-add-btn:active {
    transform: scale(0.96);
}
.todo-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    padding-top: 6px;
    border-top: 1px solid var(--border-light);
}
.todo-select {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 5px 10px;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text-secondary);
    outline: none;
    cursor: pointer;
    font-family: inherit;
}
.todo-date-input {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 4px 8px;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text-secondary);
    outline: none;
    font-family: inherit;
}

/* ── Filter Tabs ────────────────────────────────────────────────── */
.todo-filter-tabs {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 12px;
    scrollbar-width: none;
}
.todo-filter-tabs::-webkit-scrollbar { display: none; }
.todo-tab-btn {
    padding: 6px 14px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    white-space: nowrap;
    text-decoration: none;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}
.todo-tab-btn.active {
    background: #4F46E5;
    color: #ffffff;
    border-color: #4F46E5;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}

/* ── Category Strip ─────────────────────────────────────────────── */
.todo-cat-strip {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 16px;
    scrollbar-width: none;
}
.todo-cat-strip::-webkit-scrollbar { display: none; }
.todo-cat-pill {
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    white-space: nowrap;
    text-decoration: none;
    transition: all 0.15s ease;
}
.todo-cat-pill.active {
    background: var(--primary-dim);
    color: var(--primary);
    border-color: var(--primary);
}

/* ── Task List & Card ───────────────────────────────────────────── */
.todo-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.todo-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 14px 16px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: flex-start;
    gap: 14px;
    transition: transform 0.15s ease, border-color 0.15s ease, opacity 0.2s ease;
    position: relative;
}
.todo-card.completed {
    opacity: 0.65;
    background: var(--bg);
}
.todo-card.completed .todo-title {
    text-decoration: line-through;
    color: var(--text-muted);
}

/* Checkbox Button */
.todo-checkbox {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid #CBD5E1;
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    color: transparent;
}
.todo-checkbox:hover {
    border-color: #4F46E5;
}
.todo-card.completed .todo-checkbox {
    background: #10B981;
    border-color: #10B981;
    color: #ffffff;
    transform: scale(1.05);
}

.todo-content {
    flex: 1;
    min-width: 0;
}
.todo-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
}
.todo-title {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.3;
    word-break: break-word;
}
.todo-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 8px;
    line-height: 1.4;
    word-break: break-word;
}
.todo-tags-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}
.todo-tag {
    font-size: 10.5px;
    font-weight: 700;
    padding: 2.5px 7px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.todo-tag.prio-high {
    background: rgba(239, 68, 68, 0.12);
    color: #EF4444;
}
.todo-tag.prio-medium {
    background: rgba(245, 158, 11, 0.12);
    color: #F59E0B;
}
.todo-tag.prio-low {
    background: rgba(59, 130, 246, 0.12);
    color: #3B82F6;
}
.todo-tag.due {
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-secondary);
}
.todo-tag.due.urgent {
    background: rgba(239, 68, 68, 0.12);
    color: #EF4444;
    border-color: rgba(239, 68, 68, 0.2);
}
.todo-tag.cat {
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-secondary);
}

.todo-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-left: auto;
}
.todo-act-btn {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    cursor: pointer;
    background: transparent;
    border: none;
    transition: all 0.15s ease;
}
.todo-act-btn:hover {
    background: var(--bg);
    color: var(--text-primary);
}
.todo-act-btn.delete:hover {
    color: var(--expense);
    background: rgba(239, 68, 68, 0.1);
}

/* ── Subtasks preview ───────────────────────────────────────────── */
.todo-subtasks-box {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px dashed var(--border);
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.todo-subtask-item {
    font-size: 11.5px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 6px;
}
.todo-subtask-item.done {
    text-decoration: line-through;
    color: var(--text-muted);
}

/* ── Modal Edit / Subtask Sheet ─────────────────────────────────── */
.todo-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.todo-modal-overlay.open {
    display: flex;
}
.todo-modal-sheet {
    background: var(--bg-card);
    border-radius: 24px;
    width: 100%;
    max-width: 480px;
    padding: 22px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
    max-height: 90vh;
    overflow-y: auto;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="todo-page">

    <!-- ── Hero Summary Card ──────────────────────────────────────── -->
    <div class="todo-hero-card">
        <div class="todo-hero-top">
            <span class="todo-hero-title">Rencana &amp; Tugas</span>
            <span class="todo-hero-badge"><?= date('l, d F') ?></span>
        </div>
        <div class="todo-hero-count" id="heroTaskCount">
            <?= (int)$summary['completed_all'] ?> / <?= (int)$summary['total_all'] ?> Selesai
        </div>
        <div class="todo-hero-sub">
            <?= (int)$summary['pending_all'] ?> tugas aktif · <?= (int)$summary['overdue_count'] ?> jatuh tempo
        </div>
        <div class="todo-progress-track">
            <div class="todo-progress-fill" id="heroProgressBar" style="width: <?= (int)$summary['completion_rate'] ?>%"></div>
        </div>
    </div>

    <!-- ── Quick Add Task Bar ────────────────────────────────────── -->
    <form action="/todo/store" method="POST" class="todo-quick-add" id="quickAddForm">
        <?= csrf_field() ?>
        <div class="todo-input-row">
            <input type="text" name="title" class="todo-input" id="quickTaskInput" placeholder="Tambah tugas baru (contoh: Bayar Listrik)..." required autocomplete="off">
            <button type="submit" class="todo-add-btn">
                <span>+</span> Tambah
            </button>
        </div>
        <div class="todo-meta-row">
            <select name="category" class="todo-select">
                <?php foreach (($todoCategories ?? []) as $cName => $cData): ?>
                <option value="<?= esc($cName) ?>"><?= $cData['icon'] ?> <?= esc($cName) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="priority" class="todo-select">
                <option value="medium">⚡ Prioritas: Sedang</option>
                <option value="high">🔥 Prioritas: Tinggi</option>
                <option value="low">🌱 Prioritas: Rendah</option>
            </select>
            <input type="date" name="due_date" class="todo-date-input" value="<?= date('Y-m-d') ?>" title="Jatuh Tempo">
        </div>
    </form>

    <!-- ── Filter Tabs ────────────────────────────────────────────── -->
    <div class="todo-filter-tabs">
        <a href="/todo?filter=all&category=<?= esc($category) ?>" class="todo-tab-btn <?= $filter === 'all' ? 'active' : '' ?>">
            📋 Semua (<?= $summary['total_all'] ?>)
        </a>
        <a href="/todo?filter=today&category=<?= esc($category) ?>" class="todo-tab-btn <?= $filter === 'today' ? 'active' : '' ?>">
            ☀️ Hari Ini (<?= $summary['total_today'] ?>)
        </a>
        <a href="/todo?filter=high&category=<?= esc($category) ?>" class="todo-tab-btn <?= $filter === 'high' ? 'active' : '' ?>">
            🔥 Prioritas Tinggi
        </a>
        <a href="/todo?filter=completed&category=<?= esc($category) ?>" class="todo-tab-btn <?= $filter === 'completed' ? 'active' : '' ?>">
            ✅ Selesai (<?= $summary['completed_all'] ?>)
        </a>
    </div>

    <!-- ── Category Strip ─────────────────────────────────────────── -->
    <div class="todo-cat-strip">
        <a href="/todo?filter=<?= esc($filter) ?>&category=all" class="todo-cat-pill <?= $category === 'all' ? 'active' : '' ?>">
            Semua Kategori
        </a>
        <?php foreach (($todoCategories ?? []) as $cName => $cData): ?>
        <a href="/todo?filter=<?= esc($filter) ?>&category=<?= urlencode($cName) ?>" class="todo-cat-pill <?= $category === $cName ? 'active' : '' ?>">
            <?= $cData['icon'] ?> <?= esc($cName) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ── Task List ──────────────────────────────────────────────── -->
    <div class="todo-list" id="todoList">
        <?php if (empty($tasks)): ?>
        <div style="text-align:center;padding:48px 16px;color:var(--text-muted);background:var(--bg-card);border-radius:20px;border:1px solid var(--border);">
            <div style="font-size:36px;margin-bottom:8px">🎯</div>
            <div style="font-size:15px;font-weight:800;color:var(--text-primary);margin-bottom:4px">Belum Ada Tugas</div>
            <div style="font-size:12px">Semua target dan tugas Anda telah selesai atau belum ditambahkan.</div>
        </div>
        <?php else: ?>
        <?php foreach ($tasks as $t): 
            $isDone = !empty($t['is_completed']);
            $isOverdue = !$isDone && !empty($t['due_date']) && $t['due_date'] < date('Y-m-d');
            $isToday = !$isDone && !empty($t['due_date']) && $t['due_date'] === date('Y-m-d');
            $prio = $t['priority'] ?? 'medium';
            $prioClass = $prio === 'high' ? 'prio-high' : ($prio === 'low' ? 'prio-low' : 'prio-medium');
            $prioLabel = $prio === 'high' ? '🔥 Tinggi' : ($prio === 'low' ? '🌱 Rendah' : '⚡ Sedang');
            $catIcon = $todoCategories[$t['category']]['icon'] ?? '📝';
        ?>
        <div class="todo-card <?= $isDone ? 'completed' : '' ?>" id="todoCard-<?= $t['id'] ?>" data-id="<?= $t['id'] ?>">
            <button type="button" class="todo-checkbox" onclick="toggleTask(<?= $t['id'] ?>)" title="Tandai selesai">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="14" height="14">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </button>

            <div class="todo-content">
                <div class="todo-title-row">
                    <div class="todo-title"><?= esc($t['title']) ?></div>
                    <div class="todo-actions">
                        <button type="button" class="todo-act-btn" onclick="openEditModal(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Tugas">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button type="button" class="todo-act-btn delete" onclick="deleteTask(<?= $t['id'] ?>)" title="Hapus">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>

                <?php if (!empty($t['description'])): ?>
                <div class="todo-desc"><?= nl2br(esc($t['description'])) ?></div>
                <?php endif; ?>

                <div class="todo-tags-row">
                    <span class="todo-tag <?= $prioClass ?>"><?= $prioLabel ?></span>
                    <span class="todo-tag cat"><?= $catIcon ?> <?= esc($t['category']) ?></span>
                    <?php if (!empty($t['due_date'])): ?>
                    <span class="todo-tag due <?= $isOverdue ? 'urgent' : '' ?>">
                        🗓️ <?= date('d M', strtotime($t['due_date'])) ?><?= !empty($t['due_time']) ? ' ' . date('H:i', strtotime($t['due_time'])) : '' ?>
                        <?= $isOverdue ? ' (Lewat)' : ($isToday ? ' (Hari ini)' : '') ?>
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Subtasks Checklist -->
                <?php if (!empty($t['subtasks_array'])): 
                    $doneSub = count(array_filter($t['subtasks_array'], fn($s) => !empty($s['done'])));
                    $totalSub = count($t['subtasks_array']);
                ?>
                <div class="todo-subtasks-box">
                    <div style="font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:2px">
                        Subtugas (<?= $doneSub ?>/<?= $totalSub ?>):
                    </div>
                    <?php foreach ($t['subtasks_array'] as $sIdx => $st): ?>
                    <div class="todo-subtask-item <?= !empty($st['done']) ? 'done' : '' ?>">
                        <span><?= !empty($st['done']) ? '☑' : '☐' ?></span>
                        <span><?= esc($st['title']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ EDIT TASK MODAL -->
<div class="todo-modal-overlay" id="editModalOverlay">
    <div class="todo-modal-sheet">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h3 style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary)">Edit Tugas</h3>
            <button type="button" onclick="closeEditModal()" style="font-size:20px;color:var(--text-muted);cursor:pointer;background:none;border:none">✕</button>
        </div>

        <form id="editTaskForm" method="POST" action="">
            <?= csrf_field() ?>
            <div style="margin-bottom:12px">
                <label style="font-size:11.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Judul Tugas</label>
                <input type="text" name="title" id="editTitle" class="todo-input" style="width:100%;border:1px solid var(--border);border-radius:12px;padding:10px 12px;margin-top:4px;background:var(--bg);box-sizing:border-box;" required>
            </div>

            <div style="margin-bottom:12px">
                <label style="font-size:11.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Catatan / Rincian</label>
                <textarea name="description" id="editDesc" rows="3" style="width:100%;border:1px solid var(--border);border-radius:12px;padding:10px 12px;margin-top:4px;background:var(--bg);box-sizing:border-box;font-family:inherit;font-size:13px;color:var(--text-primary);outline:none;resize:vertical;"></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
                <div>
                    <label style="font-size:11.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Kategori</label>
                    <select name="category" id="editCategory" class="todo-select" style="width:100%;margin-top:4px;padding:8px 10px;">
                        <?php foreach (($todoCategories ?? []) as $cName => $cData): ?>
                        <option value="<?= esc($cName) ?>"><?= $cData['icon'] ?> <?= esc($cName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size:11.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Prioritas</label>
                    <select name="priority" id="editPriority" class="todo-select" style="width:100%;margin-top:4px;padding:8px 10px;">
                        <option value="high">🔥 Tinggi</option>
                        <option value="medium">⚡ Sedang</option>
                        <option value="low">🌱 Rendah</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
                <div>
                    <label style="font-size:11.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Jatuh Tempo</label>
                    <input type="date" name="due_date" id="editDueDate" class="todo-date-input" style="width:100%;margin-top:4px;padding:8px 10px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:11.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Jam (Opsional)</label>
                    <input type="time" name="due_time" id="editDueTime" class="todo-date-input" style="width:100%;margin-top:4px;padding:8px 10px;box-sizing:border-box;">
                </div>
            </div>

            <!-- Subtasks management -->
            <div style="margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                    <label style="font-size:11.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Subtugas / Checklist</label>
                    <button type="button" onclick="addSubtaskRow()" style="font-size:11.5px;font-weight:800;color:#4F46E5;background:none;border:none;cursor:pointer">+ Tambah Baris</button>
                </div>
                <div id="subtasksContainer" style="display:flex;flex-direction:column;gap:6px"></div>
            </div>

            <div style="display:flex;gap:10px">
                <button type="button" onclick="closeEditModal()" style="flex:1;padding:12px;border-radius:12px;border:1px solid var(--border);background:var(--bg);font-weight:700;color:var(--text-secondary);cursor:pointer">Batal</button>
                <button type="submit" style="flex:1;padding:12px;border-radius:12px;border:none;background:linear-gradient(135deg,#4F46E5,#7C3AED);font-weight:800;color:#ffffff;cursor:pointer">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
async function toggleTask(taskId) {
    const card = document.getElementById('todoCard-' + taskId);
    if (!card) return;

    try {
        const res = await fetch('/todo/toggle/' + taskId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
        });

        const data = await res.json();
        if (data.success) {
            if (data.is_completed) {
                card.classList.add('completed');
            } else {
                card.classList.remove('completed');
            }

            // Update hero counter
            if (data.summary) {
                const countEl = document.getElementById('heroTaskCount');
                const progEl = document.getElementById('heroProgressBar');
                if (countEl) countEl.innerText = `${data.summary.completed_all} / ${data.summary.total_all} Selesai`;
                if (progEl) progEl.style.width = `${data.summary.completion_rate}%`;
            }
        }
    } catch(err) {
        console.error('Error toggling task:', err);
    }
}

async function deleteTask(taskId) {
    if (!confirm('Yakin ingin menghapus tugas ini?')) return;

    try {
        const res = await fetch('/todo/delete/' + taskId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
        });

        const data = await res.json();
        if (data.success) {
            const card = document.getElementById('todoCard-' + taskId);
            if (card) {
                card.style.transform = 'scale(0.8)';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 200);
            }
            if (data.summary) {
                const countEl = document.getElementById('heroTaskCount');
                const progEl = document.getElementById('heroProgressBar');
                if (countEl) countEl.innerText = `${data.summary.completed_all} / ${data.summary.total_all} Selesai`;
                if (progEl) progEl.style.width = `${data.summary.completion_rate}%`;
            }
        }
    } catch(err) {
        console.error('Error deleting task:', err);
    }
}

function openEditModal(task) {
    const overlay = document.getElementById('editModalOverlay');
    const form = document.getElementById('editTaskForm');
    if (!overlay || !form) return;

    form.action = '/todo/update/' + task.id;
    document.getElementById('editTitle').value = task.title || '';
    document.getElementById('editDesc').value = task.description || '';
    document.getElementById('editCategory').value = task.category || 'Pribadi';
    document.getElementById('editPriority').value = task.priority || 'medium';
    document.getElementById('editDueDate').value = task.due_date || '';
    document.getElementById('editDueTime').value = task.due_time || '';

    // Render subtasks
    const container = document.getElementById('subtasksContainer');
    container.innerHTML = '';
    const subtasks = task.subtasks_array || [];
    subtasks.forEach((st, idx) => {
        addSubtaskRow(st.title, st.done);
    });

    overlay.classList.add('open');
}

function closeEditModal() {
    const overlay = document.getElementById('editModalOverlay');
    if (overlay) overlay.classList.remove('open');
}

function addSubtaskRow(title = '', done = 0) {
    const container = document.getElementById('subtasksContainer');
    if (!container) return;
    const idx = container.children.length;

    const row = document.createElement('div');
    row.style.cssText = 'display:flex;align-items:center;gap:6px;';
    row.innerHTML = `
        <input type="checkbox" name="subtasks[${idx}][done]" value="1" ${done ? 'checked' : ''} style="width:16px;height:16px;cursor:pointer">
        <input type="text" name="subtasks[${idx}][title]" value="${title.replace(/"/g, '&quot;')}" placeholder="Subtugas..." class="todo-input" style="flex:1;border:1px solid var(--border);border-radius:8px;padding:6px 8px;background:var(--bg);font-size:12px;">
        <button type="button" onclick="this.parentElement.remove()" style="color:var(--expense);background:none;border:none;font-size:14px;cursor:pointer">✕</button>
    `;
    container.appendChild(row);
}
</script>
<?= $this->endSection() ?>
