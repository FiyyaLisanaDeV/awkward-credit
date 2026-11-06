<?php

declare(strict_types=1);

namespace Database\Seeders\Local;

use App\Enums\SistemSukuBunga;
use App\Enums\StatusPermohonan;
use App\Models\Debitur;
use App\Models\Kredit;
use App\Models\PermohonanKredit;
use App\Models\Produk;
use App\Models\RefParameterKebijakan;
use App\Models\User;
use App\Services\Approval\ApprovalService;
use App\Services\Kredit\KreditService;
use App\Services\Kredit\PembayaranService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Data pilot/UAT 1 cabang (KDI). Hanya untuk lingkungan lokal.
 * Tidak boleh dijalankan di produksi dan tidak memakai PII nyata.
 */
final class PilotUatSeeder extends Seeder
{
    private const PILOT_PASSWORD = 'Pilot#2026!';

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('PilotUatSeeder ditolak di produksi.');

            return;
        }

        $cabangId = DB::table('cabang')->where('kode', 'KDI')->value('id');

        if ($cabangId === null) {
            $this->command?->error('Cabang KDI belum ada. Jalankan seeder referensi dulu.');

            return;
        }

        $users = $this->seedUsers((string) $cabangId);
        $debitur = $this->seedDebitur((string) $cabangId);
        $produk = Produk::query()->where('cabang_id', $cabangId)->where('kode', 'TPP')->firstOrFail();
        $parameter = RefParameterKebijakan::query()->where('status', 'ACTIVE')->firstOrFail();

        $submitted = $this->seedPermohonan(
            (string) $cabangId,
            $debitur,
            $produk,
            $parameter,
            $users['ao'],
            'KDI/PILOT/0001',
            StatusPermohonan::SUBMITTED->value,
        );

        $approved = $this->seedPermohonan(
            (string) $cabangId,
            $debitur,
            $produk,
            $parameter,
            $users['ao2'],
            'KDI/PILOT/0002',
            StatusPermohonan::SUBMITTED->value,
        );

        $this->approveAndCash($approved, $users);

        $this->command?->info('Pilot UAT siap. Login password: '.self::PILOT_PASSWORD);
        $this->command?->table(['username', 'role', 'nama'], array_map(
            static fn (User $user): array => [$user->username, $user->getRoleNames()->implode(','), $user->name],
            $users,
        ));
        $this->command?->info('Permohonan UAT: '.$submitted->no_permohonan.' (SUBMITTED), '.$approved->no_permohonan.' (ACTIVE/berjalan)');

        Cache::flush();
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(string $cabangId): array
    {
        $rows = [
            'ao' => ['AO', 'AO Pilot', 'ao.pilot'],
            'ao2' => ['AO', 'AO Pilot 2', 'ao2.pilot'],
            'mgr' => ['MGR_PEMASARAN', 'Manajer Pemasaran Pilot', 'mgr.pilot'],
            'koordinator' => ['KOORDINATOR', 'Koordinator Pilot', 'koordinator.pilot'],
            'dirut' => ['DIRUT', 'Direktur Pilot', 'dirut.pilot'],
            'komite' => ['KOMITE', 'Komite Pilot', 'komite.pilot'],
            'admin' => ['ADMIN', 'Admin Pilot', 'admin.pilot'],
            'legal' => ['LEGAL', 'Legal Pilot', 'legal.pilot'],
            'auditor' => ['AUDITOR', 'Auditor Pilot', 'auditor.pilot'],
        ];

        $users = [];

        foreach ($rows as $key => [$role, $name, $username]) {
            $user = User::query()->firstOrCreate(
                ['username' => $username],
                [
                    'cabang_id' => $cabangId,
                    'name' => $name,
                    'email' => $username.'@bpr-pilot.local',
                    'password' => self::PILOT_PASSWORD,
                    'jabatan' => $role,
                    'is_active' => true,
                    'must_change_password' => false,
                ],
            );

            $user->assignRole($role);
            $users[$key] = $user;
        }

        return $users;
    }

    private function seedDebitur(string $cabangId): Debitur
    {
        return Debitur::query()->firstOrCreate(
            ['no_ktp' => '7371000000000001'],
            [
                'cabang_id' => $cabangId,
                'nama_ktp' => 'Debitur Pilot Satu',
                'nama_ibu_kandung' => 'Ibu Pilot',
                'jenis_kelamin' => 'L',
                'status_perkawinan' => 'MENIKAH',
                'no_hp' => '081200000001',
                'alamat_jalan' => 'Jalan Pilot No. 1',
                'alamat_rt' => '001',
                'alamat_rw' => '002',
                'kelurahan' => 'Kelurahan Pilot',
                'kecamatan' => 'Kecamatan Pilot',
                'kabupaten' => 'Kendari',
                'provinsi' => 'Sulawesi Tenggara',
                'tempat_lahir' => 'Kendari',
                'tanggal_lahir' => '1990-01-01',
                'jumlah_keluarga' => 3,
                'jumlah_tanggungan' => 2,
                'sumber_data' => 'MANUAL',
            ],
        );
    }

    private function seedPermohonan(
        string $cabangId,
        Debitur $debitur,
        Produk $produk,
        RefParameterKebijakan $parameter,
        User $ao,
        string $nomor,
        string $status,
    ): PermohonanKredit {
        return PermohonanKredit::query()->firstOrCreate(
            ['no_permohonan' => $nomor],
            [
                'cabang_id' => $cabangId,
                'debitur_id' => $debitur->id,
                'produk_id' => $produk->id,
                'plafon_diajukan' => 5000000,
                'suku_bunga_tahunan' => 0.18,
                'tenor_diajukan' => 12,
                'sistem_suku_bunga' => SistemSukuBunga::Flat->value,
                'kegunaan_pinjaman' => 'Konsumsi',
                'tujuan_penggunaan' => 'Konsumsi',
                'status' => $status,
                'ao_id' => $ao->id,
                'parameter_kebijakan_id' => $parameter->id,
                'tanggal_permohonan' => '2026-11-06',
                'kelengkapan_persen' => 100,
            ],
        );
    }

    /**
     * @param  array<string, User>  $users
     */
    private function approveAndCash(PermohonanKredit $permohonan, array $users): void
    {
        $approval = app(ApprovalService::class);

        if ((string) $permohonan->status === StatusPermohonan::SUBMITTED->value) {
            $approval->approve($permohonan, $users['ao'], 'Level 1 pilot');
            $approval->approve($permohonan->fresh() ?? $permohonan, $users['mgr'], 'Level 2 pilot');
        }

        $permohonan->refresh();

        if ($permohonan->status !== StatusPermohonan::APPROVED->value) {
            return;
        }

        /** @var Kredit $kredit */
        $kredit = app(KreditService::class)->cairkan($permohonan, $users['admin'], [
            'tanggal_cair' => '2026-11-06',
            'rekening_pencairan' => '1234567890',
            'no_akad' => 'AKAD/PILOT/0002',
        ]);

        if (! $kredit->pembayaran()->where('periode', 1)->exists()) {
            app(PembayaranService::class)->catat($kredit, $users['admin'], [
                'periode' => 1,
                'tanggal_bayar' => '2026-12-06',
                'metode' => 'TUNAI',
                'no_bukti' => 'PILOT-BAYAR-0001',
            ]);
        }
    }
}
