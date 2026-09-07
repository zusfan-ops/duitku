#!/usr/bin/env bash
# ==============================================================================
# Runner Deploy Duitku (Linux server / aaPanel)
# 
# Cara pakai (dua-duanya sama):
#   bash deploy-run.sh                       # jalankan sebagai user yang punya sudo
#   sudo bash deploy-run.sh                  # atau langsung sebagai root
#
# Script ini menjalankan deploy.sh SEBAGAI user aplikasi ('www') supaya semua
# folder milik www (writable/, .git, dst) dapat ditulis, dan set HOME=/tmp agar
# git config global tidak menabrak file ~/.gitconfig milik user lain.
# Jika sudah root / sudah memiliki akses tulis penuh, deploy.sh dijalankan langsung.
# ==============================================================================

set -Eeuo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

APP_USER="${APP_USER:-www}"

echo "==> Menyiapkan user deploy: '$APP_USER'"

# Cek apakah kita sudah punya akses tulis penuh (root, atau pemilik folder)
WRITABLE_TEST="$PROJECT_DIR/writable/.deploy_write_test"
if touch "$WRITABLE_TEST" 2>/dev/null; then
    rm -f "$WRITABLE_TEST"
    echo "    Folder writable dapat ditulis langsung → jalankan deploy.sh tanpa sudo."
    exec bash "$PROJECT_DIR/deploy.sh"
fi

# Cek sudo tersedia
if ! command -v sudo >/dev/null 2>&1; then
    echo "ERROR: Folder writable tidak dapat ditulis oleh user saat ini, dan 'sudo' tidak tersedia." >&2
    echo "       Jalankan script ini sebagai pemilik folder atau sebagai root." >&2
    echo "       Perbaiki juga kepemilikan: sudo chown -R www:www \"$PROJECT_DIR\"" >&2
    exit 1
fi

# Cek user aplikasi ada
if ! id "$APP_USER" >/dev/null 2>&1; then
    echo "ERROR: User aplikasi '$APP_USER' tidak ditemukan. Set APP_USER=<nama_user> lalu ulangi." >&2
    exit 1
fi

echo "    Elevasi ke user '$APP_USER' (sudo) untuk menjalankan deploy.sh..."

# Siapkan safe.directory untuk user aplikasi (HOME=/tmp agar tidak menumpuk ke ~ user lain)
sudo -u "$APP_USER" env HOME=/tmp git config --global --add safe.directory "$PROJECT_DIR" 2>/dev/null || true

# Tempatkan komposisi: file sementara runner + deploy.sh dijalankan sebagai APP_USER
sudo -u "$APP_USER" env HOME=/tmp bash "$PROJECT_DIR/deploy.sh"