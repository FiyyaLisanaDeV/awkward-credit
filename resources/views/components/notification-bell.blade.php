<div class="notif" wire:poll.30s>
    <button type="button" class="btn btn-sm" wire:click="toggle">
        {{ __('notifikasi.judul') }}
        @if ($jumlah > 0)
            <span class="badge badge-danger">{{ $jumlah }}</span>
        @endif
    </button>

    @if ($terbuka)
        <div class="notif-panel card">
            @forelse ($daftar as $item)
                <div class="notif-item">
                    <b>{{ $item->judul }}</b>
                    <div class="small muted">{{ $item->pesan }}</div>
                </div>
            @empty
                <div class="small muted">{{ __('notifikasi.kosong') }}</div>
            @endforelse

            @if ($jumlah > 0)
                <button type="button" class="btn btn-sm mt-1" wire:click="tandaiSemua">
                    {{ __('notifikasi.tandai_semua') }}
                </button>
            @endif
        </div>
    @endif
</div>
