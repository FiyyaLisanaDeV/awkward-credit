<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\MetodePembayaran;
use App\Models\JadwalAngsuran;
use App\Models\Kredit;
use App\Models\User;
use App\Services\Kredit\PembayaranService;
use App\Support\Konstanta;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class PembayaranForm extends Component
{
    public string $kredit_id = '';

    public string $periode = '';

    public string $tanggal_bayar = '';

    public string $metode = 'TUNAI';

    public string $denda = '0';

    public string $pesan = '';

    public function mount(string $kredit): void
    {
        $this->kredit_id = $kredit;
    }

    public function simpan(PembayaranService $service): void
    {
        $this->validate([
            'periode' => ['required', 'integer', 'min:1'],
            'tanggal_bayar' => ['nullable', 'date'],
            'metode' => ['required', 'string'],
            'denda' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        try {
            $kredit = Kredit::query()->findOrFail($this->kredit_id);

            $service->catat($kredit, $user, [
                'periode' => $this->periode,
                'tanggal_bayar' => $this->tanggal_bayar !== '' ? $this->tanggal_bayar : null,
                'metode' => $this->metode,
                'denda' => $this->denda,
            ]);

            session()->flash('status', __('pembayaran.recorded'));
            $this->redirectRoute('kredit.show', $this->kredit_id, navigate: true);
        } catch (HttpException $exception) {
            $this->pesan = $exception->getMessage();
        }
    }

    public function render(): View
    {
        return view('livewire.pembayaran-form', [
            'metodeOptions' => collect(MetodePembayaran::cases())
                ->mapWithKeys(static fn ($case): array => [$case->value => __('metode_pembayaran.'.$case->value)])
                ->all(),
            'periodeBelumBayar' => JadwalAngsuran::query()
                ->where('kredit_id', $this->kredit_id)
                ->where('status', Konstanta::JADWAL_BELUM_BAYAR)
                ->orderBy('periode')
                ->pluck('periode')
                ->all(),
        ]);
    }
}
