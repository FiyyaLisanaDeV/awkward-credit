#!/usr/bin/env python3
"""build-wiki.py — ubah manual book (project/docs/manual) menjadi halaman wiki GitHub.

Output: <repo>/wiki/*.md  (Home, _Sidebar, _Footer, Bab 1..23, + lampiran).
Jalankan lalu publikasikan via scripts/publish-wiki.sh.
"""
from __future__ import annotations

import os
import re
import sys

REPO = os.path.dirname(  # .../commandcode bpr
    os.path.dirname(  # .../commandcode bpr/project
        os.path.dirname(os.path.abspath(__file__))  # .../project/scripts
    )
)
MANUAL = os.path.join(REPO, "project", "docs", "manual")
OUT = os.path.join(REPO, "wiki")

REPO_URL = "https://github.com/FiyyaLisanaDeV/awkward-credit"
BLOB = REPO_URL + "/blob/main"

# --- peta slug halaman wiki -------------------------------------------------
CHAPTERS = [
    ("01-ringkasan-ruang-lingkup.md", "Bab-01-Ringkasan-Ruang-Lingkup", "Ringkasan & Ruang Lingkup"),
    ("02-arsitektur-stack.md", "Bab-02-Arsitektur-Stack", "Arsitektur & Stack"),
    ("03-instalasi-konfigurasi-operasi.md", "Bab-03-Instalasi-Konfigurasi-Operasi", "Instalasi, Konfigurasi, Operasi"),
    ("04-auth-rbac-keamanan.md", "Bab-04-Auth-RBAC-Keamanan", "Auth, RBAC & Keamanan"),
    ("05-debitur-dan-data-nasabah.md", "Bab-05-Debitur-dan-Data-Nasabah", "Debitur & Data Nasabah"),
    ("06-permohonan-produk-slik.md", "Bab-06-Permohonan-Produk-SLIK", "Permohonan, Produk & SLIK"),
    ("07-analisis-kredit.md", "Bab-07-Analisis-Kredit", "Analisis Kredit"),
    ("08-perhitungan-jadwal-eir-simulasi.md", "Bab-08-Perhitungan-Jadwal-EIR-Simulasi", "Perhitungan, Jadwal, EIR & Simulasi"),
    ("09-business-rules-validasi.md", "Bab-09-Business-Rules-Validasi", "Business Rules & Validasi"),
    ("10-approval-dokumen-workflow.md", "Bab-10-Approval-Dokumen-Workflow", "Approval, Dokumen & Workflow"),
    ("11-kredit-fasilitas-pembayaran.md", "Bab-11-Kredit-Fasilitas-Pembayaran", "Kredit, Fasilitas & Pembayaran"),
    ("12-ledger-audit-integritas.md", "Bab-12-Ledger-Audit-Integritas", "Ledger, Audit & Integritas"),
    ("13-api-v1.md", "Bab-13-API-v1", "Referensi API v1"),
    ("14-ui-web-livewire.md", "Bab-14-UI-Web-Livewire", "UI Web & Livewire"),
    ("15-cli-artisan-tools.md", "Bab-15-CLI-Artisan-Tools", "CLI, Artisan & Quality Tools"),
    ("16-master-policy-seeder.md", "Bab-16-Master-Policy-Seeder", "Master, Policy & Seeder"),
    ("17-import-ekstraksi-data-quality.md", "Bab-17-Import-Ekstraksi-Data-Quality", "Import, Ekstraksi & Data Quality"),
    ("18-keamanan-pii-pdp.md", "Bab-18-Keamanan-PII-PDP", "Keamanan, PII & PDP"),
    ("19-struktur-data-model-migrasi.md", "Bab-19-Struktur-Data-Model-Migrasi", "Struktur Data (Model/Migrasi/Enum)"),
    ("20-known-issues-gap-rencana.md", "Bab-20-Known-Issues-Gap-Rencana", "Known Issues, Gap & Rencana"),
    ("21-operasional-backup-rilis.md", "Bab-21-Operasional-Backup-Rilis", "Operasional, Backup & Rilis"),
    ("22-mekanisme-update-manual-book.md", "Bab-22-Mekanisme-Update-Manual-Book", "Mekanisme Update Manual Book"),
    ("23-glosarium-indeks.md", "Bab-23-Glosarium-Indeks", "Glosarium & Indeks"),
]

