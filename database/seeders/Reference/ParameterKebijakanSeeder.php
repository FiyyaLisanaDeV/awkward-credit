<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ParameterKebijakanSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ref_parameter_kebijakan')->where('version', 'v1.0')->exists()) {
            return;
        }

        $adminId = DB::table('users')->orderBy('id')->value('id');

        if ($adminId === null) {
            $this->command?->warn('ParameterKebijakanSeeder dilewati: belum ada user.');

            return;
        }

        $params = [
            'margin_kapasitas_bayar' => 0.5,
            'minimum_dscr' => 1.0,
            'dscr_ideal' => 1.25,
            'maksimum_ltv' => 0.7,
            'hari_kerja_per_tahun' => 360,
            'sensitivitas_omzet_ringan' => 0.10,
            'sensitivitas_omzet_berat' => 0.20,
            'sensitivitas_biaya' => 0.10,
            'bobot_character' => 0.25,
            'bobot_capacity' => 0.30,
            'bobot_capital' => 0.15,
            'bobot_collateral' => 0.20,
            'bobot_condition' => 0.10,
            'rating_sangat_layak_min' => 4.0,
            'rating_layak_min' => 3.0,
            'rating_bersyarat_min' => 2.0,
            'threshold_slik_macet' => 2,
            'threshold_slik_kol3' => 1,
            'threshold_slik_nominal' => 1000000,
            'rasio_balloon_layak' => 1.10,
            'rasio_balloon_perhatian' => 1.00,
            'idir_threshold_tpp' => 0.35,
            'idir_hard_stop_tpp' => 0.80,
            'plafon_kewajaran_multiplier' => 1.20,
            'biaya_provisi_default' => 0.01,
            'biaya_administrasi_default' => 0.01,
            'biaya_survey_default' => 0.02,
            'biaya_lain_bulanan' => 0.0,
            'hari_persediaan_default' => 0,
            'skor_band_dscr' => [
                ['min' => 1.50, 'skor' => 5],
                ['min' => 1.25, 'skor' => 4],
                ['min' => 1.00, 'skor' => 3],
                ['min' => 0.75, 'skor' => 2],
                ['min' => 0.00, 'skor' => 1],
            ],
            'skor_band_equity_asset' => [
                ['min' => 0.50, 'skor' => 5],
                ['min' => 0.30, 'skor' => 4],
                ['min' => 0.20, 'skor' => 3],
                ['min' => 0.10, 'skor' => 2],
                ['min' => 0.00, 'skor' => 1],
            ],
            'skor_band_ltv' => [
                ['max' => 0.50, 'skor' => 5],
                ['max' => 0.60, 'skor' => 4],
                ['max' => 0.70, 'skor' => 3],
                ['max' => 0.80, 'skor' => 2],
                ['max' => 1.00, 'skor' => 1],
            ],
            'skor_band_slik' => [
                ['maks_kol3' => 0, 'skor' => 5],
                ['maks_kol3' => 1, 'skor' => 3],
                ['maks_kol3' => 2, 'skor' => 2],
                ['maks_kol3' => 99, 'skor' => 1],
            ],
            'skor_band_draft' => true,
            'bobot_kelengkapan' => [
                'debitur' => 25,
                'permohonan' => 15,
                'slik' => 15,
                'neraca' => 20,
                'kapasitas' => 10,
                'scoring' => 10,
                'dokumen' => 5,
            ],
        ];

        DB::table('ref_parameter_kebijakan')->insert([
            'id' => (string) Str::uuid(),
            'version' => 'v1.0',
            'effective_date' => '2026-01-01',
            'params' => json_encode($params),
            'status' => 'ACTIVE',
            'created_by' => $adminId,
            'approved_by' => $adminId,
            'approved_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
