@extends('layouts.app', ['title' => __($meta['label'])])

@section('content')
    <div class="page-head between">
        <div>
            <div class="small muted"><a href="{{ route('admin.index') }}">{{ __('setting.title') }}</a> / {{ __($meta['label']) }}</div>
            <h1>{{ __($meta['label']) }}</h1>
            <p class="muted mb-0">{{ __($meta['description']) }}</p>
        </div>
        <div class="flex gap-2">
            <a class="btn btn-sm" href="{{ route('admin.inbox') }}">{{ __('setting.inbox') }}</a>
            <a class="btn btn-sm" href="{{ route('admin.history', ['section' => $section->value]) }}">{{ __('setting.history') }}</a>
        </div>
    </div>

    @if ($activeVersion = $meta['versioned'] ? $active?->version : null)
        <div class="alert alert-info">
            {{ __('setting.active_version') }}: <span class="mono">{{ $activeVersion }}</span>
            · {{ __('setting.effective_date') }}: {{ $active?->effective_date?->format('d/m/Y') }}
        </div>
    @endif

    <div class="grid grid-2">
        <div class="card">
            <h2>{{ __('setting.active_data') }}</h2>

            @if ($section->value === 'POLICY_PARAMETER')
                @if ($active)
                    <table class="table">
                        <tbody>
                            @foreach ($policyLabels as $key => $label)
                                @php $value = $active->params[$key] ?? null; @endphp
                                <tr>
                                    <th>{{ $label }}</th>
                                    <td class="num">
                                        @if (is_array($value))
                                            <span class="mono small">{{ json_encode($value) }}</span>
                                        @elseif (is_bool($value))
                                            {{ $value ? 'Ya' : 'Tidak' }}
                                        @else
                                            {{ $value ?? '-' }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="muted">{{ __('setting.empty') }}</p>
                @endif
            @elseif ($section->value === 'APPROVAL_MATRIX')
                @if ($active)
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="num">Plafon min</th>
                                    <th class="num">Plafon max</th>
                                    <th>Level</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($active->matrix as $row)
                                    <tr>
                                        <td class="num">{{ number_format((float) $row['plafon_min'], 0, ',', '.') }}</td>
                                        <td class="num">{{ isset($row['plafon_max']) ? number_format((float) $row['plafon_max'], 0, ',', '.') : '∞' }}</td>
                                        <td>{{ $row['level'] }}</td>
                                        <td class="mono">{{ $row['role'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="muted">{{ __('setting.empty') }}</p>
                @endif
            @else
                @if ($active->isNotEmpty())
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    @foreach ($fields as $field)
                                        <th>{{ $field['label'] }}</th>
                                    @endforeach
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($active as $row)
                                    <tr>
                                        @foreach ($fields as $field)
                                            <td class="{{ in_array($field['type'], ['number'], true) ? 'num' : '' }}">
                                                {{ $row->{$field['key']} ?? '-' }}
                                            </td>
                                        @endforeach
                                        <td class="nowrap">
                                            @can('setting.edit')
                                                <a class="btn btn-sm" href="{{ route('admin.section', ['section' => $section->value, 'target' => $row->id]) }}">{{ __('setting.update') }}</a>
                                                @if (isset($row->is_active))
                                                    <form method="POST" action="{{ route('admin.store', $section->value) }}" class="inline-form">
                                                        @csrf
                                                        <input type="hidden" name="action" value="{{ $row->is_active ? 'DEACTIVATE' : 'ACTIVATE' }}">
                                                        <input type="hidden" name="target_id" value="{{ $row->id }}">
                                                        <button type="submit" class="btn btn-sm">{{ $row->is_active ? __('setting.deactivate') : __('setting.activate') }}</button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="muted">{{ __('setting.empty') }}</p>
                @endif
            @endif
        </div>

        @can('setting.edit')
            <div class="card">
                <h2>{{ $draftRevision ? __('setting.edit_revision') : __('setting.create_revision') }}</h2>
                <p class="muted small">{{ __('setting.locked') }}</p>

                <form method="POST" action="{{ $draftRevision ? route('admin.update', $draftRevision) : route('admin.store', $section->value) }}">
                    @csrf
                    @if ($draftRevision)
                        @method('PATCH')
                    @endif
                    <input type="hidden" name="action" value="{{ $targetId ? 'UPDATE' : 'CREATE' }}">
                    @if ($targetId)
                        <input type="hidden" name="target_id" value="{{ $targetId }}">
                    @endif

                    @if ($draftRevision)
                        <x-input name="note" label="{{ __('setting.note') }}" value="{{ old('note', $draftRevision->note) }}" />
                    @else
                        <x-input name="note" label="{{ __('setting.note') }}" value="{{ old('note') }}" />
                    @endif

                    @if ($section->value === 'POLICY_PARAMETER')
                        @include('settings.sections.policy')
                    @elseif ($section->value === 'APPROVAL_MATRIX')
                        @include('settings.sections.approval-matrix')
                    @else
                        @include('settings.sections.reference')
                    @endif

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">{{ __('setting.save_draft') }}</button>
                        @if ($target)
                            <a class="btn" href="{{ route('admin.section', $section->value) }}">{{ __('setting.create') }}</a>
                        @endif
                    </div>
                </form>
            </div>
        @else
            <div class="card">
                <h2>{{ __('setting.create_revision') }}</h2>
                <p class="muted">{{ __('setting.readonly') }}</p>
            </div>
        @endcan
    </div>

    <div class="card">
        <h2>{{ __('setting.revisions') }}</h2>
        @include('settings.partials.revisions', ['revisions' => $revisions])
    </div>
@endsection
