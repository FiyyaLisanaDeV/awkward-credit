<?php

declare(strict_types=1);

namespace App\Services\Setting\Validators;

use App\Support\Konstanta;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class PolicyParameterValidator
{
    private const RATIO_KEYS = [
        'margin_kapasitas_bayar',
        'maksimum_ltv',
        'sensitivitas_omzet_ringan',
        'sensitivitas_omzet_berat',
        'sensitivitas_biaya',
        'bobot_character',
        'bobot_capacity',
        'bobot_capital',
        'bobot_collateral',
        'bobot_condition',
        'idir_threshold_tpp',
        'idir_hard_stop_tpp',
        'biaya_provisi_default',
        'biaya_administrasi_default',
        'biaya_survey_default',
        'biaya_lain_bulanan',
    ];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function validate(array $payload): void
    {
        $rules = [];

        foreach (self::RATIO_KEYS as $key) {
            $rules['params.'.$key] = ['required', 'numeric', 'between:0,1'];
        }

        $rules += [
            'params.minimum_dscr' => ['required', 'numeric', 'min:0'],
            'params.dscr_ideal' => ['required', 'numeric', 'min:0'],
            'params.hari_kerja_per_tahun' => ['required', 'integer', 'between:1,'.Konstanta::SETTING_MAX_HARI_KERJA],
            'params.rating_sangat_layak_min' => ['required', 'numeric', 'between:0,5'],
            'params.rating_layak_min' => ['required', 'numeric', 'between:0,5'],
            'params.rating_bersyarat_min' => ['required', 'numeric', 'between:0,5'],
            'params.threshold_slik_macet' => ['required', 'integer', 'min:0'],
            'params.threshold_slik_kol3' => ['required', 'integer', 'min:0'],
            'params.threshold_slik_nominal' => ['required', 'integer', 'min:0'],
            'params.rasio_balloon_layak' => ['required', 'numeric', 'min:0'],
            'params.rasio_balloon_perhatian' => ['required', 'numeric', 'min:0'],
            'params.plafon_kewajaran_multiplier' => ['required', 'numeric', 'min:1'],
            'params.hari_persediaan_default' => ['required', 'integer', 'min:0'],
            'params.skor_band_draft' => ['required', 'boolean'],
            'params.bobot_kelengkapan' => ['required', 'array'],
            'params.bobot_kelengkapan.debitur' => ['required', 'integer', 'min:0'],
            'params.bobot_kelengkapan.permohonan' => ['required', 'integer', 'min:0'],
            'params.bobot_kelengkapan.slik' => ['required', 'integer', 'min:0'],
            'params.bobot_kelengkapan.neraca' => ['required', 'integer', 'min:0'],
            'params.bobot_kelengkapan.kapasitas' => ['required', 'integer', 'min:0'],
            'params.bobot_kelengkapan.scoring' => ['required', 'integer', 'min:0'],
            'params.bobot_kelengkapan.dokumen' => ['required', 'integer', 'min:0'],
            'params.bobot_kelengkapan.*' => ['required', 'integer', 'min:0'],
            'params.skor_band_dscr' => ['required', 'array', 'min:1'],
            'params.skor_band_dscr.*.min' => ['required', 'numeric', 'min:0'],
            'params.skor_band_dscr.*.skor' => ['required', 'integer', 'between:'.Konstanta::SKOR_MIN.','.Konstanta::SKOR_MAKS],
            'params.skor_band_equity_asset' => ['required', 'array', 'min:1'],
            'params.skor_band_equity_asset.*.min' => ['required', 'numeric', 'between:0,1'],
            'params.skor_band_equity_asset.*.skor' => ['required', 'integer', 'between:'.Konstanta::SKOR_MIN.','.Konstanta::SKOR_MAKS],
            'params.skor_band_ltv' => ['required', 'array', 'min:1'],
            'params.skor_band_ltv.*.max' => ['required', 'numeric', 'between:0,1'],
            'params.skor_band_ltv.*.skor' => ['required', 'integer', 'between:'.Konstanta::SKOR_MIN.','.Konstanta::SKOR_MAKS],
            'params.skor_band_slik' => ['required', 'array', 'min:1'],
            'params.skor_band_slik.*.maks_kol3' => ['required', 'integer', 'min:0'],
            'params.skor_band_slik.*.skor' => ['required', 'integer', 'between:'.Konstanta::SKOR_MIN.','.Konstanta::SKOR_MAKS],
        ];

        Validator::make($payload, $rules)->validate();

        $params = $payload['params'];

        $this->assertWeightTotal($params);
        $this->assertKelengkapanTotal($params);
        $this->assertRatingOrder($params);
        $this->assertBalloonOrder($params);
        $this->assertBandOrder($params);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function assertWeightTotal(array $params): void
    {
        $total = 0.0;

        foreach (['bobot_character', 'bobot_capacity', 'bobot_capital', 'bobot_collateral', 'bobot_condition'] as $key) {
            $total += (float) $params[$key];
        }

        if (abs($total - 1.0) > Konstanta::SETTING_TOLERANCE) {
            $this->fail('params.bobot_character', 'Total bobot 5C harus 1.0 (saat ini '.$total.').');
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function assertKelengkapanTotal(array $params): void
    {
        $total = array_sum(array_map(static fn ($value): int => (int) $value, $params['bobot_kelengkapan']));

        if ((int) $total !== Konstanta::SETTING_BOBOT_TOTAL) {
            $this->fail('params.bobot_kelengkapan', 'Total bobot kelengkapan harus '.Konstanta::SETTING_BOBOT_TOTAL.'.');
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function assertRatingOrder(array $params): void
    {
        if ((float) $params['rating_sangat_layak_min'] <= (float) $params['rating_layak_min']
            || (float) $params['rating_layak_min'] <= (float) $params['rating_bersyarat_min']) {
            $this->fail('params.rating_layak_min', 'Ambang rating harus menurun: sangat layak > layak > bersyarat.');
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function assertBalloonOrder(array $params): void
    {
        if ((float) $params['rasio_balloon_layak'] <= (float) $params['rasio_balloon_perhatian']) {
            $this->fail('params.rasio_balloon_layak', 'Rasio balloon layak harus lebih besar dari rasio perhatian.');
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function assertBandOrder(array $params): void
    {
        $this->assertMonotonic($params['skor_band_dscr'], 'min', 'desc', 'params.skor_band_dscr');
        $this->assertMonotonic($params['skor_band_equity_asset'], 'min', 'desc', 'params.skor_band_equity_asset');
        $this->assertMonotonic($params['skor_band_ltv'], 'max', 'asc', 'params.skor_band_ltv');
        $this->assertMonotonic($params['skor_band_slik'], 'maks_kol3', 'asc', 'params.skor_band_slik');
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function assertMonotonic(array $rows, string $field, string $direction, string $key): void
    {
        $previous = null;

        foreach ($rows as $row) {
            $value = (float) $row[$field];

            if ($previous !== null) {
                $valid = $direction === 'desc' ? $value < $previous : $value > $previous;

                if (! $valid) {
                    $this->fail($key, 'Urutan band tidak monoton pada kolom '.$field.'.');
                }
            }

            $previous = $value;
        }
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
