<?php

declare(strict_types=1);

namespace App\Services\Document\Generators;

use App\Enums\JenisDokumen;
use App\Services\Document\AbstractDocumentGenerator;

final class Doc14ChecklistDokumen extends AbstractDocumentGenerator
{
    public function kode(): JenisDokumen
    {
        return JenisDokumen::Doc14;
    }
}
