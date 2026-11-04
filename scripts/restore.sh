#!/usr/bin/env bash
# Restore database dari file pg_dump (-Fc).
set -euo pipefail
cd "$(dirname "$0")/.."

if [ $# -lt 1 ]; then
  echo "pemakaian: bash scripts/restore.sh backups/<file>.dump" >&2
  exit 1
fi
DUMP="$1"
[ -f "$DUMP" ] || { echo "file tidak ada: $DUMP" >&2; exit 1; }

DB_NAME=$(grep -E '^DB_DATABASE=' .env | head -1 | cut -d= -f2- | tr -d '"')
DB_HOST=$(grep -E '^DB_HOST=' .env | head -1 | cut -d= -f2- | tr -d '"')
DB_USER=$(grep -E '^DB_USERNAME=' .env | head -1 | cut -d= -f2- | tr -d '"')

echo "PERINGATAN: database $DB_NAME akan ditimpa dari $DUMP"
read -r -p "Ketik RESTORE untuk melanjutkan: " JAWAB
[ "$JAWAB" = "RESTORE" ] || { echo "Dibatalkan."; exit 1; }

pg_restore -h "$DB_HOST" -U "$DB_USER" --clean --if-exists --no-owner -d "$DB_NAME" "$DUMP"
php artisan migrate --force
echo "restore selesai."
