<?php

declare(strict_types=1);

namespace App\Services\Document;

use App\Enums\JenisDokumen;
use App\Services\Document\Contracts\DocumentGenerator;
use App\Services\Document\Generators\Doc01AnalisisKredit;
use App\Services\Document\Generators\Doc02PerhitunganJadwal;
use App\Services\Document\Generators\Doc03MemoPencairan;
use App\Services\Document\Generators\Doc04PerjanjianKredit;
use App\Services\Document\Generators\Doc05SuratKuasa;
use App\Services\Document\Generators\Doc06SuratPernyataan;
use App\Services\Document\Generators\Doc07PemblokiranTabungan;
use App\Services\Document\Generators\Doc08PersetujuanSlik;
use App\Services\Document\Generators\Doc09Spk;
use App\Services\Document\Generators\Doc10PernyataanAsuransi;
use App\Services\Document\Generators\Doc11PenyerahanJaminan;
use App\Services\Document\Generators\Doc12LembarKonfirmasi;
use App\Services\Document\Generators\Doc13PengkinianData;
use App\Services\Document\Generators\Doc14ChecklistDokumen;
use App\Services\Document\Generators\Doc15AsuransiJiwa;
use App\Services\Document\Generators\Doc16AsuransiKebakaran;
use App\Services\Document\Generators\Doc17AppraisalAgunan;

final class DocumentGeneratorRegistry
{
    /** @return array<string, class-string<DocumentGenerator>> */
    private function map(): array
    {
        return [
            JenisDokumen::Doc01->value => Doc01AnalisisKredit::class,
            JenisDokumen::Doc02->value => Doc02PerhitunganJadwal::class,
            JenisDokumen::Doc03->value => Doc03MemoPencairan::class,
            JenisDokumen::Doc04->value => Doc04PerjanjianKredit::class,
            JenisDokumen::Doc05->value => Doc05SuratKuasa::class,
            JenisDokumen::Doc06->value => Doc06SuratPernyataan::class,
            JenisDokumen::Doc07->value => Doc07PemblokiranTabungan::class,
            JenisDokumen::Doc08->value => Doc08PersetujuanSlik::class,
            JenisDokumen::Doc09->value => Doc09Spk::class,
            JenisDokumen::Doc10->value => Doc10PernyataanAsuransi::class,
            JenisDokumen::Doc11->value => Doc11PenyerahanJaminan::class,
            JenisDokumen::Doc12->value => Doc12LembarKonfirmasi::class,
            JenisDokumen::Doc13->value => Doc13PengkinianData::class,
            JenisDokumen::Doc14->value => Doc14ChecklistDokumen::class,
            JenisDokumen::Doc15->value => Doc15AsuransiJiwa::class,
            JenisDokumen::Doc16->value => Doc16AsuransiKebakaran::class,
            JenisDokumen::Doc17->value => Doc17AppraisalAgunan::class,
        ];
    }

    public function for(JenisDokumen $jenis): DocumentGenerator
    {
        $class = $this->map()[$jenis->value] ?? null;

        abort_if($class === null, 404, 'Generator dokumen tidak tersedia.');

        return app($class);
    }
}
