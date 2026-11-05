<?php

declare(strict_types=1);

namespace App\Support\Validation;

use App\Enums\JenisKelamin;
use App\Enums\StatusPerkawinan;
use Illuminate\Validation\Rule;

final class DebiturRules
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'nama_ktp' => ['required', 'string', 'max:100'],
            'nama_ibu_kandung' => ['nullable', 'string', 'max:100'],
            'no_ktp' => ['required', 'digits:16'],
            'no_kk' => ['nullable', 'digits:16'],
            'npwp' => ['nullable', 'string', 'max:20'],
            'jenis_kelamin' => ['required', Rule::enum(JenisKelamin::class)],
            'status_perkawinan' => ['required', Rule::enum(StatusPerkawinan::class)],
            'agama' => ['nullable', 'string', 'max:20'],
            'pendidikan' => ['nullable', 'string', 'max:10'],
            'pekerjaan_utama' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'nama_perusahaan' => ['nullable', 'string', 'max:100'],
            'lama_bekerja_tahun' => ['nullable', 'integer', 'min:0', 'max:80'],
            'penghasilan_bulanan' => ['nullable', 'numeric', 'min:0'],
            'email' => ['nullable', 'email', 'max:100'],
            'no_hp' => ['required', 'string', 'max:20'],
            'no_wa' => ['nullable', 'string', 'max:20'],
            'alamat_jalan' => ['required', 'string'],
            'alamat_rt' => ['required', 'string', 'max:5'],
            'alamat_rw' => ['required', 'string', 'max:5'],
            'kelurahan' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kabupaten' => ['required', 'string', 'max:100'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'alamat_tinggal' => ['nullable', 'string'],
            'status_tempat_tinggal' => ['nullable', 'string', 'max:20'],
            'lama_tinggal_tahun' => ['nullable', 'integer', 'min:0'],
            'koordinat_rumah' => ['nullable', 'string', 'max:50'],
            'jumlah_keluarga' => ['nullable', 'integer', 'min:0'],
            'jumlah_tanggungan' => ['nullable', 'integer', 'min:0'],
            'tempat_lahir' => ['required', 'string', 'max:50'],
            'tanggal_lahir' => ['required', 'date', 'before:-17 years'],
        ];
    }
}
