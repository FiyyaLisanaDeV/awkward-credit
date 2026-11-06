<?php

declare(strict_types=1);

namespace App\Services\Slik;

use App\Models\PermohonanKredit;
use App\Support\Konstanta;
use App\Support\Rounding;
use Illuminate\Database\Eloquent\Model;

final class SlikAggregationService
{
    /**
     * @return array{angsuran_debitur: float, angsuran_pasangan: float, total_angsuran_slik: float, jumlah_fasilitas: int}
     */
    public function aggregateActiveObligations(PermohonanKredit $permohonan): array
    {
        $debiturFacilities = $permohonan->slikDebitur()->where('baki_debet', '>', 0)->get();
        $pasanganFacilities = $permohonan->slikPasangan()->where('baki_debet', '>', 0)->get();

        $seen = [];
        $angsuranDebitur = 0.0;
        $angsuranPasangan = 0.0;
        $count = 0;

        foreach ([
            ['rows' => $debiturFacilities, 'side' => 'debitur'],
            ['rows' => $pasanganFacilities, 'side' => 'pasangan'],
        ] as $group) {
            foreach ($group['rows'] as $facility) {
                $key = $this->dedupeKey($facility);

                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $angsuran = (float) ($facility->angsuran_bulanan ?? 0);

                if ($angsuran <= 0) {
                    $tenor = max(1, (int) ($facility->jangka_waktu_bulan ?? 1));
                    $rate = (float) ($facility->suku_bunga ?? 0);
                    $plafon = (float) $facility->plafon;
                    $angsuran = ($plafon / $tenor) + (($plafon * $rate) / Konstanta::BULAN_PER_TAHUN);
                    $facility->forceFill(['is_angsuran_estimated' => true])->save();
                }

                if ($group['side'] === 'debitur') {
                    $angsuranDebitur += $angsuran;
                } else {
                    $angsuranPasangan += $angsuran;
                }

                $count++;
            }
        }

        return [
            'angsuran_debitur' => Rounding::money($angsuranDebitur),
            'angsuran_pasangan' => Rounding::money($angsuranPasangan),
            'total_angsuran_slik' => Rounding::money($angsuranDebitur + $angsuranPasangan),
            'jumlah_fasilitas' => $count,
        ];
    }

    private function dedupeKey(Model $facility): string
    {
        $noFasilitas = trim((string) $facility->getAttribute('no_fasilitas'));

        if ($noFasilitas !== '') {
            return 'NO:'.$noFasilitas;
        }

        $tanggal = $facility->getAttribute('tanggal_data');
        $tanggal = $tanggal instanceof \DateTimeInterface ? $tanggal->format('Y-m-d') : '-';

        return 'NAT:'.$facility->getAttribute('nama_lembaga').':'.$facility->getAttribute('plafon').':'.$facility->getAttribute('baki_debet').':'.$tanggal;
    }
}
