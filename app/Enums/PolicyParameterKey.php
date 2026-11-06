<?php

declare(strict_types=1);

namespace App\Enums;

enum PolicyParameterKey: string
{
    case MarginKapasitasBayar = 'margin_kapasitas_bayar';
    case MinimumDscr = 'minimum_dscr';
    case DscrIdeal = 'dscr_ideal';
    case MaksimumLtv = 'maksimum_ltv';
    case HariKerjaPerTahun = 'hari_kerja_per_tahun';
    case SensitivitasOmzetRingan = 'sensitivitas_omzet_ringan';
    case SensitivitasOmzetBerat = 'sensitivitas_omzet_berat';
    case SensitivitasBiaya = 'sensitivitas_biaya';
    case BobotCharacter = 'bobot_character';
    case BobotCapacity = 'bobot_capacity';
    case BobotCapital = 'bobot_capital';
    case BobotCollateral = 'bobot_collateral';
    case BobotCondition = 'bobot_condition';
    case RatingSangatLayakMin = 'rating_sangat_layak_min';
    case RatingLayakMin = 'rating_layak_min';
    case RatingBersyaratMin = 'rating_bersyarat_min';
    case ThresholdSlikMacet = 'threshold_slik_macet';
    case ThresholdSlikKol3 = 'threshold_slik_kol3';
    case ThresholdSlikNominal = 'threshold_slik_nominal';
    case RasioBalloonLayak = 'rasio_balloon_layak';
    case RasioBalloonPerhatian = 'rasio_balloon_perhatian';
    case IdirThresholdTpp = 'idir_threshold_tpp';
    case IdirHardStopTpp = 'idir_hard_stop_tpp';
    case PlafonKewajaranMultiplier = 'plafon_kewajaran_multiplier';
    case BiayaProvisiDefault = 'biaya_provisi_default';
    case BiayaAdministrasiDefault = 'biaya_administrasi_default';
    case BiayaSurveyDefault = 'biaya_survey_default';
    case BiayaLainBulanan = 'biaya_lain_bulanan';
    case HariPersediaanDefault = 'hari_persediaan_default';
    case SkorBandDscr = 'skor_band_dscr';
    case SkorBandEquityAsset = 'skor_band_equity_asset';
    case SkorBandLtv = 'skor_band_ltv';
    case SkorBandSlik = 'skor_band_slik';
    case SkorBandDraft = 'skor_band_draft';
    case BobotKelengkapan = 'bobot_kelengkapan';
}
