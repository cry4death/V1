@extends('layouts.app')

@section('title', $promotion->title . ' — Акция | Маяк Здоровья')
@section('meta_description', $promotion->short_description ?? $promotion->title)
@section('body_class', 'promotion-detail-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-promotions-page.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/style-one-promotion-page.css') }}?v=7">
@endpush

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <a href="{{ route('promotions.index') }}" class="breadcrumb-link">Акции</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">{{ $promotion->title }}</span>
        </nav>
    </div>

    <main class="promo-page">
        <div class="promo-page-sea-decor" aria-hidden="true">
            <i class="fa-solid fa-anchor ppsd--anchor-top"></i>
            <i class="fa-solid fa-ship ppsd--ship-upper"></i>
            <i class="fa-solid fa-anchor ppsd--anchor-left-mid"></i>
            <i class="fa-solid fa-compass ppsd--compass-mid"></i>
            <i class="fa-solid fa-water ppsd--wave-high-right"></i>
            <i class="fa-solid fa-fish ppsd--fish-mid"></i>
            <i class="fa-solid fa-water ppsd--wave-left"></i>
            <i class="fa-solid fa-life-ring ppsd--lifebuoy-mid"></i>
            <i class="fa-solid fa-tower-observation ppsd--lighthouse-low"></i>
            <i class="fa-solid fa-dharmachakra ppsd--wheel-right"></i>
            <i class="fa-solid fa-ship ppsd--ship-bottom"></i>
            <i class="fa-solid fa-compass ppsd--compass-low"></i>
            <i class="fa-solid fa-binoculars ppsd--binoculars-low"></i>
            <i class="fa-solid fa-life-ring ppsd--lifebuoy-bottom-right"></i>
            <i class="fa-solid fa-anchor ppsd--anchor-bottom"></i>
            <i class="fa-solid fa-water ppsd--wave-bottom"></i>
        </div>

        <section class="promo-hero" aria-label="Акция">
            <div class="promo-hero-decor" aria-hidden="true">
                <i class="fa-solid fa-anchor phero--anchor"></i>
                <i class="fa-solid fa-fish phero--fish"></i>
                <i class="fa-solid fa-compass phero--compass"></i>
                <i class="fa-solid fa-life-ring phero--lifebuoy"></i>
                <i class="fa-solid fa-dharmachakra phero--wheel"></i>
                <i class="fa-solid fa-ship phero--ship"></i>
                <i class="fa-solid fa-water phero--wave"></i>
                <i class="fa-solid fa-tower-observation phero--lighthouse"></i>
                <i class="fa-solid fa-binoculars phero--binoculars"></i>
            </div>
            <div class="container">
                <div class="promo-hero-inner">
                    <div class="promo-hero-content">
                        <span class="promo-hero-badge"><i class="fa-solid fa-percent"></i> Акция</span>
                        <h1 class="promo-hero-title">{{ $promotion->title }}</h1>
                        @if ($promotion->start_date || $promotion->end_date)
                            <div class="promo-hero-meta">
                                <span>
                                    <i class="fa-regular fa-calendar"></i>
                                    Действует
                                    @if ($promotion->start_date) с {{ $promotion->start_date->format('d.m.Y') }} @endif
                                    @if ($promotion->end_date) по {{ $promotion->end_date->format('d.m.Y') }} @endif
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="promo-hero-image promo-block">
                        <img src="{{ $promotion->imagePublicUrl('images/promos/promo-hero.jpg') }}" alt="{{ $promotion->title }}" class="zoomable-image">
                        <button type="button" class="promo-zoom-btn" aria-label="Увеличить изображение">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="promo-content-section">
            <div class="container">
                <div class="promo-layout">
                    <div class="promo-main-col">
                        <article class="promo-article prose">
                            @if (! empty($promotion->items))
                                <h2 id="section-included"><i class="fa-solid fa-clipboard-list promo-h2-icon"></i> Что входит в программу</h2>
                                <ul class="promo-icon-list">
                                    @foreach ($promotion->items as $item)
                                        <li>
                                            <span class="promo-icon-list__icon"><i class="fa-solid fa-chevron-right"></i></span>
                                            <span class="promo-icon-list__text">{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($promotion->full_description)
                                <div id="promo-content">
                                    {!! $promotion->fullDescriptionForSite() !!}
                                </div>
                            @endif

                            <div class="promo-disclaimer">
                                <p>* Скидка предоставляется на тарифную часть без учёта стоимости лекарственных средств, расходных материалов, изделий медицинского назначения и медицинской техники.</p>
                            </div>
                        </article>
                    </div>

                    <aside class="promo-sidebar">
                        <div class="promo-toc">
                            <h3 class="promo-toc-title"><i class="fa-solid fa-list"></i> Навигация</h3>
                            <nav>
                                <ul class="promo-toc-list" id="promo-toc-list"></ul>
                            </nav>
                        </div>

                        <div class="promo-share">
                            <h3 class="promo-share-title">Поделиться</h3>
                            <div class="promo-share-icons">
                                <a href="#" class="promo-share-btn" aria-label="Поделиться в VK"><i class="fa-brands fa-vk"></i></a>
                                <a href="#" class="promo-share-btn" aria-label="Поделиться в Telegram"><i class="fa-brands fa-telegram"></i></a>
                                <a href="#" class="promo-share-btn" aria-label="Поделиться в WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                                <a href="#" class="promo-share-btn" aria-label="Скопировать ссылку"><i class="fa-solid fa-link"></i></a>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        @if (isset($related) && $related->isNotEmpty())
            <section class="promo-related">
                <div class="container">
                    <div class="promo-related-header">
                        <h2 class="promo-related-title">Другие акции</h2>
                        <a href="{{ route('promotions.index') }}" class="promo-related-all">Все акции <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="promo-related-grid">
                        @foreach ($related as $item)
                            <a href="{{ route('promotions.show', $item->slug) }}" class="promo-card-link">
                                <article class="promo-card">
                                    <div class="promo-image">
                                        <img src="{{ asset($item->image ?: 'images/promos/promo-hero.jpg') }}" alt="{{ $item->title }}">
                                    </div>
                                    <div class="promo-content">
                                        @if ($item->category)
                                            <span class="promo-badge">{{ $item->category->name }}</span>
                                        @endif
                                        <h3 class="promo-title">{{ $item->title }}</h3>
                                        <p class="promo-description">{{ $item->short_description }}</p>
                                        <div class="promo-chevron">
                                            <span class="promo-chevron-icon"><i class="fa-solid fa-chevron-right"></i></span>
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
            var article = document.querySelector('.promo-article');
            var list = document.getElementById('promo-toc-list');
            if (!article || !list) return;
            var headings = article.querySelectorAll('h2');
            if (!headings.length) {
                var toc = document.querySelector('.promo-toc');
                if (toc) toc.style.display = 'none';
                return;
            }
            headings.forEach(function (h, i) {
                if (!h.id) h.id = 'promo-section-' + (i + 1);
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
