#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

info() { printf '\033[1;32m==>\033[0m %s\n' "$*"; }
err()  { printf '\033[1;31mERROR:\033[0m %s\n' "$*" >&2; }

need() { command -v "$1" >/dev/null 2>&1 || { err "$1 tidak ditemukan di PATH"; exit 1; }; }

need php
need composer
need psql
need createdb

php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
  || { err "PHP >= 8.3 dibutuhkan (sekarang $(php -r 'echo PHP_VERSION;'))"; exit 1; }
php -r 'exit(extension_loaded("pdo_pgsql") ? 0 : 1);' \
  || { err "ekstensi pdo_pgsql tidak tersedia"; exit 1; }

if [ ! -f .env ]; then
  info "membuat .env dari .env.example"
  cp .env.example .env
  php artisan key:generate --ansi
fi

if [ ! -d vendor ]; then
  info "composer install"
  COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction
fi

DB_NAME=$(grep -E '^DB_DATABASE=' .env | head -1 | cut -d= -f2- | tr -d '"')
DB_NAME=${DB_NAME:-loan_analytic_bpr}
DB_HOST=$(grep -E '^DB_HOST=' .env | head -1 | cut -d= -f2- | tr -d '"')
DB_HOST=${DB_HOST:-127.0.0.1}

for db in "$DB_NAME" "${DB_NAME}_test"; do
  if psql -h "$DB_HOST" -d postgres -tAc "select 1 from pg_database where datname='$db'" | grep -q 1; then
    info "database $db sudah ada"
  else
    info "membuat database $db"
    createdb -h "$DB_HOST" "$db"
  fi
done

info "migrasi"
php artisan migrate --force

info "seeder"
php artisan db:seed --force

php artisan storage:link >/dev/null 2>&1 || true
mkdir -p storage/app/private/documents storage/app/private/pii

info "membersihkan cache"
php artisan optimize:clear >/dev/null

info "selesai"
cat <<'EOF'

Jalankan aplikasi:
  php artisan serve                 # http://127.0.0.1:8000
  herd link loan-analytic-bpr       # http://loan-analytic-bpr.test

Verifikasi cepat:
  bash scripts/verify.sh --quick
EOF
