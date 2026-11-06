<?php

declare(strict_types=1);

namespace App\Services\Setting;

use App\Enums\KategoriChecklist;
use App\Enums\ProdukTipe;
use App\Enums\SettingSection;
use App\Enums\StatusTemplate;
use App\Models\Produk;
use App\Models\RefApprovalMatrix;
use App\Models\RefChecklistDokumen;
use App\Models\RefKategoriAgunanPojk;
use App\Models\RefParameterKebijakan;
use App\Models\RefRateAsuransiJiwa;
use App\Models\RefRateAsuransiKebakaran;
use App\Models\RefRateKendaraan;
use App\Models\RefTemplateDokumen;
use App\Support\Konstanta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

final class SettingSectionRegistry
{
    /**
     * @return array<string, array{label: string, description: string, model: class-string<Model>, partial: string, versioned: bool}>
     */
    public function all(): array
    {
        return [
            SettingSection::PolicyParameter->value => [
                'label' => 'setting_section.policy',
                'description' => 'setting_section.policy_desc',
                'model' => RefParameterKebijakan::class,
                'partial' => 'settings.sections.policy',
                'versioned' => true,
            ],
            SettingSection::ApprovalMatrix->value => [
                'label' => 'setting_section.approval_matrix',
                'description' => 'setting_section.approval_matrix_desc',
                'model' => RefApprovalMatrix::class,
                'partial' => 'settings.sections.approval-matrix',
                'versioned' => true,
            ],
            SettingSection::RateJiwa->value => [
                'label' => 'setting_section.rate_jiwa',
                'description' => 'setting_section.rate_jiwa_desc',
                'model' => RefRateAsuransiJiwa::class,
                'partial' => 'settings.sections.rates',
                'versioned' => false,
            ],
            SettingSection::RateKebakaran->value => [
                'label' => 'setting_section.rate_kebakaran',
                'description' => 'setting_section.rate_kebakaran_desc',
                'model' => RefRateAsuransiKebakaran::class,
                'partial' => 'settings.sections.rates',
                'versioned' => false,
            ],
            SettingSection::RateKendaraan->value => [
                'label' => 'setting_section.rate_kendaraan',
                'description' => 'setting_section.rate_kendaraan_desc',
                'model' => RefRateKendaraan::class,
                'partial' => 'settings.sections.rates',
                'versioned' => false,
            ],
            SettingSection::CollateralCategory->value => [
                'label' => 'setting_section.collateral',
                'description' => 'setting_section.collateral_desc',
                'model' => RefKategoriAgunanPojk::class,
                'partial' => 'settings.sections.collateral',
                'versioned' => false,
            ],
            SettingSection::ChecklistDokumen->value => [
                'label' => 'setting_section.checklist',
                'description' => 'setting_section.checklist_desc',
                'model' => RefChecklistDokumen::class,
                'partial' => 'settings.sections.checklist',
                'versioned' => false,
            ],
            SettingSection::TemplateDokumen->value => [
                'label' => 'setting_section.template',
                'description' => 'setting_section.template_desc',
                'model' => RefTemplateDokumen::class,
                'partial' => 'settings.sections.templates',
                'versioned' => false,
            ],
            SettingSection::Produk->value => [
                'label' => 'setting_section.produk',
                'description' => 'setting_section.produk_desc',
                'model' => Produk::class,
                'partial' => 'settings.sections.produk',
                'versioned' => false,
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, description: string, model: class-string<Model>, partial: string, versioned: bool}>
     */
    public function references(): array
    {
        return array_filter($this->all(), static fn (array $meta): bool => ! $meta['versioned']);
    }

    public function model(SettingSection $section): string
    {
        return $this->all()[$section->value]['model'];
    }

    public function partial(SettingSection $section): string
    {
        return $this->all()[$section->value]['partial'];
    }

    public function isVersioned(SettingSection $section): bool
    {
        return $this->all()[$section->value]['versioned'];
    }

    public function label(SettingSection $section): string
    {
        return (string) __($this->all()[$section->value]['label']);
    }

    public function fromString(string $section): SettingSection
    {
        return SettingSection::from($section);
    }

    /**
     * @return list<string>
     */
    public function roles(): array
    {
        return array_values(array_map(
            static fn (mixed $name): string => (string) $name,
            Role::query()->orderBy('name')->pluck('name')->all(),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(SettingSection $section): array
    {
        return match ($section) {
            SettingSection::RateJiwa => [
                'perusahaan' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
                'usia_min' => ['required', 'integer', 'between:0,'.Konstanta::SETTING_MAX_UMUR],
                'usia_max' => ['required', 'integer', 'between:0,'.Konstanta::SETTING_MAX_UMUR, 'gte:usia_min'],
                'tenor_min' => ['required', 'integer', 'between:1,'.Konstanta::SETTING_MAX_TENOR],
                'tenor_max' => ['required', 'integer', 'between:1,'.Konstanta::SETTING_MAX_TENOR, 'gte:tenor_min'],
                'rate_permil' => ['required', 'numeric', 'min:0'],
                'effective_date' => ['required', 'date'],
                'sumber' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
            ],
            SettingSection::RateKebakaran => [
                'perusahaan' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
                'usia_min' => ['required', 'integer', 'between:0,'.Konstanta::SETTING_MAX_UMUR],
                'usia_max' => ['required', 'integer', 'between:0,'.Konstanta::SETTING_MAX_UMUR, 'gte:usia_min'],
                'masa_asuransi' => ['required', 'integer', 'between:1,'.Konstanta::SETTING_MAX_TENOR],
                'rate_permil' => ['required', 'numeric', 'min:0'],
                'effective_date' => ['required', 'date'],
                'sumber' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
            ],
            SettingSection::RateKendaraan => [
                'perusahaan' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
                'jenis_pertanggungan' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
                'harga_min' => ['required', 'numeric', 'min:0'],
                'harga_max' => ['required', 'numeric', 'min:0', 'gte:harga_min'],
                'rate' => ['required', 'numeric', 'min:0'],
                'effective_date' => ['required', 'date'],
                'sumber' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
            ],
            SettingSection::CollateralCategory => [
                'nama' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_NAME],
                'persen_max' => ['required', 'numeric', 'between:0,1'],
                'pasal_pojk' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
                'catatan' => ['nullable', 'string'],
                'sumber' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
                'effective_date' => ['required', 'date'],
                'is_active' => ['boolean'],
            ],
            SettingSection::ChecklistDokumen => [
                'kode' => ['required', 'string', 'max:20'],
                'nama' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_NAME],
                'kategori' => ['required', Rule::enum(KategoriChecklist::class)],
                'wajib' => ['boolean'],
                'urutan' => ['required', 'integer', 'min:1'],
                'is_active' => ['boolean'],
                'sumber' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
            ],
            SettingSection::TemplateDokumen => [
                'kode' => ['required', 'string', 'max:20'],
                'nama' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_NAME],
                'version' => ['required', 'string', 'max:20'],
                'file_path' => ['nullable', 'string', 'max:500'],
                'status' => ['required', Rule::enum(StatusTemplate::class)],
                'effective_date' => ['required', 'date'],
                'sumber' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_TEXT],
            ],
            SettingSection::Produk => [
                'cabang_id' => ['required', 'uuid', 'exists:cabang,id'],
                'kode' => ['required', 'string', 'max:20'],
                'nama' => ['required', 'string', 'max:'.Konstanta::SETTING_MAX_NAME],
                'tipe' => ['required', Rule::enum(ProdukTipe::class)],
                'rate_default' => ['required', 'numeric', 'between:0,1'],
                'tenor_min' => ['required', 'integer', 'between:1,'.Konstanta::SETTING_MAX_TENOR],
                'tenor_max' => ['required', 'integer', 'between:1,'.Konstanta::SETTING_MAX_TENOR, 'gte:tenor_min'],
                'biaya_default' => ['nullable', 'array'],
                'is_active' => ['boolean'],
            ],
            SettingSection::PolicyParameter, SettingSection::ApprovalMatrix => [],
        };
    }

    /**
     * @return array<string, list<string>>
     */
    public function policyGroups(): array
    {
        return [
            'Kapasitas & DSCR' => ['margin_kapasitas_bayar', 'minimum_dscr', 'dscr_ideal', 'idir_threshold_tpp'],
            'LTV, Plafon & Balloon' => ['maksimum_ltv', 'plafon_kewajaran_multiplier', 'rasio_balloon_layak', 'rasio_balloon_perhatian'],
            'Bobot 5C & Rating' => [
                'bobot_character', 'bobot_capacity', 'bobot_capital', 'bobot_collateral', 'bobot_condition',
                'rating_sangat_layak_min', 'rating_layak_min', 'rating_bersyarat_min',
            ],
            'SLIK' => ['threshold_slik_macet', 'threshold_slik_kol3', 'threshold_slik_nominal'],
            'Sensitivitas & Hari Kerja' => [
                'sensitivitas_omzet_ringan', 'sensitivitas_omzet_berat', 'sensitivitas_biaya',
                'hari_kerja_per_tahun', 'hari_persediaan_default',
            ],
            'Biaya' => ['biaya_provisi_default', 'biaya_administrasi_default', 'biaya_survey_default', 'biaya_lain_bulanan'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function policyFieldLabels(): array
    {
        return [
            'margin_kapasitas_bayar' => 'Margin kapasitas bayar',
            'minimum_dscr' => 'DSCR minimum',
            'dscr_ideal' => 'DSCR ideal',
            'maksimum_ltv' => 'LTV maksimum',
            'hari_kerja_per_tahun' => 'Hari kerja per tahun',
            'sensitivitas_omzet_ringan' => 'Sensitivitas omzet ringan',
            'sensitivitas_omzet_berat' => 'Sensitivitas omzet berat',
            'sensitivitas_biaya' => 'Sensitivitas biaya',
            'bobot_character' => 'Bobot character',
            'bobot_capacity' => 'Bobot capacity',
            'bobot_capital' => 'Bobot capital',
            'bobot_collateral' => 'Bobot collateral',
            'bobot_condition' => 'Bobot condition',
            'rating_sangat_layak_min' => 'Rating sangat layak minimum',
            'rating_layak_min' => 'Rating layak minimum',
            'rating_bersyarat_min' => 'Rating bersyarat minimum',
            'threshold_slik_macet' => 'Batas SLIK macet',
            'threshold_slik_kol3' => 'Batas SLIK KOL 3',
            'threshold_slik_nominal' => 'Batas nominal SLIK',
            'rasio_balloon_layak' => 'Rasio balloon layak',
            'rasio_balloon_perhatian' => 'Rasio balloon perhatian',
            'idir_threshold_tpp' => 'Ambang IDIR TPP',
            'plafon_kewajaran_multiplier' => 'Multiplier plafon kewajaran',
            'biaya_provisi_default' => 'Provisi default',
            'biaya_administrasi_default' => 'Administrasi default',
            'biaya_survey_default' => 'Survey default',
            'biaya_lain_bulanan' => 'Biaya lain per bulan',
            'hari_persediaan_default' => 'Hari persediaan default',
            'skor_band_draft' => 'Izinkan skor band draft',
        ];
    }

    /**
     * @return list<array{key: string, label: string, type: string, step?: string, options?: list<string>}>
     */
    public function referenceFields(SettingSection $section): array
    {
        return match ($section) {
            SettingSection::RateJiwa => [
                ['key' => 'perusahaan', 'label' => 'Perusahaan', 'type' => 'text'],
                ['key' => 'usia_min', 'label' => 'Usia minimum', 'type' => 'number'],
                ['key' => 'usia_max', 'label' => 'Usia maksimum', 'type' => 'number'],
                ['key' => 'tenor_min', 'label' => 'Tenor minimum (bulan)', 'type' => 'number'],
                ['key' => 'tenor_max', 'label' => 'Tenor maksimum (bulan)', 'type' => 'number'],
                ['key' => 'rate_permil', 'label' => 'Rate permil', 'type' => 'number', 'step' => '0.0001'],
                ['key' => 'effective_date', 'label' => 'Tanggal berlaku', 'type' => 'date'],
                ['key' => 'sumber', 'label' => 'Sumber', 'type' => 'text'],
            ],
            SettingSection::RateKebakaran => [
                ['key' => 'perusahaan', 'label' => 'Perusahaan', 'type' => 'text'],
                ['key' => 'usia_min', 'label' => 'Usia minimum', 'type' => 'number'],
                ['key' => 'usia_max', 'label' => 'Usia maksimum', 'type' => 'number'],
                ['key' => 'masa_asuransi', 'label' => 'Masa asuransi (bulan)', 'type' => 'number'],
                ['key' => 'rate_permil', 'label' => 'Rate permil', 'type' => 'number', 'step' => '0.0001'],
                ['key' => 'effective_date', 'label' => 'Tanggal berlaku', 'type' => 'date'],
                ['key' => 'sumber', 'label' => 'Sumber', 'type' => 'text'],
            ],
            SettingSection::RateKendaraan => [
                ['key' => 'perusahaan', 'label' => 'Perusahaan', 'type' => 'text'],
                ['key' => 'jenis_pertanggungan', 'label' => 'Jenis pertanggungan', 'type' => 'text'],
                ['key' => 'harga_min', 'label' => 'Harga minimum', 'type' => 'number'],
                ['key' => 'harga_max', 'label' => 'Harga maksimum', 'type' => 'number'],
                ['key' => 'rate', 'label' => 'Rate', 'type' => 'number', 'step' => '0.000001'],
                ['key' => 'effective_date', 'label' => 'Tanggal berlaku', 'type' => 'date'],
                ['key' => 'sumber', 'label' => 'Sumber', 'type' => 'text'],
            ],
            SettingSection::CollateralCategory => [
                ['key' => 'nama', 'label' => 'Kategori agunan', 'type' => 'text'],
                ['key' => 'persen_max', 'label' => 'Persen maksimum (0-1)', 'type' => 'number', 'step' => '0.0001'],
                ['key' => 'pasal_pojk', 'label' => 'Pasal POJK', 'type' => 'text'],
                ['key' => 'catatan', 'label' => 'Catatan', 'type' => 'text'],
                ['key' => 'sumber', 'label' => 'Sumber', 'type' => 'text'],
                ['key' => 'effective_date', 'label' => 'Tanggal berlaku', 'type' => 'date'],
            ],
            SettingSection::ChecklistDokumen => [
                ['key' => 'kode', 'label' => 'Kode', 'type' => 'text'],
                ['key' => 'nama', 'label' => 'Nama dokumen', 'type' => 'text'],
                ['key' => 'kategori', 'label' => 'Kategori', 'type' => 'select', 'options' => array_map(static fn (KategoriChecklist $case): string => $case->value, KategoriChecklist::cases())],
                ['key' => 'urutan', 'label' => 'Urutan', 'type' => 'number'],
                ['key' => 'sumber', 'label' => 'Sumber', 'type' => 'text'],
            ],
            SettingSection::TemplateDokumen => [
                ['key' => 'kode', 'label' => 'Kode', 'type' => 'text'],
                ['key' => 'nama', 'label' => 'Nama template', 'type' => 'text'],
                ['key' => 'version', 'label' => 'Versi', 'type' => 'text'],
                ['key' => 'file_path', 'label' => 'Path file', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => array_map(static fn (StatusTemplate $case): string => $case->value, StatusTemplate::cases())],
                ['key' => 'effective_date', 'label' => 'Tanggal berlaku', 'type' => 'date'],
                ['key' => 'sumber', 'label' => 'Sumber', 'type' => 'text'],
            ],
            SettingSection::Produk => [
                ['key' => 'kode', 'label' => 'Kode produk', 'type' => 'text'],
                ['key' => 'nama', 'label' => 'Nama produk', 'type' => 'text'],
                ['key' => 'tipe', 'label' => 'Tipe', 'type' => 'select', 'options' => array_map(static fn (ProdukTipe $case): string => $case->value, ProdukTipe::cases())],
                ['key' => 'rate_default', 'label' => 'Rate default (0-1)', 'type' => 'number', 'step' => '0.0001'],
                ['key' => 'tenor_min', 'label' => 'Tenor minimum (bulan)', 'type' => 'number'],
                ['key' => 'tenor_max', 'label' => 'Tenor maksimum (bulan)', 'type' => 'number'],
            ],
            default => [],
        };
    }
}
