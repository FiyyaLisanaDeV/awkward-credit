#!/usr/bin/env bash
# manual-snapshot.sh — snapshot angka target (repo app) untuk memperbarui MANIFEST/manual.
# Pakai: bash scripts/manual-snapshot.sh [path-repo-app]
# Default path-repo-app: <workspace>/APP BPR/loan-analytic-bpr (relatif ke skrip ini)
set -uo pipefail

DEFAULT_REPO="$(cd "$(dirname "$0")/../../.." 2>/dev/null && pwd)/APP BPR/loan-analytic-bpr"
APP_REPO="${1:-${APP_REPO:-$DEFAULT_REPO}}"

if [ ! -d "$APP_REPO" ]; then
  echo "ERROR: repo app tidak ditemukan: $APP_REPO" >&2
  exit 1
fi

cd "$APP_REPO"

echo "# Manual Snapshot — $(date +%Y-%m-%dT%H:%M%z)"
echo "repo: $APP_REPO"
echo

echo "## Git"
echo "branch   : $(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo '-')"
echo "commit   : $(git rev-parse --short HEAD 2>/dev/null || echo '-')"
echo "tag      : $(git describe --tags --abbrev=0 2>/dev/null || echo '-')"
echo "dirty    : $(git status --porcelain 2>/dev/null | wc -l | tr -d ' ') berkas berubah"
echo

echo "## Struktur"
echo "migrasi  : $(ls database/migrations 2>/dev/null | wc -l | tr -d ' ')"
echo "model    : $(ls app/Models/*.php 2>/dev/null | wc -l | tr -d ' ')"
echo "enum     : $(ls app/Enums/*.php 2>/dev/null | wc -l | tr -d ' ')"
echo "controller API : $(ls app/Http/Controllers/Api/V1/*.php 2>/dev/null | wc -l | tr -d ' ')"
echo "service  : $(find app/Services -name '*.php' 2>/dev/null | wc -l | tr -d ' ')"
echo "command  : $(ls app/Console/Commands/*.php 2>/dev/null | wc -l | tr -d ' ')"
echo "livewire : $(ls app/Livewire/*.php 2>/dev/null | wc -l | tr -d ' ')"
echo "test file: $(find tests -name '*Test.php' 2>/dev/null | wc -l | tr -d ' ')"
echo

if command -v php >/dev/null 2>&1; then
  echo "## Artisan (opsional; butuh DB untuk sebagian perintah)"
  echo "laravel  : $(php artisan --version 2>/dev/null || echo 'n/a')"
  echo "rute     : $(php artisan route:list --json 2>/dev/null | php -r '$r=json_decode(stream_get_contents(STDIN),true); echo is_array($r)?count($r):"n/a";' 2>/dev/null)"
  echo "engine   : $(php -r 'require "vendor/autoload.php"; $c=require "config/loan.php"; echo $c["engine_version"];' 2>/dev/null || echo 'n/a')"
else
  echo "## Artisan: dilewati (php tidak ada)"
fi

echo
echo "> Salin angka di atas ke docs/manual/MANIFEST.md dan bandingkan dengan tabel snapshot."
echo "> Jalankan 'bash scripts/verify.sh --phase' di repo app untuk baseline QA."
