<?php

declare(strict_types=1);

namespace App\Enums;

enum JenisDokumen: string
{
    case Doc01 = 'DOC-01';
    case Doc02 = 'DOC-02';
    case Doc03 = 'DOC-03';
    case Doc04 = 'DOC-04';
    case Doc05 = 'DOC-05';
    case Doc06 = 'DOC-06';
    case Doc07 = 'DOC-07';
    case Doc08 = 'DOC-08';
    case Doc09 = 'DOC-09';
    case Doc10 = 'DOC-10';
    case Doc11 = 'DOC-11';
    case Doc12 = 'DOC-12';
    case Doc13 = 'DOC-13';
    case Doc14 = 'DOC-14';
    case Doc15 = 'DOC-15';
    case Doc16 = 'DOC-16';
    case Doc17 = 'DOC-17';
    case Lainnya = 'LAINNYA';

    /**
     * Kode template kredit (DOC-01..DOC-17), tanpa LAINNYA.
     *
     * @return list<self>
     */
    public static function templateCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $case): bool => $case !== self::Lainnya
        ));
    }
}