EXTRA = [
    ("templates/feature-matrix.md", "Feature-Matrix", "Feature Matrix"),
    ("templates/checklist-pr.md", "Checklist-PR", "Checklist PR"),
    ("templates/fitur-final-entry.md", "Template-Fitur-Final", "Template Entri Fitur Final"),
    ("templates/evidence-note.md", "Template-Evidence-Note", "Template Evidence Note"),
    ("CHANGELOG.md", "Changelog-Manual", "Changelog Manual"),
    ("MANIFEST.md", "Manual-MANIFEST", "Manual MANIFEST"),
]

# dokumen root repo -> halaman wiki (nama berkas di repo root)
ROOT = [
    ("PRD.md", "PRD", "PRD — Product Requirements"),
    ("ERD.md", "ERD", "ERD — Entity Relationship"),
]
ROOT_SLUG = {f: s for f, s, _ in ROOT}

# target relatif (dari manual/) -> slug wiki
SLUG = {
    "chapters/" + f: s for f, s, _ in CHAPTERS
} | {t: s for t, s, _ in EXTRA} | {"README.md": "Home"}


def wiki_slug(rel: str) -> str | None:
    rel = rel.lstrip("./")
    if rel in SLUG:
        return SLUG[rel]
    # toleran terhadap "../chapters/.." atau "chapters/.."
    m = re.search(r"(chapters/[^/]+\.md)$", rel)
    if m and m.group(1) in SLUG:
        return SLUG[m.group(1)]
    m = re.search(r"(templates/[^/]+\.md)$", rel)
    if m and m.group(1) in SLUG:
        return SLUG[m.group(1)]
    base = os.path.basename(rel)
    if base in ("README.md",):
        return "Home"
    if base in ("CHANGELOG.md", "MANIFEST.md"):
        return base.replace(".md", "").replace("CHANGELOG", "Changelog").replace("MANIFEST", "Manual-MANIFEST")
    return None


LINK_RE = re.compile(r"\]\(([^)]+)\)")


def convert_links(text: str, src_rel: str) -> str:
    def repl(m: re.Match) -> str:
        target = m.group(1).strip()
        if target.startswith(("http://", "https://", "mailto:", "#")):
            return m.group(0)
        frag = ""
        if "#" in target:
            target, frag = target.split("#", 1)
            frag = "#" + frag
        slug = wiki_slug(target)
        if slug:
            return "](" + slug + frag + ")"
        # repo-relative lain -> tautan GitHub blob
        clean = os.path.normpath(os.path.join(os.path.dirname(src_rel), target))
        clean = clean.replace(os.sep, "/").lstrip("./")
        if clean.endswith(".md") or "/" in clean:
            return "](" + BLOB + "/project/docs/manual/" + clean + ")"
        return m.group(0)

    return LINK_RE.sub(repl, text)


def convert_root_links(text: str, src_rel: str) -> str:
    """Tautan pada dokumen root (PRD/ERD): PRD/ERD -> slug wiki, sisanya -> blob repo."""

    def repl(m: re.Match) -> str:
        target = m.group(1).strip()
        if target.startswith(("http://", "https://", "mailto:", "#")):
            return m.group(0)
        frag = ""
        if "#" in target:
            target, frag = target.split("#", 1)
            frag = "#" + frag
        if target in ROOT_SLUG:
            return "](" + ROOT_SLUG[target] + frag + ")"
        clean = os.path.normpath(os.path.join(os.path.dirname(src_rel), target))
        clean = clean.replace(os.sep, "/").lstrip("./")
        return "](" + BLOB + "/" + clean + frag + ")"

    return LINK_RE.sub(repl, text)


def read(path: str) -> str:
    with open(path, encoding="utf-8") as fh:
        return fh.read()


def write(name: str, text: str) -> None:
    with open(os.path.join(OUT, name + ".md"), "w", encoding="utf-8") as fh:
        fh.write(text.rstrip() + "\n")


def nav(prev_slug: str | None, prev_title: str | None, nxt_slug: str | None, nxt_title: str | None) -> str:
    left = f"← [{prev_title}]({prev_slug})" if prev_slug else "← *(awal)*"
    right = f"[{nxt_title}]({nxt_slug}) →" if nxt_slug else "*(akhir)* →"
    return f"\n---\n\n{left} &nbsp;·&nbsp; [🏠 Home](Home) &nbsp;·&nbsp; {right}\n"


