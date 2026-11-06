@php
    $matrix = is_array($payload['matrix'] ?? null) ? array_values($payload['matrix']) : [];
    $matrix[] = ['plafon_min' => '', 'plafon_max' => '', 'level' => '', 'role' => ''];
@endphp

<p class="hint">{{ __('setting.matrix_hint') }}</p>

<x-input name="payload[effective_date]" label="{{ __('setting.effective_date') }}" type="date" required
    value="{{ old('payload.effective_date', $payload['effective_date'] ?? now()->toDateString()) }}" />

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th class="num">Plafon min</th>
                <th class="num">Plafon max (kosong = ∞)</th>
                <th>Level</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody id="matrix-body">
            @foreach ($matrix as $index => $row)
                <tr>
                    <td>
                        <input class="input" type="number" step="1" min="0"
                            name="payload[matrix][{{ $index }}][plafon_min]"
                            value="{{ old('payload.matrix.'.$index.'.plafon_min', $row['plafon_min'] ?? '') }}">
                    </td>
                    <td>
                        <input class="input" type="number" step="1" min="0"
                            name="payload[matrix][{{ $index }}][plafon_max]"
                            value="{{ old('payload.matrix.'.$index.'.plafon_max', $row['plafon_max'] ?? '') }}">
                    </td>
                    <td>
                        <input class="input" type="number" step="1" min="1"
                            name="payload[matrix][{{ $index }}][level]"
                            value="{{ old('payload.matrix.'.$index.'.level', $row['level'] ?? '') }}">
                    </td>
                    <td>
                        <select class="input" name="payload[matrix][{{ $index }}][role]">
                            <option value="">—</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('payload.matrix.'.$index.'.role', $row['role'] ?? '') === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<template id="matrix-row-template">
    <tr>
        <td><input class="input" type="number" step="1" min="0" name="payload[matrix][__INDEX__][plafon_min]"></td>
        <td><input class="input" type="number" step="1" min="0" name="payload[matrix][__INDEX__][plafon_max]"></td>
        <td><input class="input" type="number" step="1" min="1" name="payload[matrix][__INDEX__][level]"></td>
        <td>
            <select class="input" name="payload[matrix][__INDEX__][role]">
                <option value="">—</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>
        </td>
    </tr>
</template>

<div class="mt-1">
    <button type="button" class="btn btn-sm" onclick="addMatrixRow()">Tambah baris</button>
</div>

<script>
    let matrixIndex = {{ count($matrix) }};
    function addMatrixRow() {
        const template = document.getElementById('matrix-row-template').innerHTML.replaceAll('__INDEX__', matrixIndex++);
        document.getElementById('matrix-body').insertAdjacentHTML('beforeend', template);
    }
</script>
