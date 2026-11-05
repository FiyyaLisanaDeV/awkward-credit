<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ __('documents.'.$jenis.'.judul') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        th, td { border: 1px solid #999; padding: 4px 6px; vertical-align: top; }
        th { background: #eee; text-align: left; width: 22%; }
    </style>
</head>
<body>
    <h1>{{ __('documents.'.$jenis.'.judul') }}</h1>
    <p class="muted">Nomor: {{ $nomor }} &middot; Tanggal: {{ $tanggal }}</p>

    <table>
        <tr>
            <th>Debitur</th>
            <td>{{ $permohonan->debitur?->nama_ktp }}</td>
            <th>No. Permohonan</th>
            <td>{{ $permohonan->no_permohonan }}</td>
        </tr>
        <tr>
            <th>Plafon</th>
            <td>Rp {{ number_format((float) $permohonan->plafon_diajukan, 0, ',', '.') }}</td>
            <th>Tenor</th>
            <td>{{ $permohonan->tenor_diajukan }} bulan</td>
        </tr>
        <tr>
            <th>Sistem bunga</th>
            <td>{{ __('enums.sistem_suku_bunga.'.$permohonan->sistem_suku_bunga) }}</td>
            <th>Status</th>
            <td>{{ __('status_permohonan.'.$permohonan->status) }}</td>
        </tr>
    </table>

    <p>{{ __('documents.'.$jenis.'.isi') }}</p>

    <p class="muted">Dokumen dihasilkan dari data permohonan tersimpan. Nilai mengikuti snapshot, bukan input manual.</p>
</body>
</html>
