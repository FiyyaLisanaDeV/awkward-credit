<?php

declare(strict_types=1);

namespace App\Services\Simulation;

use App\Enums\ProdukTipe;
use App\Enums\SistemSukuBunga;
use App\Models\SimulasiKredit;
use App\Models\User;
use App\Services\Policy\PolicyParameterResolver;
use App\Support\Konstanta;

final class SimulationRunner
{
    public function __construct(
        private readonly SimulationEngine $engine,
        private readonly SimulationService $simulation,
        private readonly PolicyParameterResolver $resolver,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function preview(array $input): array
    {
        return $this->engine->run($input, $this->resolver->active());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, User $user): SimulasiKredit
    {
        $parameters = $this->resolver->active();

        $mulai = microtime(true);
        $output = $this->engine->run($data, $parameters);
        $durasi = (int) round((microtime(true) - $mulai) * Konstanta::MILI_PER_DETIK);

        return $this->simulation->record(
            $user,
            (string) $data['label'],
            ProdukTipe::from((string) $data['produk_tipe']),
            SistemSukuBunga::from((string) $data['sistem_suku_bunga']),
            $parameters,
            $data,
            $output,
            $durasi,
        );
    }

    /**
     * Bandingkan dua hasil simulasi pada kolom numerik yang sama.
     *
     * @param  array<string, mixed>  $awal
     * @param  array<string, mixed>  $akhir
     * @return array<string, array{awal: float, akhir: float, selisih: float}>
     */
    public function diff(array $awal, array $akhir): array
    {
        $hasil = [];

        foreach ($this->flatten($awal) as $kunci => $nilai) {
            if (! is_numeric($nilai) || ! array_key_exists($kunci, $this->flatten($akhir))) {
                continue;
            }

            $pembanding = $this->flatten($akhir)[$kunci];

            if (! is_numeric($pembanding)) {
                continue;
            }

            $hasil[$kunci] = [
                'awal' => (float) $nilai,
                'akhir' => (float) $pembanding,
                'selisih' => (float) $pembanding - (float) $nilai,
            ];
        }

        return $hasil;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $hasil = [];

        foreach ($data as $kunci => $nilai) {
            $nama = $prefix === '' ? (string) $kunci : $prefix.'.'.$kunci;

            if (is_array($nilai)) {
                $hasil = array_merge($hasil, $this->flatten($nilai, $nama));

                continue;
            }

            $hasil[$nama] = $nilai;
        }

        return $hasil;
    }
}
