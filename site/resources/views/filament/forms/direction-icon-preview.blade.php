@once
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />
@endonce
@php
    $iconClass = $get('icon') ?: 'fa-stethoscope';
    $rawIconImage = $get('icon_image');
    $iconImagePath = null;
    if (filled($rawIconImage)) {
        if (is_array($rawIconImage)) {
            $iconImagePath = reset($rawIconImage) ?: null;
        } elseif (is_string($rawIconImage)) {
            $iconImagePath = $rawIconImage;
        }
    }
@endphp
<div
    class="space-y-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50"
    wire:key="direction-icon-preview-{{ $iconClass }}-{{ $iconImagePath }}"
>
    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Предпросмотр на сайте</div>

    @if (filled($iconImagePath))
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs text-gray-500 dark:text-gray-400">Своя иконка (имеет приоритет):</span>
            <span
                class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-600"
            >
                <img
                    src="{{ asset($iconImagePath) }}"
                    alt=""
                    class="max-h-8 max-w-8 object-contain"
                />
            </span>
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <span class="text-xs text-gray-500 dark:text-gray-400">Font Awesome:</span>
        <span
            class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-xl text-[#4682b4] shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:text-sky-300 dark:ring-gray-600"
        >
            <i class="fas {{ $iconClass }}"></i>
        </span>
        <code class="rounded bg-white px-2 py-1 text-xs text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600">{{ $iconClass }}</code>
    </div>
</div>
