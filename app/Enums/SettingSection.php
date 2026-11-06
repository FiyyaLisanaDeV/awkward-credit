<?php

declare(strict_types=1);

namespace App\Enums;

enum SettingSection: string
{
    case PolicyParameter = 'POLICY_PARAMETER';
    case ApprovalMatrix = 'APPROVAL_MATRIX';
    case RateJiwa = 'RATE_JIWA';
    case RateKebakaran = 'RATE_KEBAKARAN';
    case RateKendaraan = 'RATE_KENDARAAN';
    case CollateralCategory = 'COLLATERAL_CATEGORY';
    case ChecklistDokumen = 'CHECKLIST_DOKUMEN';
    case TemplateDokumen = 'TEMPLATE_DOKUMEN';
    case Produk = 'PRODUK';
}
