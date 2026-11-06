@php
    $user = auth()->user();
    $badge = [
        'DRAFT' => 'badge',
        'PENDING' => 'badge badge-warning',
        'APPROVED' => 'badge badge-info',
        'REJECTED' => 'badge badge-danger',
        'APPLIED' => 'badge badge-success',
    ];
@endphp

@if ($revisions->isEmpty())
    <p class="muted">{{ __('setting.empty_history') }}</p>
@else
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('setting.created_at') }}</th>
                    <th>{{ __('setting.status') }}</th>
                    <th>{{ __('setting.action') }}</th>
                    <th>{{ __('setting.created_by') }}</th>
                    <th>{{ __('setting.note') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($revisions as $revision)
                    <tr>
                        <td class="nowrap">{{ $revision->created_at?->format('d/m/Y H:i') }}</td>
                        <td><span class="{{ $badge[$revision->status->value] ?? 'badge' }}">{{ __('status_revision.'.$revision->status->value) }}</span></td>
                        <td class="mono small">{{ $revision->action->value }}</td>
                        <td>{{ $revision->creator?->name ?? '-' }}</td>
                        <td class="small">{{ \Illuminate\Support\Str::limit((string) $revision->note, 60) }}</td>
                        <td class="nowrap">
                            @if ($revision->status->value === 'DRAFT')
                                @can('setting.edit')
                                    <a class="btn btn-sm" href="{{ route('admin.section', ['section' => $revision->section->value, 'revision' => $revision->id]) }}">{{ __('setting.edit_revision') }}</a>
                                    <form method="POST" action="{{ route('admin.submit', $revision) }}" class="inline-form">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">{{ __('setting.submit') }}</button>
                                    </form>
                                @endcan
                            @elseif ($revision->status->value === 'PENDING')
                                @can('setting.approve')
                                    @if ($revision->created_by !== $user?->id)
                                        <form method="POST" action="{{ route('admin.approve', $revision) }}" class="inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">{{ __('setting.approve') }}</button>
                                        </form>
                                    @else
                                        <span class="small muted">{{ __('setting.sod') }}</span>
                                    @endif
                                    <form method="POST" action="{{ route('admin.reject', $revision) }}" class="inline-form">
                                        @csrf
                                        <input class="input" name="reason" placeholder="{{ __('setting.reason') }}" required>
                                        <button type="submit" class="btn btn-sm btn-danger">{{ __('setting.reject') }}</button>
                                    </form>
                                @endcan
                            @elseif ($revision->status->value === 'APPROVED')
                                @can('setting.apply')
                                    <form method="POST" action="{{ route('admin.apply', $revision) }}" class="inline-form">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">{{ __('setting.apply') }}</button>
                                    </form>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
