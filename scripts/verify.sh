#!/usr/bin/env bash
# Verifikasi bertingkat: --quick | --phase | --full
set -uo pipefail
cd "$(dirname "$0")/.."

MODE="${1:---phase}"
FAIL=0

step() { printf '\n\033[1;34m▶ %s\033[0m\n' "$*"; }
ok()   { printf '  \033[1;32mOK\033[0m %s\n' "$*"; }
bad()  { printf '  \033[1;31mGAGAL\033[0m %s\n' "$*"; FAIL=1; }

run() {
  local label="$1"; shift
  if "$@"; then ok "$label"; else bad "$label"; fi
}

has_cmd() { php artisan list --raw 2>/dev/null | awk '{print $1}' | grep -qx "$1"; }

step "Lingkungan"
php -v | head -1
php artisan --version
git status -sb 2>/dev/null | head -3 || echo "(belum git)"

step "Database"
if php artisan migrate:status >/dev/null 2>&1; then
  php artisan migrate:status | tail -3
  ok "koneksi database"
else
  bad "koneksi database / migrasi"
fi

step "Rute & test terdaftar"
php artisan route:list --json | php -r 'echo "rute: ".count(json_decode(stream_get_contents(STDIN), true)).PHP_EOL;' 2>/dev/null \
  || bad "route:list"
php -r '$n=0; foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator("tests")) as $f) { if ($f->isFile() && str_ends_with($f->getFilename(), "Test.php")) $n++; } echo "file test: $n\n";'

if [ "$MODE" = "--quick" ]; then
  has_cmd state:check && run "state:check" php artisan state:check
  echo; [ "$FAIL" -eq 0 ] && { echo "VERIFY: OK (quick)"; exit 0; } || { echo "VERIFY: GAGAL (quick)"; exit 1; }
fi

step "Gaya kode (Pint)"
run "pint --test" ./vendor/bin/pint --test

step "Analisis statis (PHPStan level 5)"
run "phpstan app" ./vendor/bin/phpstan analyse --no-progress --memory-limit=1G

if [ -f phpstan-services.neon ] && [ -n "$(find app/Services app/Enums app/Domain -name '*.php' -print -quit 2>/dev/null)" ]; then
  step "Analisis statis (PHPStan level 8: Services/Enums/Domain)"
  run "phpstan services" ./vendor/bin/phpstan analyse -c phpstan-services.neon --no-progress --memory-limit=1G
fi

step "Test"
run "pest" php artisan test --compact

step "Penjaga"
has_cmd hardcode:audit && run "hardcode:audit" php artisan hardcode:audit
has_cmd docs:check && run "docs:check" php artisan docs:check
has_cmd state:check && run "state:check" php artisan state:check

if [ "$MODE" = "--full" ]; then
  step "Dokumen (strict)"
  has_cmd docs:check && run "docs:check --strict" php artisan docs:check --strict
fi

echo
if [ "$FAIL" -eq 0 ]; then
  echo "VERIFY: OK ($MODE)"
  exit 0
fi
echo "VERIFY: GAGAL ($MODE)"
exit 1
