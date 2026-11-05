<div>
    <ol class="wizard-steps">
        @foreach ([1 => __('permohonan.step_pihak'), 2 => __('permohonan.step_plafon'), 3 => __('permohonan.step_tujuan'), 4 => __('permohonan.step_slik'), 5 => __('permohonan.step_review')] as $nomor => $judul)
            <li class="{{ $step === $nomor ? 'active' : ($step > $nomor ? 'done' : '') }}">
                <span class="step-no">{{ $nomor }}</span> {{ $judul }}
            </li>
        @endforeach
    </ol>

    @if ($nomorPermohonan !== '')
        <p class="hint">{{ __('permohonan.draft_no') }}: <strong>{{ $nomorPermohonan }}</strong></p>
    @endif

    <form wire:submit="submit" class="card">
        @if ($step === 1)
            <div class="grid grid-2">
                <x-select name="debitur_id" :label="__('permohonan.debitur')" wire:model="debitur_id" required
                    placeholder="{{ __('form.pilih') }}" :options="$debiturOptions" />
                <x-select name="produk_id" :label="__('permohonan.produk')" wire:model="produk_id" required
                    placeholder="{{ __('form.pilih') }}" :options="$produkOptions" />
            </div>
            <p class="hint">{{ __('permohonan.debitur_hint') }}</p>
            <a class="btn btn-sm" href="{{ route('debitur.create') }}">{{ __('debitur.new') }}</a>
        @elseif ($step === 2)
            <div class="grid grid-2">
                <x-money-input name="plafon_diajukan" :label="__('permohonan.plafon')" wire:model="plafon_diajukan" required />
                <x-input name="suku_bunga_tahunan" type="number" step="0.0001" :label="__('permohonan.suku_bunga')" wire:model="suku_bunga_tahunan" required hint="{{ __('permohonan.suku_bunga_hint') }}" />
                <x-input name="tenor_diajukan" type="number" :label="__('permohonan.tenor')" wire:model="tenor_diajukan" required />
                <x-select name="sistem_suku_bunga" :label="__('permohonan.sistem')" wire:model="sistem_suku_bunga" required :options="$sistemOptions" />
                <x-input name="tanggal_permohonan" type="date" :label="__('permohonan.tanggal')" wire:model="tanggal_permohonan" required />
                <x-input name="tanggal_target_realisasi" type="date" :label="__('permohonan.target_realisasi')" wire:model="tanggal_target_realisasi" />
                <x-input name="prioritas" :label="__('permohonan.prioritas')" wire:model="prioritas" />
            </div>
            <x-input name="kegunaan_pinjaman" :label="__('permohonan.kegunaan')" wire:model="kegunaan_pinjaman" required />
            @if ($sistem_suku_bunga === 'Grace Period')
                <div class="grid grid-2">
                    <x-input name="sumber_pelunasan_balloon" :label="__('permohonan.sumber_balloon')" wire:model="sumber_pelunasan_balloon" required />
                    <x-money-input name="perkiraan_penerimaan_balloon" :label="__('permohonan.perkiraan_balloon')" wire:model="perkiraan_penerimaan_balloon" required />
                </div>
            @endif
        @elseif ($step === 3)
            <div class="grid grid-2">
                <x-input name="sektor" :label="__('permohonan.sektor')" wire:model="sektor" />
                <x-input name="tujuan_penggunaan" :label="__('permohonan.tujuan')" wire:model="tujuan_penggunaan" />
                <x-input name="pinjaman_ke" type="number" :label="__('permohonan.pinjaman_ke')" wire:model="pinjaman_ke" />
                <x-input name="sumber_pembayaran_angsuran" :label="__('permohonan.sumber_angsuran')" wire:model="sumber_pembayaran_angsuran" />
            </div>
            <x-input name="catatan_ao" :label="__('permohonan.catatan')" wire:model="catatan_ao" />
        @elseif ($step === 4)
            <x-paste-grid :judul="__('permohonan.slik_title')" :petunjuk="__('permohonan.slik_hint')">
                <textarea class="input" rows="4" wire:model="paste" placeholder="BANK ABC;KPR;150000000;120000000;1;1500000"></textarea>
                @error('paste')<div class="error">{{ $message }}</div>@enderror
                <button type="button" class="btn btn-sm mt-1" wire:click="parsePaste">{{ __('permohonan.parse') }}</button>
            </x-paste-grid>

            <table class="table mt-2">
                <thead>
                    <tr>
                        <th>{{ __('permohonan.lembaga') }}</th>
                        <th>{{ __('permohonan.jenis_fasilitas') }}</th>
                        <th>{{ __('permohonan.plafon') }}</th>
                        <th>{{ __('permohonan.baki_debet') }}</th>
                        <th>{{ __('permohonan.kolektibilitas') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slikRows as $index => $row)
                        <tr wire:key="slik-{{ $index }}">
                            <td><input class="input" wire:model="slikRows.{{ $index }}.nama_lembaga"></td>
                            <td><input class="input" wire:model="slikRows.{{ $index }}.jenis_fasilitas"></td>
                            <td><input class="input" type="number" wire:model="slikRows.{{ $index }}.plafon"></td>
                            <td><input class="input" type="number" wire:model="slikRows.{{ $index }}.baki_debet"></td>
                            <td>
                                <select class="input" wire:model="slikRows.{{ $index }}.kolektibilitas_id">
                                    <option value="">{{ __('form.pilih') }}</option>
                                    @foreach ($kolektibilitasOptions as $id => $teks)
                                        <option value="{{ $id }}">{{ $teks }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><button type="button" class="btn btn-sm" wire:click="removeSlikRow({{ $index }})">×</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">{{ __('permohonan.slik_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            <button type="button" class="btn btn-sm" wire:click="addSlikRow">{{ __('permohonan.add_row') }}</button>
        @else
            <div class="review">
                <h4>{{ __('permohonan.review') }}</h4>
                <dl class="review-list">
                    <dt>{{ __('permohonan.debitur') }}</dt><dd>{{ $debiturOptions[$debitur_id] ?? '—' }}</dd>
                    <dt>{{ __('permohonan.produk') }}</dt><dd>{{ $produkOptions[$produk_id] ?? '—' }}</dd>
                    <dt>{{ __('permohonan.plafon') }}</dt><dd>Rp {{ number_format((float) $plafon_diajukan, 0, ',', '.') }}</dd>
                    <dt>{{ __('permohonan.suku_bunga') }}</dt><dd>{{ $suku_bunga_tahunan }}</dd>
                    <dt>{{ __('permohonan.tenor') }}</dt><dd>{{ $tenor_diajukan }} {{ __('form.bulan') }}</dd>
                    <dt>{{ __('permohonan.sistem') }}</dt><dd>{{ $sistemOptions[$sistem_suku_bunga] ?? $sistem_suku_bunga }}</dd>
                    <dt>{{ __('permohonan.slik_title') }}</dt><dd>{{ count($slikRows) }} {{ __('permohonan.fasilitas') }}</dd>
                </dl>
            </div>

            @error('submit')<div class="alert alert-danger">{{ $message }}</div>@enderror
        @endif

        <div class="wizard-actions">
            @if ($step > 1)
                <button type="button" class="btn" wire:click="previous">{{ __('form.back') }}</button>
            @endif
            @if ($step > 1 && $step < 5)
                <button type="button" class="btn" wire:click="saveDraft">{{ __('permohonan.save_draft') }}</button>
            @endif
            @if ($step < 5)
                <button type="button" class="btn btn-primary" wire:click="next">{{ __('form.next') }}</button>
            @else
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">{{ __('permohonan.submit') }}</button>
            @endif
        </div>
    </form>
</div>
