<?php

declare(strict_types=1);

namespace App\Services\Document;

use App\Enums\JenisDokumen;
use App\Enums\LedgerEvent;
use App\Enums\PihakSignature;
use App\Enums\StatusTemplate;
use App\Enums\StatusTtd;
use App\Models\Cabang;
use App\Models\Dokumen;
use App\Models\PermohonanKredit;
use App\Models\RefTemplateDokumen;
use App\Models\SignatureLog;
use App\Models\User;
use App\Services\Ledger\LedgerService;
use App\Services\Workflow\NomorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

final class DocumentService
{
    public function __construct(
        private readonly DocumentGeneratorRegistry $registry,
        private readonly NomorService $nomor,
        private readonly LedgerService $ledger,
    ) {}

    public function generate(PermohonanKredit $permohonan, JenisDokumen $jenis, ?User $user = null): Dokumen
    {
        $template = RefTemplateDokumen::query()
            ->where('kode', $jenis->value)
            ->where('status', StatusTemplate::APPROVED->value)
            ->first();

        abort_if($template === null, 422, 'Template dokumen belum APPROVED (BR-07).');

        $generator = $this->registry->for($jenis);
        $cabang = Cabang::query()->findOrFail($permohonan->cabang_id);

        $existing = Dokumen::query()
            ->where('permohonan_id', $permohonan->id)
            ->where('jenis_dokumen', $jenis->value)
            ->latest('created_at')
            ->first();

        $nomor = $existing !== null ? (string) $existing->nomor_dokumen : '';

        if ($nomor === '') {
            $nomor = $this->nomor->dokumen($cabang, $jenis->value);
        }

        $tanggal = now()->toDateString();

        /** @var view-string $viewName */
        $viewName = $generator->view();

        $html = view($viewName, array_merge($generator->data($permohonan), [
            'jenis' => $jenis->value,
            'nomor' => $nomor,
            'tanggal' => $tanggal,
        ]))->render();

        $hash = hash('sha256', $html);

        if ($existing !== null && $existing->file_hash === $hash) {
            return $existing;
        }

        $pdf = Pdf::loadHTML($html)->output();
        $path = 'dokumen/'.$permohonan->id.'/'.$jenis->value.'-'.$hash.'.pdf';
        Storage::disk('local')->put($path, $pdf);

        $dokumen = Dokumen::query()->create([
            'cabang_id' => $permohonan->cabang_id,
            'permohonan_id' => $permohonan->id,
            'template_id' => $template->id,
            'jenis_dokumen' => $jenis->value,
            'nomor_dokumen' => $nomor,
            'tanggal_dokumen' => $tanggal,
            'file_path' => $path,
            'file_hash' => $hash,
            'status_ttd' => StatusTtd::DRAFT->value,
            'ukuran_bytes' => strlen($pdf),
            'sumber' => 'GENERATE',
        ]);

        $this->ledger->record(
            LedgerEvent::DokumenDibuat,
            'dokumen',
            (string) $dokumen->id,
            ['jenis_dokumen' => $jenis->value, 'nomor' => $nomor, 'hash' => $hash],
            $user,
        );

        return $dokumen;
    }

    /**
     * @return list<Dokumen>
     */
    public function generateAll(PermohonanKredit $permohonan, ?User $user = null): array
    {
        $hasil = [];

        foreach (JenisDokumen::templateCases() as $jenis) {
            $hasil[] = $this->generate($permohonan, $jenis, $user);
            gc_collect_cycles();
        }

        return $hasil;
    }

    public function sign(
        Dokumen $dokumen,
        User $user,
        PihakSignature $pihak,
        string $nama,
        ?string $jabatan = null,
        ?string $ttdHash = null,
        ?string $ttdFilePath = null,
    ): SignatureLog {
        $signature = SignatureLog::query()->create([
            'dokumen_id' => $dokumen->id,
            'pihak' => $pihak->value,
            'nama' => $nama,
            'jabatan' => $jabatan,
            'ttd_hash' => $ttdHash,
            'ttd_file_path' => $ttdFilePath,
            'ip_address' => request()->ip(),
        ]);

        $dokumen->update([
            'status_ttd' => StatusTtd::DITANDATANGANI->value,
            'tanggal_ttd' => now()->toDateString(),
        ]);

        $this->ledger->record(
            LedgerEvent::DokumenDitandatangani,
            'dokumen',
            (string) $dokumen->id,
            ['pihak' => $pihak->value, 'nama' => $nama],
            $user,
        );

        return $signature;
    }
}
