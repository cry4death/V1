@extends('layouts.app')

@section('title', 'Блог — Маяк Здоровья')
@section('body_class', 'blog-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-blog-page.css') }}">
@endpush

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Блог</span>
        </nav>
    </div>

    <section id="blog" class="blog-section">
        <div class="blog-sea-decor" aria-hidden="true">
            <i class="fa-solid fa-anchor bsd--anchor-lg"></i>
            <i class="fa-solid fa-tower-observation bsd--lighthouse"></i>
            <i class="fa-solid fa-ship bsd--ship"></i>
            <i class="fa-solid fa-compass bsd--compass"></i>
            <i class="fa-solid fa-anchor bsd--anchor-sm"></i>
            <i class="fa-solid fa-water bsd--wave"></i>
            <i class="fa-solid fa-fish bsd--fish"></i>
            <i class="fa-solid fa-binoculars bsd--binoculars"></i>
            <i class="fa-solid fa-life-ring bsd--lifebuoy"></i>
            <i class="fa-solid fa-ship bsd--ship-low"></i>
            <i class="fa-solid fa-anchor bsd--anchor-left"></i>
            <i class="fa-solid fa-water bsd--wave-mid"></i>
            <i class="fa-solid fa-compass bsd--compass-low"></i>
            <i class="fa-solid fa-dharmachakra bsd--wheel-top"></i>
        </div>
        <div class="container">
            <div class="section-header">
                <h2>Блог</h2>
                <p>Полезные статьи о здоровье, профилактике заболеваний и медицинских услугах нашей клиники</p>
            </div>

            <div class="blog-tabs-wrap"></div>

            <div class="blog-container">
                @forelse ($articles as $article)
                    <a href="{{ route('blog.show', $article->slug) }}" class="blog-card animate-on-scroll" data-category="{{ $article->category->name ?? '' }}" style="text-decoration:none;color:inherit">
                        <div class="blog-image">
                            <img src="{{ $article->coverPublicUrl() }}" alt="{{ $article->title }}">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                @if ($article->category)
                                    <span class="blog-badge">{{ $article->category->name }}</span>
                                @endif
                                @if ($article->published_at)
                                    <span class="blog-date">{{ $article->published_at->translatedFormat('d F Y') }}</span>
                                @endif
                            </div>
                            <h3>{{ $article->title }}</h3>
                            <p>{{ $article->meta_description }}</p>
                            <div class="category-chevron">
                                <div class="category-chevron-icon"><i class="fa-solid fa-chevron-right"></i></div>
                            </div>
                        </div>
                    </a>
                @empty
                    <p>Статьи не найдены.</p>
                @endforelse
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('scripts/script-blog-page.js') }}"></script>
@endpush
