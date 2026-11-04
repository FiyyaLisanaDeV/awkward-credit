<?php

declare(strict_types=1);

namespace App\Enums;

enum MetodePembayaran: string
{
    case Tunai = 'TUNAI';
    case Transfer = 'TRANSFER';
    case PotongTpp = 'POTONG_TPP';
    case Lainnya = 'LAINNYA';
}
