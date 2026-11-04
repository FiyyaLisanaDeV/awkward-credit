<?php

declare(strict_types=1);

namespace App\Enums;

enum LedgerEvent: string
{
    case SimulasiDijalankan = 'SIMULASI_DIJALANKAN';
    case AnalisisDisimpan = 'ANALISIS_DISIMPAN';
    case PermohonanDibuat = 'PERMOHONAN_DIBUAT';
    case PermohonanDiajukan = 'PERMOHONAN_DIAJUKAN';
    case PermohonanDikembalikan = 'PERMOHONAN_DIKEMBALIKAN';
    case PermohonanDisetujui = 'PERMOHONAN_DISETUJUI';
    case PermohonanDitolak = 'PERMOHONAN_DITOLAK';
    case DokumenDibuat = 'DOKUMEN_DIBUAT';
    case DokumenDitandatangani = 'DOKUMEN_DITANDATANGANI';
    case DokumenDiunggah = 'DOKUMEN_DIUNGGAH';
    case ChecklistDiverifikasi = 'CHECKLIST_DIVERIFIKASI';
    case ParameterDiaktifkan = 'PARAMETER_DIAKTIFKAN';
    case TemplateDisetujui = 'TEMPLATE_DISETUJUI';
    case SlikDiimpor = 'SLIK_DIIMPOR';
    case KtpDiekstraksi = 'KTP_DIEKSTRAKSI';
    case DataDiimpor = 'DATA_DIIMPOR';
    case ImportDibatalkan = 'IMPORT_DIBATALKAN';
    case AkadDicatat = 'AKAD_DICATAT';
    case PencairanDicatat = 'PENCAIRAN_DICATAT';
    case AngsuranDibayar = 'ANGSURAN_DIBAYAR';
    case AngsuranDireversal = 'ANGSURAN_DIREVERSAL';
    case AksesPii = 'AKSES_PII';
    case PenggunaDiaktifkan = 'PENGGUNA_DIAKTIFKAN';
}
