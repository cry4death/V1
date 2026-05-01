@props(['direction'])

@if (filled($direction->icon_image))
    <img
        {{ $attributes->class(['direction-custom-icon']) }}
        src="{{ asset($direction->icon_image) }}"
        alt=""
    />
@else
    <i class="fas {{ $direction->icon ?: 'fa-stethoscope' }}"></i>
@endif
