@extends('layouts.app')

@section('title', 'Документы — Маяк Здоровья')
@section('meta_description', 'Документы медицинского центра «Маяк Здоровья»: лицензия, публичная оферта, политика конфиденциальности, правила внутреннего распорядка и регистрационные документы.')
@section('body_class', 'documents-page-view')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-documents-page.css') }}">
@endpush

@php
    $fallbackDocuments = [
        ['url' => 'https://license.gov.by/onelicense/174652', 'label' => 'Основной документ', 'title' => 'Лицензия на медицинскую деятельность', 'text' => 'Официальная лицензия, подтверждающая право клиники на оказание медицинских услуг.'],
        ['url' => 'https://lighthouse.by/dogovor.pdf', 'label' => 'Для пациентов', 'title' => 'Договор публичной оферты', 'text' => 'Условия оказания услуг, порядок взаимодействия и основные положения публичного договора.'],
        ['url' => 'https://lighthouse.by/politika-konfidenczialnosti/', 'label' => 'Конфиденциальность', 'title' => 'Политика конфиденциальности', 'text' => 'Информация об обработке персональных данных, защите приватности и правилах хранения данных.'],
        ['url' => 'https://lighthouse.by/pvr-dlya-pacientov.pdf', 'label' => 'Порядок посещения', 'title' => 'Правила внутреннего распорядка для пациентов', 'text' => 'Правила посещения клиники, взаимодействия с персоналом и порядок получения медицинской помощи.'],
        ['url' => 'https://lighthouse.by/registraciya.pdf', 'label' => 'Регистрация', 'title' => 'Свидетельство о государственной регистрации', 'text' => 'Регистрационный документ, подтверждающий официальную деятельность медицинского центра.'],
    ];
    $docsList = (isset($documents) && $documents->count())
        ? $documents->map(fn ($d) => ['url' => $d->url, 'label' => $d->label ?? '', 'title' => $d->title, 'text' => $d->text ?? ''])->all()
        : $fallbackDocuments;
@endphp

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Документы</span>
        </nav>
    </div>

    <main class="info-page documents-page">
        <section class="info-documents-section">
            <div class="documents-sea-decor" aria-hidden="true">
                <i class="documents-sea documents-sea--anchor-lg fa-solid fa-anchor"></i>
                <i class="documents-sea documents-sea--anchor-sm fa-solid fa-anchor"></i>
                <i class="documents-sea documents-sea--fish fa-solid fa-fish"></i>
                <i class="documents-sea documents-sea--wave fa-solid fa-water"></i>
                <i class="documents-sea documents-sea--ship fa-solid fa-ship"></i>
                <i class="documents-sea documents-sea--binoculars fa-solid fa-binoculars"></i>
                <i class="documents-sea documents-sea--compass fa-solid fa-compass"></i>
                <i class="documents-sea documents-sea--lighthouse fa-solid fa-tower-observation"></i>
            </div>
            <div class="container">
                <div class="section-header">
                    <h2>Документы</h2>
                    <p>Открывайте нужный документ в новой вкладке и быстро находите официальную информацию о работе медицинского центра.</p>
                </div>

                <div class="info-documents-grid">
                    @foreach ($docsList as $doc)
                        <a href="{{ $doc['url'] }}" class="info-document-card animate-on-scroll" target="_blank" rel="noopener noreferrer" aria-label="Открыть документ: {{ $doc['title'] }}">
                            <div class="info-document-body">
                                <span class="info-document-label">{{ $doc['label'] }}</span>
                                <h3>{{ $doc['title'] }}</h3>
                                <p>{{ $doc['text'] }}</p>
                            </div>
                            <div class="info-document-chevron"><i class="fa-solid fa-chevron-right"></i></div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    </main>
@endsection
