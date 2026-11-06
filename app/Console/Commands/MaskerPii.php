<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Debitur;
use Illuminate\Console\Command;

final class MaskerPii extends Command
{
    protected $signature = 'pii:masker {--confirm : konfirmasi masker}';

    protected $description = 'Masker PII pada salinan non-produksi (menolak DB aktif/produksi)';

    public function handle(): int
    {
        if (app()->isProduction() && ! filter_var((string) config('loan.security.pii_mask_allow_production'), FILTER_VALIDATE_BOOL)) {
            $this->error('Menolak: masker tidak boleh dijalankan pada DB produksi/aktif.');

            return self::FAILURE;
        }

        if (! $this->option('confirm')) {
            $this->error('Gunakan --confirm untuk melanjutkan masker.');

            return self::FAILURE;
        }

        $total = 0;

        Debitur::query()->eachById(function (Debitur $debitur) use (&$total): void {
            $debitur->update([
                'nama_ktp' => 'DEBITUR-'.substr((string) $debitur->id, 0, 8),
                'nama_ibu_kandung' => 'TERMASKING',
                'no_ktp' => 'MASKED-'.substr(hash('sha256', (string) $debitur->id), 0, 9),
                'no_hp' => '080000000000',
                'no_wa' => null,
                'email' => 'masked-'.substr((string) $debitur->id, 0, 8).'@invalid.local',
                'alamat_jalan' => 'TERMASKING',
                'alamat_rt' => '000',
                'alamat_rw' => '000',
                'kelurahan' => 'TERMASKING',
                'kecamatan' => 'TERMASKING',
                'tempat_lahir' => 'TERMASKING',
                'foto_ktp_path' => null,
                'foto_debitur_path' => null,
            ]);

            $total++;
        });

        $this->info("PII termaskir: {$total} debitur.");

        return self::SUCCESS;
    }
}