def build() -> None:
    os.makedirs(OUT, exist_ok=True)

    # 1) Bab
    for i, (fname, slug, title) in enumerate(CHAPTERS):
        src = os.path.join(MANUAL, "chapters", fname)
        body = convert_links(read(src), "chapters/" + fname)
        prev = CHAPTERS[i - 1] if i > 0 else None
        nxt = CHAPTERS[i + 1] if i < len(CHAPTERS) - 1 else None
        body += nav(prev[1] if prev else None, prev[2] if prev else None,
                    nxt[1] if nxt else None, nxt[2] if nxt else None)
        write(slug, body)

    # 2) Lampiran
    for fname, slug, _title in EXTRA:
        src = os.path.join(MANUAL, fname)
        if not os.path.isfile(src):
            continue
        body = convert_links(read(src), fname)
        body += nav(None, None, None, None)
        write(slug, body)

    # 2b) Dokumen root (PRD/ERD)
    for fname, slug, _title in ROOT:
        src = os.path.join(REPO, fname)
        if not os.path.isfile(src):
            continue
        body = convert_root_links(read(src), fname)
        body += nav(None, None, None, None)
        write(slug, body)

    # 3) Home
    toc = "\n".join(f"{i+1}. [{t}]({s})" for i, (_f, s, t) in enumerate(CHAPTERS))
    home = f"""# Manual Book — Loan Analytic BPR

**Aplikasi Analisis Kredit, Dokumen & Ledger BPR** (PT BPR Bahteramas Kendari).

Manual lengkap yang mencakup seluruh fungsi aplikasi, disusun dari bukti kode/route/migrasi/config/test (`[E]`) plus rencana lapis risiko (`[K]`/`[Rencana]`).

> Repo: [{REPO_URL}]({REPO_URL}) · Sumber manual: [`project/docs/manual/`]({BLOB}/project/docs/manual/README.md)

## Label bukti
| Label | Arti |
|---|---|
| `[E]` | Terverifikasi dari kode/route/migrasi/config/test. |
| `[A]` | Usulan/desain. |
| `[K]` | Perlu verifikasi (angka/pasal/kebijakan/golden). |
| `[H]` | Keputusan bertanda tangan. |
| `[Rencana]` | Belum terimplementasi. |

## Dokumen produk
- [PRD — Product Requirements](PRD) — ruang lingkup, kebutuhan, koreksi wajib, NFR, milestone
- [ERD — Entity Relationship](ERD) — skema data (tabel/model/enum), relasi, invariant, gap

## Daftar isi
{toc}

## Lampiran
- [Feature Matrix](Feature-Matrix) — peta fungsi → status → bab
- [Checklist PR](Checklist-PR)
- [Template Entri Fitur Final](Template-Fitur-Final)
- [Template Evidence Note](Template-Evidence-Note)
- [Changelog Manual](Changelog-Manual)
- [Manual MANIFEST](Manual-MANIFEST)

---
_Perbarui manual setiap kali fitur/fungsi menjadi **final** — lihat [Bab 22](Bab-22-Mekanisme-Update-Manual-Book)._
"""
    write("Home", home)

    # 4) Sidebar & footer
    groups = [
        ("Ringkasan", CHAPTERS[0:1]),
        ("Arsitektur & Otorisasi", CHAPTERS[1:4]),
        ("Domain Kredit", CHAPTERS[4:7]),
        ("Perhitungan & Aturan", CHAPTERS[7:9]),
        ("Workflow & Kredit", CHAPTERS[9:12]),
        ("API, UI & CLI", CHAPTERS[12:15]),
        ("Data, Import & Keamanan", CHAPTERS[15:18]),
        ("Struktur Data & Gap", CHAPTERS[18:20]),
        ("Operasional & Pemeliharaan", CHAPTERS[20:23]),
    ]
    lines = ["### Manual Book BPR", "", "**[🏠 Home](Home)**", ""]
    for gname, items in groups:
        lines.append(f"**{gname}**")
        for _f, s, t in items:
            lines.append(f"- [{t}]({s})")
        lines.append("")
    lines.append("**Dokumen Produk**")
    for _f, s, t in ROOT:
        lines.append(f"- [{t}]({s})")
    lines.append("")
    lines.append("**Lampiran**")
    for _f, s, t in EXTRA:
        lines.append(f"- [{t}]({s})")
    lines.append("")
    lines.append(f"[Repo]({REPO_URL})")
    write("_Sidebar", "\n".join(lines))

    write("_Footer", "Manual book *Loan Analytic BPR* — label bukti `[E]/[A]/[K]/[H]/[Rencana]`. "
                     f"[Repo]({REPO_URL}) · [Manual di repo]({BLOB}/project/docs/manual/README.md)")

    print(f"wiki: {len(os.listdir(OUT))} halaman ditulis ke {OUT}")


if __name__ == "__main__":
    if not os.path.isdir(MANUAL):
        sys.exit(f"manual tidak ditemukan: {MANUAL}")
    build()
