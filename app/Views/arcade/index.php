<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.arcade-page {
    max-width: 600px;
    margin: 0 auto;
    padding: 12px 16px 120px;
}

.arcade-hub-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 20px;
}

.arcade-game-btn {
    border-radius: 16px;
    padding: 14px 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 2px solid transparent;
    cursor: pointer;
    text-decoration: none;
    color: #ffffff;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
}

.arcade-game-btn:active {
    transform: scale(0.95);
}

.arcade-game-btn.active {
    border-color: #ffffff;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.4);
}

.btn-tetris { background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); }
.btn-2048 { background: linear-gradient(135deg, #EA580C 0%, #F59E0B 100%); }
.btn-coin { background: linear-gradient(135deg, #059669 0%, #10B981 100%); }

.arcade-icon {
    font-size: 26px;
    margin-bottom: 4px;
}

.arcade-name {
    font-size: 11.5px;
    font-weight: 800;
    text-align: center;
}

.arcade-card {
    background: var(--card);
    border-radius: 20px;
    border: 1px solid var(--border);
    padding: 20px 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Canvas / Screen */
.game-container {
    width: 100%;
    max-width: 320px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.game-score-bar {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--bg);
    padding: 10px 14px;
    border-radius: 14px;
    margin-bottom: 12px;
    border: 1px solid var(--border);
}

.game-score-pill {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.game-score-pill span { font-size: 10px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; }
.game-score-pill strong { font-size: 16px; font-weight: 900; color: var(--primary); }

.game-canvas {
    background: #0B1120;
    border-radius: 16px;
    border: 2px solid #1E293B;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    touch-action: none;
}

/* Touch Controls */
.game-controls {
    display: flex;
    gap: 8px;
    margin-top: 14px;
    width: 100%;
    max-width: 320px;
    justify-content: center;
}

.game-btn {
    flex: 1;
    height: 48px;
    border-radius: 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-primary);
    font-size: 18px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    user-select: none;
    -webkit-user-select: none;
}
.game-btn:active {
    background: var(--primary);
    color: #ffffff;
    transform: scale(0.94);
}

/* 2048 Grid */
.grid-2048 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    background: #0F172A;
    padding: 10px;
    border-radius: 16px;
    width: 290px;
    height: 290px;
    box-sizing: border-box;
}

.tile-2048 {
    background: #1E293B;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 900;
    color: #ffffff;
    user-select: none;
    transition: transform 0.1s ease;
}

.t-2 { background: #334155; color: #F8FAFC; }
.t-4 { background: #0284C7; color: #ffffff; }
.t-8 { background: #059669; color: #ffffff; }
.t-16 { background: #10B981; color: #ffffff; }
.t-32 { background: #D97706; color: #ffffff; }
.t-64 { background: #F59E0B; color: #ffffff; }
.t-128 { background: #EA580C; color: #ffffff; }
.t-256 { background: #DC2626; color: #ffffff; }
.t-512 { background: #9333EA; color: #ffffff; }
.t-1024 { background: #7C3AED; color: #ffffff; }
.t-2048 { background: linear-gradient(135deg, #F59E0B, #EC4899); color: #ffffff; box-shadow: 0 0 12px rgba(236, 72, 153, 0.6); }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="arcade-page">
    
    <!-- Title -->
    <div style="margin-bottom: 16px; text-align: center;">
        <h2 style="margin: 0 0 4px; font-size: 20px; font-weight: 800; color: var(--text-primary);">🎮 DuitKu Arcade Hub</h2>
        <p style="margin: 0; font-size: 12px; color: var(--text-secondary);">Mini-games edukasi ketangkasan keuangan santai langsung di browser & PWA!</p>
    </div>

    <!-- Hub Switcher -->
    <div class="arcade-hub-grid">
        <button id="btn-tab-tetris" class="arcade-game-btn btn-tetris active" onclick="switchGame('tetris')">
            <span class="arcade-icon">🧱</span>
            <span class="arcade-name">Tetris Retro</span>
        </button>
        <button id="btn-tab-2048" class="arcade-game-btn btn-2048" onclick="switchGame('2048')">
            <span class="arcade-icon">🔢</span>
            <span class="arcade-name">Money 2048</span>
        </button>
        <button id="btn-tab-coin" class="arcade-game-btn btn-coin" onclick="switchGame('coin')">
            <span class="arcade-icon">🪙</span>
            <span class="arcade-name">Coin Catcher</span>
        </button>
    </div>

    <!-- ── 1. GAME: TETRIS ── -->
    <div id="game-tetris-view" class="arcade-card">
        <div class="game-container">
            <div class="game-score-bar">
                <div class="game-score-pill">
                    <span>Skor</span>
                    <strong id="tetris-score">0</strong>
                </div>
                <div class="game-score-pill">
                    <span>Level</span>
                    <strong id="tetris-level">1</strong>
                </div>
                <div class="game-score-pill">
                    <span>Tertinggi</span>
                    <strong id="tetris-high">0</strong>
                </div>
            </div>

            <canvas id="tetris-canvas" class="game-canvas" width="200" height="360"></canvas>

            <!-- Touch Controls -->
            <div class="game-controls">
                <button class="game-btn" onclick="tetrisMoveLeft()">◀</button>
                <button class="game-btn" onclick="tetrisRotate()">🔄</button>
                <button class="game-btn" onclick="tetrisMoveRight()">▶</button>
                <button class="game-btn" onclick="tetrisDrop()">⏬</button>
            </div>

            <div style="display: flex; gap: 8px; width: 100%; margin-top: 10px;">
                <button id="tetris-pause-btn" class="zp-btn-action" style="flex: 1; padding: 10px; margin: 0; background: var(--bg); color: var(--text-primary); border: 1px solid var(--border);" onclick="toggleTetrisPause()">⏸️ Pause</button>
                <button class="zp-btn-action" style="flex: 1; padding: 10px; margin: 0;" onclick="startTetris()">🔄 Mulai Baru</button>
            </div>
        </div>
    </div>

    <!-- ── 2. GAME: MONEY 2048 ── -->
    <div id="game-2048-view" class="arcade-card" style="display: none;">
        <div class="game-container">
            <div class="game-score-bar">
                <div class="game-score-pill">
                    <span>Skor Tabungan</span>
                    <strong id="score-2048">0</strong>
                </div>
                <div class="game-score-pill">
                    <span>Target</span>
                    <strong style="color: #EC4899;">Rp 2.048.000</strong>
                </div>
            </div>

            <div id="board-2048" class="grid-2048">
                <!-- 16 tiles dynamically generated -->
            </div>

            <!-- Direction Touch Controls -->
            <div class="game-controls">
                <button class="game-btn" onclick="move2048('left')">◀</button>
                <button class="game-btn" onclick="move2048('up')">▲</button>
                <button class="game-btn" onclick="move2048('down')">▼</button>
                <button class="game-btn" onclick="move2048('right')">▶</button>
            </div>

            <button class="zp-btn-action" style="margin-top: 12px; padding: 10px;" onclick="init2048()">🔄 Reset Papan</button>
        </div>
    </div>

    <!-- ── 3. GAME: COIN CATCHER ── -->
    <div id="game-coin-view" class="arcade-card" style="display: none;">
        <div class="game-container">
            <div class="game-score-bar">
                <div class="game-score-pill">
                    <span>Koin</span>
                    <strong id="coin-score">0</strong>
                </div>
                <div class="game-score-pill">
                    <span>Nyawa</span>
                    <strong id="coin-lives" style="color: #EF4444;">❤️❤️❤️</strong>
                </div>
                <div class="game-score-pill">
                    <span>Tertinggi</span>
                    <strong id="coin-high">0</strong>
                </div>
            </div>

            <canvas id="coin-canvas" class="game-canvas" width="280" height="380"></canvas>

            <div class="game-controls">
                <button class="game-btn" onmousedown="coinMoveLeft(true)" onmouseup="coinMoveLeft(false)" ontouchstart="coinMoveLeft(true)" ontouchend="coinMoveLeft(false)">◀ Geser Kiri</button>
                <button class="game-btn" onmousedown="coinMoveRight(true)" onmouseup="coinMoveRight(false)" ontouchstart="coinMoveRight(true)" ontouchend="coinMoveRight(false)">Geser Kanan ▶</button>
            </div>

            <button class="zp-btn-action" style="margin-top: 12px; padding: 10px;" onclick="startCoinCatcher()">🔄 Mulai Lagi</button>
        </div>
    </div>

</div>

<script>
function switchGame(gameId) {
    document.querySelectorAll('.arcade-hub-grid .arcade-game-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('game-tetris-view').style.display = 'none';
    document.getElementById('game-2048-view').style.display = 'none';
    document.getElementById('game-coin-view').style.display = 'none';

    document.getElementById('btn-tab-' + gameId).classList.add('active');
    document.getElementById('game-' + gameId + '-view').style.display = 'flex';

    if (gameId === 'tetris') startTetris();
    if (gameId === '2048') init2048();
    if (gameId === 'coin') startCoinCatcher();
}

// ─────────────────────────────────────────────────────────────
// 1. TETRIS ENGINE
// ─────────────────────────────────────────────────────────────
const tCanvas = document.getElementById('tetris-canvas');
const tCtx = tCanvas.getContext('2d');
const COLS = 10, ROWS = 18, BLOCK = 20;
let tGrid = [], tScore = 0, tLines = 0, tLevel = 1, tHigh = 0;
let tCurrent, tTimer = null, tPaused = false;

const SHAPES = [
    [[1,1,1,1]], // I
    [[1,1],[1,1]], // O
    [[0,1,0],[1,1,1]], // T
    [[1,0,0],[1,1,1]], // L
    [[0,0,1],[1,1,1]], // J
    [[0,1,1],[1,1,0]], // S
    [[1,1,0],[0,1,1]]  // Z
];
const COLORS = ['#38BDF8', '#FBBF24', '#A855F7', '#FB923C', '#3B82F6', '#34D399', '#EF4444'];

function startTetris() {
    tGrid = Array.from({length: ROWS}, () => Array(COLS).fill(0));
    tScore = 0; tLines = 0; tLevel = 1; tPaused = false;
    tHigh = parseInt(localStorage.getItem('duitku_tetris_high') || '0');
    document.getElementById('tetris-high').innerText = tHigh;
    document.getElementById('tetris-score').innerText = '0';
    document.getElementById('tetris-level').innerText = '1';
    spawnTetrisPiece();
    if (tTimer) clearInterval(tTimer);
    tTimer = setInterval(tetrisTick, 600);
    drawTetris();
}

function spawnTetrisPiece() {
    const idx = Math.floor(Math.random() * SHAPES.length);
    tCurrent = {
        shape: SHAPES[idx],
        color: COLORS[idx],
        x: Math.floor(COLS / 2) - Math.floor(SHAPES[idx][0].length / 2),
        y: 0
    };
    if (tetrisCollides(tCurrent.x, tCurrent.y, tCurrent.shape)) {
        // Game Over
        clearInterval(tTimer);
        alert('Game Over! Skor Anda: ' + tScore);
        if (tScore > tHigh) {
            localStorage.setItem('duitku_tetris_high', tScore);
            document.getElementById('tetris-high').innerText = tScore;
        }
    }
}

function tetrisCollides(x, y, shape) {
    for (let r = 0; r < shape.length; r++) {
        for (let c = 0; c < shape[r].length; c++) {
            if (shape[r][c]) {
                const newX = x + c;
                const newY = y + r;
                if (newX < 0 || newX >= COLS || newY >= ROWS) return true;
                if (newY >= 0 && tGrid[newY][newX]) return true;
            }
        }
    }
    return false;
}

function tetrisTick() {
    if (tPaused) return;
    if (!tetrisCollides(tCurrent.x, tCurrent.y + 1, tCurrent.shape)) {
        tCurrent.y++;
    } else {
        // Lock
        for (let r = 0; r < tCurrent.shape.length; r++) {
            for (let c = 0; c < tCurrent.shape[r].length; c++) {
                if (tCurrent.shape[r][c]) {
                    tGrid[tCurrent.y + r][tCurrent.x + c] = tCurrent.color;
                }
            }
        }
        // Clear lines
        let cleared = 0;
        for (let r = ROWS - 1; r >= 0; r--) {
            if (tGrid[r].every(val => val !== 0)) {
                tGrid.splice(r, 1);
                tGrid.unshift(Array(COLS).fill(0));
                cleared++;
                r++;
            }
        }
        if (cleared > 0) {
            tScore += cleared * 100 * tLevel;
            tLines += cleared;
            tLevel = Math.floor(tLines / 10) + 1;
            document.getElementById('tetris-score').innerText = tScore;
            document.getElementById('tetris-level').innerText = tLevel;
        }
        spawnTetrisPiece();
    }
    drawTetris();
}

function drawTetris() {
    tCtx.fillStyle = '#0B1120';
    tCtx.fillRect(0, 0, tCanvas.width, tCanvas.height);

    // Draw Grid
    for (let r = 0; r < ROWS; r++) {
        for (let c = 0; c < COLS; c++) {
            if (tGrid[r][c]) {
                tCtx.fillStyle = tGrid[r][c];
                tCtx.fillRect(c * BLOCK + 1, r * BLOCK + 1, BLOCK - 2, BLOCK - 2);
            }
        }
    }

    // Draw Current
    if (tCurrent) {
        tCtx.fillStyle = tCurrent.color;
        for (let r = 0; r < tCurrent.shape.length; r++) {
            for (let c = 0; c < tCurrent.shape[r].length; c++) {
                if (tCurrent.shape[r][c]) {
                    tCtx.fillRect((tCurrent.x + c) * BLOCK + 1, (tCurrent.y + r) * BLOCK + 1, BLOCK - 2, BLOCK - 2);
                }
            }
        }
    }
}

function tetrisMoveLeft() {
    if (!tetrisCollides(tCurrent.x - 1, tCurrent.y, tCurrent.shape)) {
        tCurrent.x--;
        drawTetris();
    }
}
function tetrisMoveRight() {
    if (!tetrisCollides(tCurrent.x + 1, tCurrent.y, tCurrent.shape)) {
        tCurrent.x++;
        drawTetris();
    }
}
function tetrisDrop() {
    if (!tetrisCollides(tCurrent.x, tCurrent.y + 1, tCurrent.shape)) {
        tCurrent.y++;
        drawTetris();
    }
}
function tetrisRotate() {
    const rotated = tCurrent.shape[0].map((_, i) => tCurrent.shape.map(row => row[i]).reverse());
    if (!tetrisCollides(tCurrent.x, tCurrent.y, rotated)) {
        tCurrent.shape = rotated;
        drawTetris();
    }
}
function toggleTetrisPause() {
    tPaused = !tPaused;
    document.getElementById('tetris-pause-btn').innerText = tPaused ? '▶️ Lanjutkan' : '⏸️ Pause';
}

// ─────────────────────────────────────────────────────────────
// 2. MONEY 2048 ENGINE
// ─────────────────────────────────────────────────────────────
let b2048 = [], score2048 = 0;
const RUPIAH_VALS = {
    2: '2rb', 4: '4rb', 8: '8rb', 16: '16rb', 32: '32rb', 64: '64rb',
    128: '128rb', 256: '256rb', 512: '512rb', 1024: '1Jt', 2048: '2Jt'
};

function init2048() {
    b2048 = Array.from({length: 4}, () => Array(4).fill(0));
    score2048 = 0;
    addRandomTile2048();
    addRandomTile2048();
    render2048();
}

function addRandomTile2048() {
    const empty = [];
    for (let r = 0; r < 4; r++) {
        for (let c = 0; c < 4; c++) {
            if (b2048[r][c] === 0) empty.push({r, c});
        }
    }
    if (empty.length > 0) {
        const spot = empty[Math.floor(Math.random() * empty.length)];
        b2048[spot.r][spot.c] = Math.random() < 0.9 ? 2 : 4;
    }
}

function render2048() {
    const board = document.getElementById('board-2048');
    board.innerHTML = '';
    document.getElementById('score-2048').innerText = 'Rp ' + (score2048 * 1000).toLocaleString('id-ID');

    for (let r = 0; r < 4; r++) {
        for (let c = 0; c < 4; c++) {
            const val = b2048[r][c];
            const tile = document.createElement('div');
            tile.className = 'tile-2048' + (val > 0 ? ' t-' + val : '');
            tile.innerText = val > 0 ? (RUPIAH_VALS[val] || val) : '';
            board.appendChild(tile);
        }
    }
}

function move2048(dir) {
    let moved = false;
    const slide = row => {
        let arr = row.filter(x => x !== 0);
        for (let i = 0; i < arr.length - 1; i++) {
            if (arr[i] === arr[i + 1]) {
                arr[i] *= 2;
                score2048 += arr[i];
                arr.splice(i + 1, 1);
                moved = true;
            }
        }
        while (arr.length < 4) arr.push(0);
        return arr;
    };

    if (dir === 'left') {
        for (let r = 0; r < 4; r++) {
            const row = b2048[r];
            const newRow = slide(row);
            if (JSON.stringify(row) !== JSON.stringify(newRow)) moved = true;
            b2048[r] = newRow;
        }
    } else if (dir === 'right') {
        for (let r = 0; r < 4; r++) {
            const row = [...b2048[r]].reverse();
            const newRow = slide(row).reverse();
            if (JSON.stringify(b2048[r]) !== JSON.stringify(newRow)) moved = true;
            b2048[r] = newRow;
        }
    } else if (dir === 'up') {
        for (let c = 0; c < 4; c++) {
            const col = [b2048[0][c], b2048[1][c], b2048[2][c], b2048[3][c]];
            const newCol = slide(col);
            if (JSON.stringify(col) !== JSON.stringify(newCol)) moved = true;
            for (let r = 0; r < 4; r++) b2048[r][c] = newCol[r];
        }
    } else if (dir === 'down') {
        for (let c = 0; c < 4; c++) {
            const col = [b2048[3][c], b2048[2][c], b2048[1][c], b2048[0][c]];
            const newCol = slide(col).reverse();
            if (JSON.stringify([b2048[0][c], b2048[1][c], b2048[2][c], b2048[3][c]]) !== JSON.stringify(newCol)) moved = true;
            for (let r = 0; r < 4; r++) b2048[r][c] = newCol[r];
        }
    }

    if (moved) {
        addRandomTile2048();
        render2048();
    }
}

// ─────────────────────────────────────────────────────────────
// 3. COIN CATCHER ENGINE
// ─────────────────────────────────────────────────────────────
const cCanvas = document.getElementById('coin-canvas');
const cCtx = cCanvas.getContext('2d');
let cScore = 0, cLives = 3, cHigh = 0, cPlayerX = 110, cTimer = null;
let cItems = [], cMoveLeft = false, cMoveRight = false;

function startCoinCatcher() {
    cScore = 0; cLives = 3; cPlayerX = 110; cItems = [];
    cHigh = parseInt(localStorage.getItem('duitku_coin_high') || '0');
    document.getElementById('coin-high').innerText = cHigh;
    document.getElementById('coin-score').innerText = '0';
    document.getElementById('coin-lives').innerText = '❤️❤️❤️';
    if (cTimer) clearInterval(cTimer);
    cTimer = setInterval(coinTick, 1000 / 60);
}

function coinMoveLeft(active) { cMoveLeft = active; }
function coinMoveRight(active) { cMoveRight = active; }

function coinTick() {
    // Move Player
    if (cMoveLeft) cPlayerX = Math.max(0, cPlayerX - 5);
    if (cMoveRight) cPlayerX = Math.min(cCanvas.width - 60, cPlayerX + 5);

    // Spawn items
    if (Math.random() < 0.04) {
        cItems.push({
            x: Math.random() * (cCanvas.width - 30),
            y: 0,
            speed: 2 + Math.random() * 3,
            isBomb: Math.random() < 0.25
        });
    }

    // Update items
    for (let i = cItems.length - 1; i >= 0; i--) {
        const it = cItems[i];
        it.y += it.speed;

        // Collision with basket
        if (it.y >= cCanvas.height - 40 && it.y <= cCanvas.height - 10) {
            if (it.x + 20 >= cPlayerX && it.x <= cPlayerX + 60) {
                if (it.isBomb) {
                    cLives--;
                    document.getElementById('coin-lives').innerText = '❤️'.repeat(Math.max(0, cLives));
                    if (cLives <= 0) {
                        clearInterval(cTimer);
                        alert('Game Over! Koin yang Anda kumpulkan: ' + cScore);
                        if (cScore > cHigh) {
                            localStorage.setItem('duitku_coin_high', cScore);
                            document.getElementById('coin-high').innerText = cScore;
                        }
                    }
                } else {
                    cScore += 10;
                    document.getElementById('coin-score').innerText = cScore;
                }
                cItems.splice(i, 1);
                continue;
            }
        }

        // Missed item
        if (it.y > cCanvas.height) {
            cItems.splice(i, 1);
        }
    }

    drawCoinCatcher();
}

function drawCoinCatcher() {
    cCtx.fillStyle = '#0B1120';
    cCtx.fillRect(0, 0, cCanvas.width, cCanvas.height);

    // Draw Basket Player
    cCtx.fillStyle = '#10B981';
    cCtx.beginPath();
    cCtx.roundRect(cPlayerX, cCanvas.height - 24, 60, 16, 8);
    cCtx.fill();
    cCtx.fillStyle = '#ffffff';
    cCtx.font = 'bold 10px sans-serif';
    cCtx.fillText('DOMPET', cPlayerX + 10, cCanvas.height - 12);

    // Draw Items
    cItems.forEach(it => {
        if (it.isBomb) {
            cCtx.fillStyle = '#EF4444';
            cCtx.beginPath();
            cCtx.arc(it.x + 12, it.y + 12, 10, 0, Math.PI * 2);
            cCtx.fill();
            cCtx.fillStyle = '#ffffff';
            cCtx.font = '10px sans-serif';
            cCtx.fillText('💣', it.x + 5, it.y + 16);
        } else {
            cCtx.fillStyle = '#F59E0B';
            cCtx.beginPath();
            cCtx.arc(it.x + 12, it.y + 12, 10, 0, Math.PI * 2);
            cCtx.fill();
            cCtx.fillStyle = '#ffffff';
            cCtx.font = 'bold 9px sans-serif';
            cCtx.fillText('Rp', it.x + 6, it.y + 16);
        }
    });
}

// Global Keyboard Handler
document.addEventListener('keydown', e => {
    if (document.getElementById('game-tetris-view').style.display !== 'none') {
        if (e.key === 'ArrowLeft') tetrisMoveLeft();
        if (e.key === 'ArrowRight') tetrisMoveRight();
        if (e.key === 'ArrowDown') tetrisDrop();
        if (e.key === 'ArrowUp') tetrisRotate();
    } else if (document.getElementById('game-2048-view').style.display !== 'none') {
        if (e.key === 'ArrowLeft') move2048('left');
        if (e.key === 'ArrowRight') move2048('right');
        if (e.key === 'ArrowUp') move2048('up');
        if (e.key === 'ArrowDown') move2048('down');
    } else if (document.getElementById('game-coin-view').style.display !== 'none') {
        if (e.key === 'ArrowLeft') cPlayerX = Math.max(0, cPlayerX - 15);
        if (e.key === 'ArrowRight') cPlayerX = Math.min(cCanvas.width - 60, cPlayerX + 15);
    }
});

// Initial Start
document.addEventListener('DOMContentLoaded', () => {
    startTetris();
});
</script>
<?= $this->endSection() ?>
