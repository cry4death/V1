@extends('layouts.app')

@section('title', ($article->title ?? 'Статья') . ' — Блог | Маяк Здоровья')
@section('meta_description', $article->meta_description ?? $article->title)
@section('body_class', 'blog-post-detail-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-blog-page.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/style-blog-post.css') }}?v=8">
@endpush

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <a href="{{ route('blog.index') }}" class="breadcrumb-link">Блог</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">{{ $article->title }}</span>
        </nav>
    </div>

    <main class="blog-post-page">
        <div class="bp-page-sea-decor" aria-hidden="true">
            <i class="fa-solid fa-tower-observation bpsd--lighthouse-top"></i>
            <i class="fa-solid fa-ship bpsd--ship-upper"></i>
            <i class="fa-solid fa-compass bpsd--compass-left-high"></i>
            <i class="fa-solid fa-compass bpsd--compass-mid"></i>
            <i class="fa-solid fa-fish bpsd--fish-left"></i>
            <i class="fa-solid fa-anchor bpsd--anchor-mid"></i>
            <i class="fa-solid fa-water bpsd--wave-mid-left"></i>
            <i class="fa-solid fa-water bpsd--wave-right"></i>
            <i class="fa-solid fa-life-ring bpsd--lifebuoy-low"></i>
            <i class="fa-solid fa-binoculars bpsd--binoculars-low"></i>
            <i class="fa-solid fa-dharmachakra bpsd--wheel-mid"></i>
            <i class="fa-solid fa-ship bpsd--ship-bottom"></i>
            <i class="fa-solid fa-life-ring bpsd--lifebuoy-upper-right"></i>
            <i class="fa-solid fa-fish bpsd--fish-low-right"></i>
            <i class="fa-solid fa-anchor bpsd--anchor-bottom"></i>
            <i class="fa-solid fa-water bpsd--wave-bottom"></i>
        </div>

        <section class="bp-hero">
            <div class="bp-hero-decor" aria-hidden="true">
                <i class="fa-solid fa-tower-observation bph--lighthouse"></i>
                <i class="fa-solid fa-anchor bph--anchor"></i>
                <i class="fa-solid fa-compass bph--compass"></i>
                <i class="fa-solid fa-fish bph--fish"></i>
                <i class="fa-solid fa-dharmachakra bph--wheel"></i>
                <i class="fa-solid fa-ship bph--ship"></i>
                <i class="fa-solid fa-binoculars bph--binoculars"></i>
                <i class="fa-solid fa-water bph--wave"></i>
                <i class="fa-solid fa-life-ring bph--lifebuoy"></i>
            </div>
            <div class="container">
                <div class="bp-hero-inner">
                    <div class="bp-hero-content">
                        @if ($article->category)
                            <span class="bp-hero-badge"><i class="fa-solid fa-tag"></i> {{ $article->category->name }}</span>
                        @endif
                        <h1 class="bp-hero-title">{{ $article->title }}</h1>
                        <div class="bp-hero-meta">
                            @if ($article->published_at)
                                <span><i class="fa-regular fa-calendar"></i> {{ $article->published_at->translatedFormat('d F Y') }}</span>
                            @endif
                            @if ($article->reading_time)
                                <span><i class="fa-regular fa-clock"></i> {{ $article->reading_time }} мин чтения</span>
                            @endif
                            @if ($article->author)
                                <span><i class="fa-regular fa-user"></i> {{ $article->author }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="bp-hero-image">
                        <img src="{{ $article->coverPublicUrl() }}" alt="{{ $article->title }}">
                    </div>
                </div>
            </div>
        </section>

        <section class="bp-content-section">
            <div class="container">
                <div class="bp-layout">
                    <article class="bp-article prose">
                        {!! $article->contentForSite() !!}
                    </article>

                    <aside class="bp-sidebar">
                        <div class="bp-toc">
                            <h3 class="bp-toc-title"><i class="fa-solid fa-list"></i> Содержание</h3>
                            <nav>
                                <ul class="bp-toc-list" id="bp-toc-list"></ul>
                            </nav>
                        </div>

                        <div class="bp-share">
                            <h3 class="bp-share-title">Поделиться</h3>
                            <div class="bp-share-icons">
                                <a href="#" class="bp-share-btn" aria-label="Поделиться в VK"><i class="fa-brands fa-vk"></i></a>
                                <a href="#" class="bp-share-btn" aria-label="Поделиться в Telegram"><i class="fa-brands fa-telegram"></i></a>
                                <a href="#" class="bp-share-btn" aria-label="Поделиться в WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                                <a href="#" class="bp-share-btn" aria-label="Скопировать ссылку"><i class="fa-solid fa-link"></i></a>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        @if ($related->isNotEmpty())
            <section class="bp-related">
                <div class="container">
                    <div class="bp-related-header">
                        <h2 class="bp-related-title">Похожие статьи</h2>
                        <a href="{{ route('blog.index') }}" class="bp-related-all">Все статьи <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="bp-related-grid">
                        @foreach ($related as $item)
                            <a href="{{ route('blog.show', $item->slug) }}" class="blog-card-link">
                                <article class="blog-card animate-on-scroll">
                                    <div class="blog-image">
                                        <img src="{{ $item->coverPublicUrl() }}" alt="{{ $item->title }}">
                                    </div>
                                    <div class="blog-content">
                                        <div class="blog-meta">
                                            @if ($item->category)
                                                <span class="blog-badge">{{ $item->category->name }}</span>
                                            @endif
                                            @if ($item->published_at)
                                                <span class="blog-date">{{ $item->published_at->translatedFormat('d F Y') }}</span>
                                            @endif
                                        </div>
                                        <h3>{{ $item->title }}</h3>
                                        <p>{{ $item->meta_description }}</p>
                                        <div class="category-chevron">
                                            <span class="category-chevron-icon"><i class="fa-solid fa-chevron-right"></i></span>
                                        </div>
                                    </div>
                                </article>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>
@endsection

@push('scripts')
    <script>
        (function () {
            var article = document.querySelector('.bp-article');
            var list = document.getElementById('bp-toc-list');
            if (!article || !list) return;
            var headings = article.querySelectorAll('h2');
            if (!headings.length) {
                var toc = document.querySelector('.bp-toc');
                if (toc) toc.style.display = 'none';
                return;
            }
            headings.forEach(function (h, i) {
                if (!h.id) h.id = 'bp-section-' + (i + 1);
                var li = document.createElement('li');
                var a = document.createElement('a');
                a.href = '#' + h.id;
                a.textContent = h.textContent.trim();
                li.appendChild(a);
                list.appendChild(li);
            });
        })();
    </script>
@endpush
