#!/usr/bin/env bash
#
# ==============================================================================
# Deploy Script untuk Duitku (CodeIgniter 4)
# Repo: https://github.com/zusfan-ops/duitku.git
#
# Desain: TAHAN "git drift", konflik lokal, dan branch divergen.
# Mengamankan konfigurasi database (app/Config/Database.php & .env)
# agar di-ignore dan TIDAK PERNAH tertimpa saat update dari GitHub.
# ==============================================================================

set -Eeuo pipefail

# Deteksi folder project secara otomatis (lokasi script ini berada)
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

REPO_URL="https://github.com/zusfan-ops/duitku.git"
BRANCH="main"
DB_CONFIG_FILE="app/Config/Database.php"

echo "========================================================"
echo "🚀 Memulai Deployment Duitku di: $PROJECT_DIR"
echo "Tanggal : $(date '+%Y-%m-%d %H:%M:%S')"
echo "========================================================"

# ------------------------------------------------------------------------------
# 1. Pastikan Remote Origin mengarah ke repo GitHub yang benar
# ------------------------------------------------------------------------------
echo "==> [1/7] Memeriksa remote git repository..."
CURRENT_REMOTE=$(git remote get-url origin 2>/dev/null || true)
if [ -z "$CURRENT_REMOTE" ]; then
    echo "    Menambahkan remote origin: $REPO_URL"
    git remote add origin "$REPO_URL"
elif [ "$CURRENT_REMOTE" != "$REPO_URL" ]; then
    echo "    Mengubah remote origin ke: $REPO_URL"
    git remote set-url origin "$REPO_URL"
fi

# ------------------------------------------------------------------------------
# 2. Backup & Amankan Pengaturan Database Server (app/Config/Database.php & .env)
# ------------------------------------------------------------------------------
echo "==> [2/7] Mengamankan pengaturan database server ($DB_CONFIG_FILE)..."
BACKUP_DIR="$PROJECT_DIR/writable/deploy_backups"
mkdir -p "$BACKUP_DIR"

# Lepas status assume-unchanged sementara sebelum operasi git
git update-index --no-assume-unchanged "$DB_CONFIG_FILE" 2>/dev/null || true
git update-index --no-skip-worktree "$DB_CONFIG_FILE" 2>/dev/null || true

# Backup permanen file Database.php server
if [ -f "$PROJECT_DIR/$DB_CONFIG_FILE" ]; then
    cp -p "$PROJECT_DIR/$DB_CONFIG_FILE" "$BACKUP_DIR/Database.php.server"
    # Simpan juga master copy produksi di folder writable
    if [ ! -f "$PROJECT_DIR/writable/Database.php.production" ]; then
        cp -p "$PROJECT_DIR/$DB_CONFIG_FILE" "$PROJECT_DIR/writable/Database.php.production"
    fi
    echo "    ✓ Konfigurasi database server ($DB_CONFIG_FILE) berhasil diamankan."
fi

# Backup .env dan db_config.php jika ada
if [ -f "$PROJECT_DIR/.env" ]; then
    cp -p "$PROJECT_DIR/.env" "$BACKUP_DIR/.env.last"
fi
if [ -f "$PROJECT_DIR/app/Config/db_config.php" ]; then
    cp -p "$PROJECT_DIR/app/Config/db_config.php" "$BACKUP_DIR/db_config.php.last"
fi

# ------------------------------------------------------------------------------
# 3. Bersihkan status git yang menggantung (merge/rebase/cherry-pick lock)
# ------------------------------------------------------------------------------
echo "==> [3/7] Membersihkan sisa proses git jika ada..."
git merge --abort        2>/dev/null || true
git rebase --abort       2>/dev/null || true
git cherry-pick --abort  2>/dev/null || true
rm -f .git/index.lock .git/MERGE_HEAD .git/CHERRY_PICK_HEAD .git/REBASE_HEAD 2>/dev/null || true

# ------------------------------------------------------------------------------
# 4. Fetch dari GitHub & Amankan Perubahan Lokal
# ------------------------------------------------------------------------------
echo "==> [4/7] Mengambil update terbaru dari GitHub ($BRANCH)..."
git fetch --prune origin "$BRANCH"

# Amankan perubahan tracked lokal selain file database ke stash
STASHED=0
if ! git diff --quiet || ! git diff --cached --quiet; then
    STASH_NAME="pre-deploy-$(date +%Y%m%d-%H%M%S)"
    git stash push -m "$STASH_NAME" || true
    STASHED=1
    echo "    ✓ Perubahan lokal tak-commit disimpan ke stash ($STASH_NAME)"
