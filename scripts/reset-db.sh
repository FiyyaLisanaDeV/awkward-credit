#!/usr/bin/env bash
# Reset database — HANYA untuk lingkungan lokal.
set -euo pipefail
cd "$(dirname "$0")/.."

APP_ENV_VALUE=$(grep -E '^APP_ENV=' .env | head -1 | cut -d= -f2- | tr -d '"')
if [ "$APP_ENV_VALUE" != "local" ]; then
  echo "DITOLAK: script ini hanya untuk APP_ENV=local (sekarang: $APP_ENV_VALUE)" >&2
  exit 1
fi

echo "PERINGATAN: seluruh data pada database aplikasi akan dihapus."
echo "Database: $(grep -E '^DB_DATABASE=' .env | cut -d= -f2-)"
read -r -p "Ketik RESET untuk melanjutkan: " JAWAB
if [ "$JAWAB" != "RESET" ]; then
  echo "Dibatalkan."
  exit 1
fi

php artisan migrate:fresh --seed --force
php artisan optimize:clear >/dev/null
echo "Selesai."
