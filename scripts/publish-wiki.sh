#!/usr/bin/env bash
# publish-wiki.sh — publikasikan manual book ke GitHub Wiki repo ini.
#
# Prasyarat: wiki HARUS sudah punya minimal 1 halaman (buat "Home" sekali via web UI:
#   https://github.com/FiyyaLisanaDeV/awkward-credit/wiki -> "Create the first page").
# Setelah itu skrip ini aman dijalankan berulang.
#
# Pakai:
#   GH_TOKEN=<token> bash scripts/publish-wiki.sh
# atau taruh token di file (TIDAK di-commit): project/../Github Fine Grained token.txt
set -euo pipefail

REPO_DIR="$(cd "$(dirname "$0")/.." && pwd)"
OWNER_REPO="${WIKI_REPO:-FiyyaLisanaDeV/awkward-credit}"
TOKEN="${GH_TOKEN:-}"

if [ -z "$TOKEN" ]; then
  for f in "$REPO_DIR/Github Fine Grained token.txt" "$REPO_DIR/.gh-token"; do
    [ -f "$f" ] && TOKEN="$(tr -d '\r\n' < "$f")" && break
  done
fi

if [ -z "$TOKEN" ]; then
  echo "ERROR: token tidak ditemukan. Set GH_TOKEN atau sediakan file token." >&2
  exit 1
fi

echo "==> membangun halaman wiki dari manual book"
python3 "$REPO_DIR/project/scripts/build-wiki.py"

WIKI_URL="https://x-access-token:${TOKEN}@github.com/${OWNER_REPO}.wiki.git"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

echo "==> clone wiki"
if ! git clone --depth 1 "$WIKI_URL" "$WORK/wiki" 2>/dev/null; then
  echo "ERROR: wiki belum ada / belum diinisialisasi." >&2
  echo "Buka https://github.com/${OWNER_REPO}/wiki lalu buat halaman pertama (mis. 'Home')," >&2
  echo "baru jalankan skrip ini lagi." >&2
  exit 2
fi

echo "==> salin halaman"
cp "$REPO_DIR"/wiki/*.md "$WORK/wiki/"

cd "$WORK/wiki"
git -c user.name="wiki-bot" -c user.email="wiki-bot@users.noreply.github.com" add -A
if git diff --cached --quiet; then
  echo "==> tidak ada perubahan"
  exit 0
fi
git -c user.name="wiki-bot" -c user.email="wiki-bot@users.noreply.github.com" \
  commit -q -m "docs(wiki): sync manual book (Bab 1-23) + lampiran"
git push -q "$WIKI_URL" HEAD:master 2>/dev/null || git push -q "$WIKI_URL" HEAD:main
echo "==> wiki diperbarui: https://github.com/${OWNER_REPO}/wiki"
