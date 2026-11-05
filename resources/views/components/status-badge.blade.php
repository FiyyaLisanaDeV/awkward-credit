@props(['status' => null, 'label' => null])

@php
    $tone = match (true) {
        in_array($status, ['APPROVED', 'ACTIVE', 'LUNAS', 'LAYAK', 'SANGAT LAYAK', 'DONE', 'COMMITTED', 'SELESAI', 'DISETUJUI'], true) => 'success',
        in_array($status, ['REJECTED', 'TIDAK LAYAK', 'DITOLAK', 'FAILED', 'BATAL', 'MACET'], true) => 'danger',
        in_array($status, ['SUBMITTED', 'IN_REVIEW', 'PENDING', 'DRAFT', 'PROCESSING', 'PERHATIAN', 'DISETUJUI DENGAN SYARAT'], true) => 'warning',
        default => 'neutral',
    };
@endphp

<span class="badge badge-{{ $tone }}">{{ $label ?? $status ?? '—' }}</span>
