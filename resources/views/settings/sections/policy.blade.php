@php
    $params = is_array($payload['params'] ?? null) ? $payload['params'] : [];
    $bands = [
        'skor_band_dscr' => ['field' => 'min', 'label' => 'Band DSCR', 'field_label' => 'DSCR minimum'],
        'skor_band_equity_asset' => ['field' => 'min', 'label' => 'Band equity/asset', 'field_label' => 'Equity/asset minimum'],
        'skor_band_ltv' => ['field' => 'max', 'label' => 'Band LTV', 'field_label' => 'LTV maksimum'],
        'skor_band_slik' => ['field' => 'maks_kol3', 'label' => 'Band SLIK', 'field_label' => 'Maksimum KOL 3'],
    ];
    $kelengkapanLabels = [
        'debitur' => 'Debitur',
        'permohonan' => 'Permohonan',
        'slik' => 'SLIK',
        'neraca' => 'Neraca',
        'kapasitas' => 'Kapasitas',
        'scoring' => 'Scoring',
        'dokumen' => 'Dokumen',
    ];
@endphp

<p class="hint">{{ __('setting.policy_hint') }}</p>

<x-input name="payload[effective_date]" label="{{ __('setting.effective_date') }}" type="date" required
    value="{{ old('payload.effective_date', $payload['effective_date'] ?? now()->toDateString()) }}" />

<div class="grid grid-2">
    @foreach ($policyGroups as $group => $keys)
        <div class="card card-tight span-2">
            <h3>{{ $group }}</h3>
            <div class="grid grid-2">
                @foreach ($keys as $key)
                    <x-input :name="'payload[params]['.$key.']'" :label="$policyLabels[$key] ?? $key" type="number" step="any" required
                        :value="old('payload.params.'.$key, $params[$key] ?? '')" />
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<div class="card card-tight">
    <h3>Band skor</h3>
    @foreach ($bands as $bandKey => $band)
        @php
            $rows = $params[$bandKey] ?? [];
            $rows = is_array($rows) && $rows !== [] ? array_values($rows) : [['min' => '', 'skor' => '']];
            $rows[] = [$band['field'] => '', 'skor' => ''];
        @endphp
        <h4>{{ $band['label'] }}</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ $band['field_label'] }}</th>
                    <th>Skor (1-5)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $index => $row)
                    <tr>
                        <td>
                            <input class="input" type="number" step="any"
                                name="payload[params][{{ $bandKey }}][{{ $index }}][{{ $band['field'] }}]"
                                value="{{ old('payload.params.'.$bandKey.'.'.$index.'.'.$band['field'], $row[$band['field']] ?? '') }}">
                        </td>
                        <td>
                            <input class="input" type="number" step="1" min="1" max="5"
                                name="payload[params][{{ $bandKey }}][{{ $index }}][skor]"
                                value="{{ old('payload.params.'.$bandKey.'.'.$index.'.skor', $row['skor'] ?? '') }}">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <label class="checkbox">
        <input type="checkbox" name="payload[params][skor_band_draft]" value="1"
            @checked(old('payload.params.skor_band_draft', $params['skor_band_draft'] ?? false))>
        {{ $policyLabels['skor_band_draft'] }}
    </label>
</div>

<div class="card card-tight">
    <h3>Bobot kelengkapan (total 100)</h3>
    <div class="grid grid-3">
        @foreach ($kelengkapanLabels as $key => $label)
            <x-input :name="'payload[params][bobot_kelengkapan]['.$key.']'" :label="$label" type="number" step="1" required
                :value="old('payload.params.bobot_kelengkapan.'.$key, $params['bobot_kelengkapan'][$key] ?? '')" />
        @endforeach
    </div>
</div>
