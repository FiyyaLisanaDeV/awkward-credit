#!/usr/bin/env bash
# Backup database + storage privat.
set -euo pipefail
cd "$(dirname "$0")/.."

DB_NAME=$(grep -E '^DB_DATABASE=' .env | head -1 | cut -d= -f2- | tr -d '"')
DB_HOST=$(grep -E '^DB_HOST=' .env | head -1 | cut -d= -f2- | tr -d '"')
DB_USER=$(grep -E '^DB_USERNAME=' .env | head -1 | cut -d= -f2- | tr -d '"')
DB_HOST=${DB_HOST:-127.0.0.1}

TS=$(date +%Y%m%d-%H%M%S)
mkdir -p backups

DUMP="backups/${DB_NAME}-${TS}.dump"
pg_dump -h "$DB_HOST" -U "$DB_USER" -Fc -d "$DB_NAME" -f "$DUMP"
echo "database  -> $DUMP"

TAR="backups/storage-private-${TS}.tar.gz"
tar -czf "$TAR" -C storage/app private
echo "storage   -> $TAR"

echo "selesai. Simpan juga APP_KEY dari .env — tanpa itu file PII tidak dapat dibuka."
