#!/usr/bin/env bash
# ==============================================================================
# Runner Deploy Duitku (Linux server / aaPanel)
#
# Cara pakai (dua-duanya sama):
#   bash deploy-run.sh                       # jalankan sebagai user yang punya sudo
#   sudo bash deploy-run.sh                  # atau langsung sebagai root
#
# Script ini menjalankan deploy.sh SEBAGAI pemilik project (biasanya 'www'),
# karena folder .git/ sering hanya writable oleh owner. Untuk itu ia:
#   1. deteksi pemilik project (dari .git atau folder project),
#   2. jika user saat ini bukan owner dan ada sudo → elevate ke owner
#      dengan HOME=/tmp (agar git config tidak menabrak ~/.gitconfig user lain),
#   3. jika sudah owner/root → jalankan deploy.sh langsung.
# ==============================================================================

set -Eeuo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

# Deteksi pemilik project (prioritas: .git, lalu folder project)
OWNER="$(stat -c '%U' "$PROJECT_DIR/.git" 2>/dev/null || stat -c '%U' "$PROJECT_DIR" 2>/dev/null || echo www)"
CURRENT_USER="$(id -un)"
echo "==> Project : $PROJECT_DIR"
echo "==> Pemilik : $OWNER"
echo "==> User    : $CURRENT_USER"

# 1) Sudah root → langsung
if [ "$CURRENT_USER" = "root" ]; then
    echo "    Dijalankan sebagai root → deploy.sh langsung."
    exec bash "$PROJECT_DIR/deploy.sh"
fi

# 2) Cek apakah user saat ini bisa menulis .git (artinya adalah pemilik/berhak)
GIT_TEST="$PROJECT_DIR/.git/.deploy_write_test"
if touch "$GIT_TEST" 2>/dev/null; then
    rm -f "$GIT_TEST"
    echo "    User saat ini dapat menulis .git → deploy.sh langsung tanpa sudo."
    exec bash "$PROJECT_DIR/deploy.sh"
fi

# 3) Tidak bisa menulis .git → perlu elevate ke owner (atau sudo)
if ! command -v sudo >/dev/null 2>&1; then
    echo "ERROR: tidak dapat menulis .git dan 'sudo' tidak tersedia." >&2
    echo "       Jalankan sebagai root atau perbaiki kepemilikan: sudo chown -R www:www \"$PROJECT_DIR\"" >&2
    exit 1
fi

if ! id "$OWNER" >/dev/null 2>&1; then
    echo "ERROR: pemilik '$OWNER' tidak ditemukan. Set OWNER=<nama_user> lalu ulangi." >&2
    exit 1
fi

echo "    Elevate ke '$OWNER' via sudo untuk menjalankan deploy.sh..."

# Sudo helper: dukung SUDO_PASSWORD untuk mode non-interaktif (cron/SSH satu baris).
if [ -n "${SUDO_PASSWORD:-}" ]; then
    run_sudo() { echo "$SUDO_PASSWORD" | sudo -S "$@"; }
else
    run_sudo() { sudo "$@"; }
fi

run_sudo -u "$OWNER" env HOME=/tmp git config --global --add safe.directory "$PROJECT_DIR" 2>/dev/null || true
exec run_sudo -u "$OWNER" env HOME=/tmp bash "$PROJECT_DIR/deploy.sh"