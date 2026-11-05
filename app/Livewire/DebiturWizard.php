<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\JenisKelamin;
use App\Enums\StatusPerkawinan;
use App\Services\Debitur\DebiturService;
use App\Support\Validation\DebiturRules;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;

final class DebiturWizard extends Component
{
    public int $step = 1;

    public string $nama_ktp = '';

    public string $nama_ibu_kandung = '';

    public string $no_ktp = '';

    public string $no_kk = '';

    public string $npwp = '';

    public string $jenis_kelamin = 'L';

    public string $status_perkawinan = '';

    public string $agama = '';

    public string $pendidikan = '';

    public string $tempat_lahir = '';

    public string $tanggal_lahir = '';

    public string $no_hp = '';

    public string $no_wa = '';

    public string $email = '';

    public string $alamat_jalan = '';

    public string $alamat_rt = '';

    public string $alamat_rw = '';

    public string $kelurahan = '';

    public string $kecamatan = '';

    public string $kabupaten = '';

    public string $provinsi = '';

    public string $kode_pos = '';

    public string $alamat_tinggal = '';

    public string $status_tempat_tinggal = '';

    public string $lama_tinggal_tahun = '';

    public string $koordinat_rumah = '';

    public string $jumlah_keluarga = '0';

    public string $jumlah_tanggungan = '0';

    public string $pekerjaan_utama = '';

    public string $jabatan = '';

    public string $nama_perusahaan = '';

    public string $lama_bekerja_tahun = '';

    public string $penghasilan_bulanan = '';

    /** @var array<int, list<string>> */
    private array $fields = [
        1 => [
            'nama_ktp', 'nama_ibu_kandung', 'no_ktp', 'no_kk', 'npwp', 'jenis_kelamin',
            'status_perkawinan', 'agama', 'pendidikan', 'tempat_lahir', 'tanggal_lahir',
        ],
        2 => [
            'no_hp', 'no_wa', 'email', 'alamat_jalan', 'alamat_rt', 'alamat_rw', 'kelurahan',
            'kecamatan', 'kabupaten', 'provinsi', 'kode_pos', 'alamat_tinggal',
            'status_tempat_tinggal', 'lama_tinggal_tahun', 'koordinat_rumah',
            'jumlah_keluarga', 'jumlah_tanggungan',
        ],
        3 => [
            'pekerjaan_utama', 'jabatan', 'nama_perusahaan', 'lama_bekerja_tahun', 'penghasilan_bulanan',
        ],
    ];

    public function next(): void
    {
        $this->validate(Arr::only(DebiturRules::rules(), $this->fields[$this->step]));

        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function previous(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function save(DebiturService $service): void
    {
        $this->validate(DebiturRules::rules());

        $debitur = $service->create($this->payload(), auth()->user());

        session()->flash('status', __('debitur.created'));

        $this->redirectRoute('debitur.show', $debitur);
    }

    public function render(): View
    {
        return view('livewire.debitur-wizard', [
            'jenisKelamin' => JenisKelamin::cases(),
            'statusPerkawinan' => StatusPerkawinan::cases(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        $data = Arr::only($this->all(), array_keys(DebiturRules::rules()));

        $payload = array_map(
            static fn (mixed $value): mixed => $value === '' ? null : $value,
            $data,
        );

        foreach (['jumlah_keluarga', 'jumlah_tanggungan', 'lama_tinggal_tahun', 'lama_bekerja_tahun'] as $field) {
            if (($payload[$field] ?? null) === null) {
                $payload[$field] = 0;
            }
        }

        return $payload;
    }
}