fi

# Amankan commit lokal jika server divergen dengan origin
LOCAL_BACKUP_BRANCH=""
if ! git merge-base --is-ancestor HEAD "origin/$BRANCH" 2>/dev/null; then
    LOCAL_BACKUP_BRANCH="predeploy-backup-$(date +%Y%m%d-%H%M%S)"
    git branch -f "$LOCAL_BACKUP_BRANCH" HEAD 2>/dev/null || true
    echo "    ✓ Commit lokal divergen diselamatkan ke branch: $LOCAL_BACKUP_BRANCH"
fi

# Reset ke versi persis dari origin/main
echo "    Sinkronisasi repo ke origin/$BRANCH..."
git reset --hard "origin/$BRANCH"

# ------------------------------------------------------------------------------
# KEMBALIKAN PENGATURAN DATABASE SERVER & SET AGAR DI-IGNORE OLEH GIT
# ------------------------------------------------------------------------------
echo "    Mengembalikan & meng-ignore pengaturan database server..."
if [ -f "$BACKUP_DIR/Database.php.server" ]; then
    cp -pf "$BACKUP_DIR/Database.php.server" "$PROJECT_DIR/$DB_CONFIG_FILE"
elif [ -f "$PROJECT_DIR/writable/Database.php.production" ]; then
    cp -pf "$PROJECT_DIR/writable/Database.php.production" "$PROJECT_DIR/$DB_CONFIG_FILE"
fi

# Set git agar meng-ignore file Database.php di server (tidak akan dideteksi sebagai perubahan)
git update-index --assume-unchanged "$DB_CONFIG_FILE"
git update-index --skip-worktree "$DB_CONFIG_FILE" 2>/dev/null || true
echo "    ✓ $DB_CONFIG_FILE berhasil di-ignore oleh git."

# Pulihkan .env atau db_config.php jika ada backupnya
if [ ! -f "$PROJECT_DIR/.env" ] && [ -f "$BACKUP_DIR/.env.last" ]; then
    cp -p "$BACKUP_DIR/.env.last" "$PROJECT_DIR/.env"
fi
if [ ! -f "$PROJECT_DIR/app/Config/db_config.php" ] && [ -f "$BACKUP_DIR/db_config.php.last" ]; then
    cp -p "$BACKUP_DIR/db_config.php.last" "$PROJECT_DIR/app/Config/db_config.php"
fi

# ------------------------------------------------------------------------------
# 5. Dependensi Composer (jika composer terpasang di server)
# ------------------------------------------------------------------------------
echo "==> [5/7] Memeriksa dependensi Composer..."
if command -v composer >/dev/null 2>&1; then
    echo "    Menjalankan composer install..."
    composer install --no-dev --optimize-autoloader --no-interaction
else
    echo "    Composer tidak ditemukan di PATH (dilewati)."
fi

# ------------------------------------------------------------------------------
# 6. Database Migrations & Spark Cache Clear
# ------------------------------------------------------------------------------
echo "==> [6/7] Menjalankan migrasi database & pembersihan cache CodeIgniter 4..."
if [ -f "spark" ] && command -v php >/dev/null 2>&1; then
    echo "    Menjalankan migrasi database..."
    php spark migrate --all || true

    echo "    Membersihkan cache aplikasi..."
    php spark cache:clear || true
    php spark optimize || true
else
    echo "    CLI Spark / PHP tidak tersedia via script (dilewati)."
fi

# ------------------------------------------------------------------------------
# 7. Pengaturan Permission Folder Writable
# ------------------------------------------------------------------------------
echo "==> [7/7] Menyesuaikan permission folder writable..."
if [ -d "writable" ]; then
    chmod -R 775 writable 2>/dev/null || chmod -R 777 writable 2>/dev/null || true
    echo "    ✓ Permission folder writable berhasil disesuaikan."
fi

echo "========================================================"
echo "✅ DEPLOY SELESAI!"
echo "Commit aktif : $(git log -1 --oneline)"
echo "Repo         : $REPO_URL"
echo "Database     : $DB_CONFIG_FILE (TETAP AMAN & DI-IGNORE)"
echo "========================================================"

if [ "$STASHED" = "1" ]; then
    echo "ℹ️  CATATAN: Perubahan file lokal tak-commit disimpan di stash (lihat: git stash list)"
fi
if [ -n "$LOCAL_BACKUP_BRANCH" ]; then
    echo "ℹ️  CATATAN: Commit lokal yang belum di push disimpan di branch: $LOCAL_BACKUP_BRANCH"
fi
