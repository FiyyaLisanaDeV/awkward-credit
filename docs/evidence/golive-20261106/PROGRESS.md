# PROGRESS — go-live 2026-11-06

| Waktu | Langkah | Perintah kunci | Hasil (exit) | Label |
|---|---|---|---|---|
| 19:25:57 | T0.1 git status | git status -sb | 0 | [V] commit awal 5048d55 |
| 19:25:57 | T0.2 verify --full | bash scripts/verify.sh --full | 0 | [V] |
| 19:25:57 | T0.3 ledger:verify | php artisan ledger:verify | 0 | [V] 10 baris utuh |
| 19:25:57 | T0.4 hardcode:audit | php artisan hardcode:audit | 0 | [V] 0 pelanggaran |
| 19:25:57 | T0.5 docs:check --strict | php artisan docs:check --strict | 0 | [V] 0 temuan |
| 19:25:57 | T0.6 inventaris route | route:list --json | 0 | [V] web kredit.index/show saja; tidak ada kredit/cairkan, dokumen, ledger, import web |
| 19:25:57 | T0.7 inventaris permission | grep RolePermissionSeeder | 0 | [V] tidak ada kredit.cairkan; dokumen/ledger/import ada |
| 19:25:57 | T0.8 generator | ls Generators | 0 | [V] 17 generator Doc01..17 |
| 19:25:57 | T0.9 template | tinker | 0 | [V] 17 template, semua DRAFT |
| 19:25:57 | T0.10 import service | find app/Services/Import | 0 | [V] kosong |
| 19:25:57 | T0.11 import_profil | tinker | 0 | [V] 8 profil |
| 19:25:57 | T0.12 cairkan | grep KreditService | 0 | [V] baris 38 |
| 19:25:57 | T0.13 PilotUatSeeder | grep | 0 | [V] hanya KDI |
| 19:25:57 | T0.14 PAT/token | find | 0 | [V] 3 file, baris 1 (nilai tidak dicetak) |
| 19:30:00 | P0.1 herd tld | herd tld | 0 | [V] test |
| 19:30:00 | P0.2 herd which | herd which | 0 | [V] LaravelValetDriver |
| 19:30:00 | P0.3 link | herd link --secure | 0 | [V] loan-analytic-bpr linked |
| 19:30:00 | P0.4 secure | herd secure loan-analytic-bpr.test --silent | 0 | [V] cert 2027-11-09 |
| 19:30:00 | P0.5 restart | herd restart | 0 | [V] pesan restart DNSMasq/PHP/Nginx |
| 19:30:00 | P0.6 proses nginx/dnsmasq | ps aux / lsof :80/443/53 | 0 | [?] TIDAK berjalan |
| 19:30:00 | P0.7 curl health https | curl -f https://loan-analytic-bpr.test/health | 28 | [?] Resolving timed out |
| 19:30:00 | P0.8 helper daemon | launchctl list | 0 | [V] helper tidak termuat |
| 19:30:00 | P0.9 sudo | sudo -n true | 1 | [V] perlu password → BLOCKED |
| 19:32:00 | P0 GATE | curl -f https://loan-analytic-bpr.test/health | 28 | BLOCKED: helper Herd butuh sudo; nginx/dnsmasq tidak jalan |
| 19:34:00 | K2 verify --full | bash scripts/verify.sh --full | 0 | [V] |
| 19:34:00 | K2 ledger:verify | php artisan ledger:verify | 0 | [V] |
| 19:34:00 | K2 hardcode:audit | php artisan hardcode:audit | 0 | [V] |
| 19:34:00 | K2 docs:check --strict | php artisan docs:check --strict | 0 | [V] |
| 19:34:00 | K2 curl health | curl -f https://loan-analytic-bpr.test/health | 28 | [?] masih timeout |
| 19:35:00 | K2 audit diri | git diff 5048d55..HEAD --stat | 0 | [V] tidak ada perubahan kode/tes/policy/secret |
| 19:51:00 | P0.10 helper load | sudo launchctl load helper | 0 | [V] helper terdaftar di system domain |
| 19:51:00 | P0.11 herd restart | herd restart | 0 | [V] nginx-arm64 + dnsmasq-arm64 berjalan |
| 19:51:00 | P0.12 curl health | curl -f https://loan-analytic-bpr.test/health | 0 | [V] 200 |
| 19:52:00 | P0.13 seed pilot | php artisan db:seed PilotUatSeeder | 0 | [V] KDI siap (idempoten) |
| 19:53:00 | P0.14 .env production | edit .env | 0 | [V] production, debug=false, https, secure cookie, queue=database |
| 19:53:00 | P0.15 storage/optimize | storage:link + optimize:clear + config:cache | 0 | [V] |
| 19:53:00 | P0.16 about | php artisan about --only=environment | 0 | [V] production + debug OFF |
| 19:53:00 | P0.17 curl redirect | curl -I -L login | 0 | [V] HTTP/2 200 tanpa loop |
| 19:53:00 | P0 GATE | curl -f https://loan-analytic-bpr.test/health | 0 | [V] SELESAI |
| 20:10:00 | P1A pencairan UI | verify --full + test PencairanUiTest | 0 | [V] SELESAI commit ee88674 |
| 20:12:00 | P1B dokumen UI | verify --full + test DokumenUiTest | 0 | [V] SELESAI commit b3bcc85 |
| 20:14:00 | P1C ledger UI | verify --full + test LedgerUiTest | 0 | [V] SELESAI commit fede060 |
| 20:20:00 | P1D import UI | verify --full + test ImportUiTest | 0 | [V] SELESAI commit 82707e1 (hanya pembayaran CSV; debitur CSV tidak ada profil) |
| 20:22:00 | P1E smoke role | curl https per role | 0 | [V] 36/36 PASS sesuai matriks |
| 20:22:00 | P1 GATE | verify --full | 0 | [V] SELESAI |
| 20:30:00 | P2.1 seeder 3 cabang | PILOT_ALLOW_PRODUCTION=true db:seed PilotUatSeeder | 0 | [V] KDI/BOM/KOL |
| 20:31:00 | P2.2 setujui template | service setting draft->approve->apply x17 | 0 | [V] 17 APPROVED [K] pilot dummy |
| 20:32:00 | P2.3 policy | tinker | 0 | [V] ACTIVE margin0.5/idir0.35/ltv0.7 tidak diubah |
| 20:33:00 | P2.4 isolasi | test 4 modul lintas cabang | 0 | [V] 20 passed |
| 20:34:00 | P2 GATE | verify --full | 0 | [V] SELESAI ledger 20 baris |
| 20:40:00 | P3A role DB | CREATE ROLE loan_bpr_app + GRANT + .env | 0 | [V] NOSUPERUSER; ledger/audit_log SELECT+INSERT |
| 20:41:00 | P3A smoke | curl https health/dashboard/dokumen | 0 | [V] 200 |
| 20:42:00 | P3A gate | verify --full | 0 | [V] SELESAI |
| 20:50:00 | P3B backup | pg_dump luar repo + tar storage | 0 | [V] |
| 20:51:00 | P3B restore drill | pg_restore scratch + count + ledger:verify | 0 | [V] semua baris cocok, ledger OK |
| 20:52:00 | P3C masker | pii:masker + test | 0 | [V] 2 test |
| 20:53:00 | P3C index | slow_query log | 0 | [V] tidak ada data produksi, tidak ada index spekulatif |
| 20:54:00 | P3C scheduler | schedule:list | 0 | [V] ledger:verify 00:30 |
| 20:55:00 | P3 GATE | verify --full | 0 | [V] SELESAI |
| 20:56:00 | P4 materi UAT | buat skenario/berita acara/checklist/lembar K | 0 | [V] kolom hasil/keputusan kosong |
| 20:56:00 | P4 docs check | docs:check --strict | 0 | [V] |
| 20:58:00 | P5 runbook | tulis docs/runbook-produksi-vps.md | 0 | [V] dokumen saja |
| 21:00:00 | K2 pemeriksaan kedua | verify/ledger/hardcode/docs/curl | 0 | [V] semua hijau |
| 21:01:00 | K2 audit diri | git diff 5048d55..HEAD | 0 | [V] tidak ada tes dilemahkan, policy tidak diubah, tidak ada secret |
