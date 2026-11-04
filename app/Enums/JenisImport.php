<?php

declare(strict_types=1);

namespace App\Enums;

enum JenisImport: string
{
    case SlikXlsx = 'SLIK_XLSX';
    case SlikPdf = 'SLIK_PDF';
    case SlikTeks = 'SLIK_TEKS';
    case Ktp = 'KTP';
    case DebiturXlsx = 'DEBITUR_XLSX';
    case DokumenZip = 'DOKUMEN_ZIP';
    case WorkbookLama = 'WORKBOOK_LAMA';
    case PembayaranCsv = 'PEMBAYARAN_CSV';
}
