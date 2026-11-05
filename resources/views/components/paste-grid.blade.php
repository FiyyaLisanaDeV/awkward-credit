@props(['judul' => null, 'petunjuk' => null])

<div class="paste-grid">
    @if ($judul)
        <h4>{{ $judul }}</h4>
    @endif
    @if ($petunjuk)
        <p class="hint">{{ $petunjuk }}</p>
    @endif
    {{ $slot }}
</div>
