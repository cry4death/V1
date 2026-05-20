@php
    $step = (int) ($step ?? 1);
    $labels = ['Услуга', 'Врач', 'Дата и время'];
    $stepUrls = $stepUrls ?? [];
@endphp
<nav class="booking-progress" aria-label="Этапы записи">
    <ol class="booking-progress__steps">
        @foreach ($labels as $i => $label)
            @php $n = $i + 1; @endphp
            <li @class([
                'booking-progress__step',
                'is-done' => $n < $step,
                'is-active' => $n === $step,
                'is-todo' => $n > $step,
            ])>
                @if ($n < $step && isset($stepUrls[$n]))
                    <a href="{{ $stepUrls[$n] }}" class="booking-progress__link">
                        <span class="booking-progress__num">{{ $n }}/3</span>
                        <span class="booking-progress__label">{{ $label }}</span>
                    </a>
                @else
                    <span class="booking-progress__num">{{ $n }}/3</span>
                    <span class="booking-progress__label">{{ $label }}</span>
                @endif
            </li>
            @if ($i < count($labels) - 1)
                <span class="booking-progress__arrow" aria-hidden="true">→</span>
            @endif
        @endforeach
    </ol>
</nav>
