<?php

declare(strict_types=1);

namespace App\Services\Document;

use App\Models\PermohonanKredit;
use App\Services\Document\Contracts\DocumentGenerator;

abstract class AbstractDocumentGenerator implements DocumentGenerator
{
    public function judul(): string
    {
        return (string) __('documents.'.$this->kode()->value.'.judul');
    }

    public function view(): string
    {
        return 'documents.'.$this->kode()->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function data(PermohonanKredit $permohonan): array
    {
        return ['permohonan' => $permohonan];
    }
}
