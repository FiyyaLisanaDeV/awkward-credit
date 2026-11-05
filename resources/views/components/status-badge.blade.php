@props(['status' => null, 'label' => null])

@php
    $tone = match (true) {
        in_array($status, ['APPROVED', 'ACTIVE', 'LUNAS', 'LAYAK', 'SANGAT LAYAK', 'DONE', 'COMMITTED', 'SELESAI', 'DISETUJUI', 'SIGNED'], true) => 'success',
        in_array($status, ['REJECTED', 'TIDAK LAYAK', 'DITOLAK', 'FAILED', 'BATAL', 'MACET', 'NPL'], true) => 'danger',
        in_array($status, ['SUBMITTED', 'IN_REVIEW', 'PENDING', 'DRAFT', 'PROCESSING', 'PERHATIAN', 'DISETUJUI DENGAN SYARAT', 'RETURNED', 'HOLD'], true) => 'warning',
        in_array($status, ['ANALYZED', 'RECOMMENDED'], true) => 'info',
        default => 'neutral',
    };
@endphp

<span class="badge badge-{{ $tone }}">{{ $label ?? $status ?? '—' }}</span>
