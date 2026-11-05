@props(['diff' => []])

@if ($diff === [])
    <p class="muted">{{ __('simulasi.no_diff') }}</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('simulasi.field') }}</th>
                <th class="num">{{ __('simulasi.awal') }}</th>
                <th class="num">{{ __('simulasi.akhir') }}</th>
                <th class="num">{{ __('simulasi.selisih') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($diff as $kunci => $baris)
                <tr>
                    <td>{{ $kunci }}</td>
                    <td class="num">{{ number_format($baris['awal'], 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($baris['akhir'], 2, ',', '.') }}</td>
                    <td class="num {{ $baris['selisih'] < 0 ? 'negatif' : 'positif' }}">
                        {{ $baris['selisih'] > 0 ? '+' : '' }}{{ number_format($baris['selisih'], 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
