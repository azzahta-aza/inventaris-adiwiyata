#!/usr/bin/env bash
# ==============================================================================
# INVENTORY SYSTEM - SERVER DEPLOYMENT AUTOMATION SCRIPT
# ==============================================================================
# Script ini dieksekusi di server production/staging saat deployment berlangsung
# ==============================================================================

set -e

# Warna terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}====================================================${NC}"
echo -e "${BLUE}   INVENTORY ADIWIYATA - SERVER DEPLOYMENT          ${NC}"
echo -e "${BLUE}====================================================${NC}"
echo "Waktu Mulai: $(date '+%Y-%m-%d %H:%M:%S')"

# 1. Tentukan Direktori Proyek
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"
echo -e "${GREEN}[1/5] Lokasi proyek: $PROJECT_DIR${NC}"

# 2. Verifikasi Git Repository
if [ ! -d ".git" ]; then
    echo -e "${RED}[ERROR] Direktori ini bukan git repository!${NC}"
    exit 1
fi

BRANCH="${DEPLOY_BRANCH:-main}"
echo -e "${GREEN}[2/5] Menyelaraskan kode dengan origin/${BRANCH}...${NC}"
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"
echo -e "Commit saat ini: $(git log -1 --format='%h - %s (%cr)')"

# 3. Pengaturan Permissions
echo -e "${GREEN}[3/5] Mengatur permissions file dan folder...${NC}"
find . -type d -exec chmod 755 {} +
find . -type f -exec chmod 644 {} +
if [ -f "deploy.sh" ]; then
    chmod +x deploy.sh
fi

# 4. Validasi Environment (.env)
echo -e "${GREEN}[4/5] Memeriksa file konfigurasi .env...${NC}"
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        echo -e "${YELLOW}[PERINGATAN] .env tidak ditemukan. Menyalin dari .env.example...${NC}"
        cp .env.example .env
        echo -e "${YELLOW}Harap segera perbarui konfigurasi database di file .env!${NC}"
    fi
else
    echo "File .env ditemukan."
fi

# 5. Uji Sintaks PHP (Health Check)
echo -e "${GREEN}[5/5] Melakukan health check sintaks PHP...${NC}"
ERRORS=0
while IFS= read -r file; do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo -e "${RED}[SYNTAX ERROR] $file${NC}"
        ERRORS=$((ERRORS + 1))
    fi
done < <(find . -type f -name "*.php" -not -path "./vendor/*")

if [ $ERRORS -ne 0 ]; then
    echo -e "${RED}[FAIL] Ditemukan $ERRORS kesalahan sintaks PHP pasca deploy!${NC}"
    exit 1
fi

echo -e "${GREEN}====================================================${NC}"
echo -e "${GREEN}   DEPLOYMENT BERHASIL SELESAI TANPA KENDALA!       ${NC}"
echo -e "${GREEN}====================================================${NC}"
echo "Waktu Selesai: $(date '+%Y-%m-%d %H:%M:%S')"
exit 0
