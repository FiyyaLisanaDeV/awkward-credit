<?php

declare(strict_types=1);

namespace App\Services\Document\Contracts;

use App\Enums\JenisDokumen;
use App\Models\PermohonanKredit;

interface DocumentGenerator
{
    public function kode(): JenisDokumen;

    public function judul(): string;

    public function view(): string;

    /**
     * @return array<string, mixed>
     */
    public function data(PermohonanKredit $permohonan): array;
}
