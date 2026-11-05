# AGENTS.md — Aturan Kerja Project BPR (INIT)

## Wajib
- Ikuti precedence: keputusan `[H]` > `docs/spec/*` > rencana/riset > prompt.
- Label setiap angka/ambang/pasal/versi: `[V]/[E]/[K]/[?]/[H]`.
- Tulis `UNKNOWN: <kebutuhan>` di `docs/questions.md` lalu berhenti bila sumber/keputusan tak ada.
- Golden test + tes negatif sebelum implementasi; Kartu Bukti di `docs/evidence/<ID>.md`.
- Setiap keputusan kredit/analisis simpan snapshot, versi policy/model, rating/alasan/penandatangan (desain target).

## Dilarang
- Float untuk uang; angka/pasal dari ingatan model; PII nyata; seed ACTIVE tanpa `[H]`.
- Tulis `ledger_*`/`audit_events` di luar `JournalPoster`/`AuditRecorder`.
- Melemahkan tes, `--no-verify`, bypass audit/immutability.
- Hapus buku parsial; BMPK bypass; model AI kredit tak dapat dijelaskan; deploy pusat data/DRP di luar Indonesia `[K verifikasi]`.

## Cakupan
- L1–L9 rencana maksimal (lihat `../research/07-rekonsiliasi-riset-independen-vs-research.md`) dengan dependency F1→F2→F3.
