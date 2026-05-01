@extends('layouts.app')

@section('title', $equipment->name . ' — Маяк Здоровья')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($equipment->summary ?: $equipment->description ?: $equipment->name), 180))
@section('body_class', 'medical-device-detail-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-medical-device-page.css') }}">
@endpush

@php
    $renderProse = function (?string $text): string {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }
        $blocks = preg_split('/\n{2,}/', $text) ?: [];
        $html = '';
        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }
            $html .= '<p>'.nl2br(e($block)).'</p>';
        }
        return $html;
    };
@endphp

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <a href="{{ route('about') }}#equipment" class="breadcrumb-link">О клинике</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">{{ $equipment->name }}</span>
        </nav>
    </div>

    <main class="medical-device-page">
        <div class="device-page-sea-decor" aria-hidden="true">
            <i class="fa-solid fa-tower-observation dpsd--lighthouse-upper-left"></i>
            <i class="fa-solid fa-compass dpsd--compass-upper-right"></i>
            <i class="fa-solid fa-ship dpsd--ship-upper-mid"></i>
            <i class="fa-solid fa-anchor dpsd--anchor-left-mid"></i>
            <i class="fa-solid fa-water dpsd--wave-left-lower"></i>
            <i class="fa-solid fa-fish dpsd--fish-center"></i>
            <i class="fa-solid fa-life-ring dpsd--lifebuoy-right-mid"></i>
            <i class="fa-solid fa-binoculars dpsd--binoculars-lower-left"></i>
            <i class="fa-solid fa-dharmachakra dpsd--wheel-lower-mid"></i>
            <i class="fa-solid fa-ship dpsd--ship-bottom-left"></i>
            <i class="fa-solid fa-compass dpsd--compass-bottom-center"></i>
            <i class="fa-solid fa-anchor dpsd--anchor-lower-right"></i>
            <i class="fa-solid fa-water dpsd--wave-bottom-right"></i>
        </div>

        <section class="device-hero">
            <div class="device-hero-decor" aria-hidden="true">
                <i class="fa-solid fa-compass dhd--compass-left"></i>
                <i class="fa-solid fa-anchor dhd--anchor-top"></i>
                <i class="fa-solid fa-ship dhd--ship-right"></i>
                <i class="fa-solid fa-water dhd--wave-bottom-right"></i>
                <i class="fa-solid fa-tower-observation dhd--lighthouse-right"></i>
            </div>
            <div class="container">
                <div class="device-hero-content">
                    <span class="device-kicker">
                        <i class="fa-solid fa-microscope"></i>
                        {{ $equipment->kicker ?: 'Медицинское оборудование' }}
                    </span>
                    <h1 class="device-title">{{ $equipment->name }}</h1>
                </div>
            </div>
        </section>

        <section class="device-overview-section">
            <div class="container">
                <div class="device-overview">
                    <div class="device-image-panel animate-on-scroll">
                        <div class="device-image-shell">
                            @if ($equipment->image)
                                <img src="{{ $equipment->imagePublicUrl() }}"
                                     alt="{{ $equipment->name }}"
                                     loading="eager"
                                     decoding="async">
                            @endif
                        </div>
                    </div>

                    <article class="device-content-panel animate-on-scroll prose">
                        @if ($equipment->subtitle)
                            <h2>{{ $equipment->subtitle }}</h2>
                        @endif
                        {!! $renderProse($equipment->description ?: $equipment->summary) !!}
                    </article>
                </div>
            </div>
        </section>
    </main>
@endsection
