<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Approval\ApprovalService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ApprovalAction extends Component
{
    public string $permohonan_id = '';

    public string $alasan = '';

    public string $pesan = '';

    public function mount(?string $permohonan = null): void
    {
        $this->permohonan_id = (string) ($permohonan ?? '');
    }

    public function approve(ApprovalService $service): void
    {
        $this->pesan = '';

        $this->jalankan(
            fn (PermohonanKredit $model, User $user) => $service->approve($model, $user, $this->alasan !== '' ? $this->alasan : null),
            __('approval.approved'),
        );
    }

    public function reject(ApprovalService $service): void
    {
        $this->validate(['alasan' => ['required', 'string', 'max:500']]);
        $this->pesan = '';

        $this->jalankan(
            fn (PermohonanKredit $model, User $user) => $service->reject($model, $user, $this->alasan),
            __('approval.rejected'),
        );
    }

    public function returnToAo(ApprovalService $service): void
    {
        $this->validate(['alasan' => ['required', 'string', 'max:500']]);
        $this->pesan = '';

        $this->jalankan(
            fn (PermohonanKredit $model, User $user) => $service->returnToAo($model, $user, $this->alasan),
            __('approval.returned'),
        );
    }

    public function render(): View
    {
        return view('livewire.approval-action');
    }

    private function jalankan(callable $aksi, string $sukses): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        try {
            $aksi(PermohonanKredit::query()->findOrFail($this->permohonan_id), $user);
            session()->flash('status', $sukses);
            $this->redirectRoute('approval.show', $this->permohonan_id, navigate: true);
        } catch (HttpException $exception) {
            $this->pesan = $exception->getMessage();
        }
    }
}
