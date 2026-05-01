@php
    $codeString = (string) ($code ?? '500');
    $codeChars = preg_split('//u', $codeString, -1, PREG_SPLIT_NO_EMPTY) ?: [];
@endphp
<section class="error-hero-section">
    <div class="error-sea-decor" aria-hidden="true">
        <i class="error-sea error-sea--anchor-lg fa-solid fa-anchor"></i>
        <i class="error-sea error-sea--ship fa-solid fa-ship"></i>
        <i class="error-sea error-sea--compass fa-regular fa-compass"></i>
        <i class="error-sea error-sea--binoculars fa-solid fa-binoculars"></i>
        <i class="error-sea error-sea--lifebuoy fa-solid fa-life-ring"></i>
        <i class="error-sea error-sea--lighthouse fa-solid fa-tower-observation"></i>
        <i class="error-sea error-sea--water fa-solid fa-water"></i>
        <i class="error-sea error-sea--anchor-sm fa-solid fa-anchor"></i>
        <i class="error-sea error-sea--wheel fa-solid fa-dharmachakra"></i>
        <i class="error-sea error-sea--ship-low fa-solid fa-ship"></i>
        <i class="error-sea error-sea--compass-low fa-solid fa-compass"></i>
        <i class="error-sea error-sea--wave-wide fa-solid fa-water"></i>
    </div>

    <div class="container">
        <div class="error-card">
            <div class="error-beacon-stage" aria-hidden="true">
                <div class="error-beacon-halo"></div>
                <div class="error-beam error-beam--left"></div>
                <div class="error-beam error-beam--right"></div>
                <div class="error-code-display">
                    @foreach ($codeChars as $char)
                        <span class="error-code-digit">{{ $char }}</span>
                    @endforeach
                </div>
            </div>

            <div class="error-header">
                <h1>{{ $heading ?? 'Что-то пошло не так' }}</h1>
                <p class="error-copy">{{ $copy ?? 'Следуйте за маяком, вернитесь на главную' }}</p>
            </div>

            <a href="{{ route('home') }}" class="btn primary-btn error-home-link">
                <i class="fa-solid fa-house"></i>
                На главную
            </a>
        </div>
    </div>
</section>
