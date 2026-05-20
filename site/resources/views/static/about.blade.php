@extends('layouts.app')

@section('title', 'О клинике — Маяк Здоровья')
@section('meta_description', 'О медицинском центре «Маяк Здоровья» — история, ценности, направления и лицензии клиники экспертной медицины в Минске.')
@section('body_class', 'about-clinic-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-medical-services-page.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/style-about-clinic-page.css') }}">
@endpush

@php
    $story = $page->content['story'] ?? [
        ['image' => 'images/about/clinic-interior.jpg', 'title' => 'Стремительный старт', 'paragraphs' => ['Мы стартовали на рынке частной медицины Беларуси в 2020 году — в разгар первой волны коронавирусной инфекции. Уже в 2021 году вошли в <strong>ТОП-30 успешных проектов Беларуси по версии Forbes</strong>.', 'Сегодня клиника оказывает услуги по 19 направлениям медицины, включая уникальные предложения — лечение сахарного диабета методом помповой инсулинотерапии.']],
        ['image' => 'images/about/medical-equipment.jpg', 'title' => 'Экспертное оборудование', 'paragraphs' => ['Клиника оснащена аппаратами от мировых производителей: Samsung Medison, Philips, Hironic, Medtronic, Candela, Leisegang, Mediola.', 'Собственная операционная позволяет проводить хирургические манипуляции с использованием лазерных технологий — минимальная травматичность и быстрое восстановление.']],
        ['image' => 'images/about/doctor-consultation.jpg', 'title' => 'Индивидуальный подход', 'paragraphs' => ['У нас принимают кандидаты медицинских наук, врачи высшей и первой квалификационной категории — ведущие специалисты, заслужившие доверие пациентов.', 'Мы помогаем каждому стать лучшей версией себя, справиться с хроническими заболеваниями и предотвратить появление новых проблем со здоровьем.']],
    ];
    $values = $page->content['values'] ?? [
        ['icon' => 'fa-award', 'title' => 'Качество', 'text' => 'Современное оборудование и постоянное повышение квалификации специалистов.'],
        ['icon' => 'fa-handshake', 'title' => 'Этика', 'text' => 'Уважение достоинства, конфиденциальность и право пациента на информированное согласие.'],
        ['icon' => 'fa-lightbulb', 'title' => 'Инновации', 'text' => 'Передовые медицинские технологии и методики лечения на основе последних научных данных.'],
        ['icon' => 'fa-users', 'title' => 'Команда', 'text' => 'Единая команда профессионалов, объединённых общей целью — здоровье пациентов.'],
    ];
    $equipment = $page->content['equipment'] ?? [
        ['tag' => 'УЗ-диагностика', 'image' => 'images/medical_equipment/clearvue550_front.png', 'title' => 'Ультразвуковая система «Philips ClearVue 550» (США) высокого класса', 'text' => 'Позволяет выполнять широкий спектр ультразвуковых исследований: органы брюшной полости, малые и поверхностно расположенные органы, педиатрия, скелетно-мышечная система, урология, предстательная железа, акушерство и гинекология, сердце, сосуды и транскраниальная доплерография.'],
        ['tag' => 'Гинекология', 'image' => 'images/medical_equipment/videokalposkop-1.png', 'title' => 'Видеокольпоскоп «3MVC LED USB» (Германия) с 3-х ступенчатым увеличением', 'text' => 'Позволяет врачу вывести на экран в высоком разрешении имеющиеся патологии у пациента при осмотре.'],
        ['tag' => 'Лазерные технологии', 'image' => 'images/medical_equipment/frakczionnyj-lazer-1-1.png', 'title' => 'Фракционный лазер «CO2RE» (США) выделяющий волны двойного лазера', 'text' => 'Единственный CO2 лазер, имеющий 7 вариантов лечение, 5 из которых предназначены для обновления кожи.', 'link' => 'medical-device'],
        ['tag' => 'Аппаратные методики', 'image' => 'images/medical_equipment/mediola-compact-4-1.png', 'title' => 'Лазерный аппарат «Mediola Compact» для широкого спектра процедур', 'text' => 'Позволяет проводить хирургические манипуляции в разных направлениях медицинских услуг.'],
        ['tag' => 'Радиоволновая хирургия', 'image' => 'images/medical_equipment/generator-radiovolnovoj-40-mgcz-surgitron™-dual-emc-90-1-1.png', 'title' => 'Радиоволновой генератор «Surgitron® Dual EMC™ 90» для хирургических процедур', 'text' => 'Генератор позволяет врачу-хирургу виртуозно выполнять точные, атравматичные разрезы кожи и тканей при температуре от 38° до 80° С с абсолютной безопасностью для себя и пациента, обеспечивая великолепный результат.'],
        ['tag' => 'Экспертное УЗИ', 'image' => 'images/medical_equipment/uzi-affiniti-50-1-1.png', 'title' => 'Ультразвуковая система «Philips Affiniti 50» (США) экспертного класса', 'text' => 'Способен выводить на монитор высокодетализированные изображения, с которыми диагностика всегда будет максимально эффективной, точной и достоверной.'],
        ['tag' => 'Портативное сканирование', 'image' => 'images/medical_equipment/perenosnoj-skaner-vysokogo-klassa-vinno-6-3-1.png', 'title' => 'Ультразвуковой сканер «VINNO 6» высокого класса', 'text' => 'Благодаря этому аппарату специалист имеет возможность получить сведения о структуре, размерах и расположению внутренних органов пациента.'],
        ['tag' => 'Плазмотерапия', 'image' => 'images/medical_equipment/czentrifuga-1-1.png', 'title' => 'Центрифуга «Medifuge» (США) для проведения процедур с плазмой крови', 'text' => 'За 15 минут подготавливает аутологичную плазму для проведения процедур с ее применением.'],
    ];
@endphp

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">О клинике</span>
        </nav>
    </div>

    <section class="about-hero-section">
        <div class="about-hero-overlay"></div>
        <div class="container">
            <div class="about-hero-body">
                <span class="about-hero-tag">Клиника экспертной медицины</span>
                <h1 class="about-hero-title">Маяк Здоровья</h1>
                <div class="about-hero-divider"></div>
                <p class="about-hero-desc">
                    Персонализированная медицина в Минске. 19 направлений, собственная операционная,
                    лазерные технологии. ТОП-30 успешных проектов Беларуси по версии Forbes.
                </p>
                <ul class="about-hero-badges">
                    <li>С 2020 года</li>
                    <li>30+ специалистов</li>
                    <li>Лицензия МЗ РБ</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="clinic-story-section about-sea-section">
        <div class="container">
            <div class="section-header">
                <h2>О нашей клинике</h2>
                <p>История, подход и возможности</p>
            </div>

            <blockquote class="story-lead">
                Маяк Здоровья — клиника экспертной медицины, в которой объединены клинический опыт врачей-профессионалов,
                оборудование мировых лидеров и персонализированный подход к пациенту.
            </blockquote>

            <div class="story-blocks">
                @foreach ($story as $block)
                    <div class="story-block animate-on-scroll">
                        <div class="story-image">
                            <img src="{{ asset($block['image']) }}" alt="{{ $block['title'] }}" loading="lazy">
                        </div>
                        <div class="story-text">
                            <h3>{{ $block['title'] }}</h3>
                            @foreach ((array) ($block['paragraphs'] ?? []) as $p)
                                <p>{!! $p !!}</p>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="values-section about-sea-section">
        <div class="container">
            <div class="section-header">
                <h2>Наши ценности</h2>
                <p>Принципы, которыми мы руководствуемся в работе</p>
            </div>
            <div class="values-grid">
                @foreach ($values as $value)
                    <div class="category-card animate-on-scroll">
                        <div class="category-icon"><i class="fa-solid {{ $value['icon'] }}"></i></div>
                        <h3>{{ $value['title'] }}</h3>
                        <p>{{ $value['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="medical-directions-section directions-section about-sea-section">
        <div class="container">
            <div class="section-header">
                <h2>Медицинские направления</h2>
                <p>Специализации нашей клиники — консультации, диагностика и лечение</p>
            </div>
            <div class="directions-accordion" id="medical-directions-accordion"></div>
            <div class="directions-show-more-wrap">
                <button type="button" class="btn secondary-btn directions-show-more-btn" id="show-more-directions" aria-label="Показать ещё направления">
                    <i class="fas fa-chevron-down"></i> Показать ещё
                </button>
                <button type="button" class="btn secondary-btn directions-collapse-btn" id="collapse-directions" aria-label="Свернуть направления" style="display:none">
                    <i class="fas fa-chevron-up"></i> Свернуть
                </button>
            </div>
        </div>
    </section>

    <section id="licenses" class="licenses-section about-sea-section">
        <div class="container">
            <div class="section-header">
                <h2>Лицензии и сертификаты</h2>
                <p>Документы, подтверждающие право на оказание медицинских услуг</p>
            </div>
            <div class="licenses-container">
                @foreach ($licenses as $license)
                    <div class="license-card" data-license="license-{{ $license->id }}">
                        <div class="license-image">
                            <img src="{{ asset($license->image) }}" alt="{{ $license->caption }}">
                            <div class="license-overlay">
                                <button class="view-license-btn"><i class="fa-solid fa-magnifying-glass"></i> Увеличить</button>
                            </div>
                        </div>
                        <div class="license-title">{{ $license->caption }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="equipment-section about-sea-section" id="equipment">
        <div class="container">
            <div class="section-header">
                <h2>Оборудование</h2>
                <p>Оснащение, от которого зависят точный диагноз, комфорт врача и уверенная тактика лечения</p>
            </div>
            <div class="equipment-layout">
                <div class="equipment-grid">
                    @if (isset($equipmentItems) && $equipmentItems->count())
                        @foreach ($equipmentItems as $eq)
                            <a href="{{ route('equipment.show', $eq->slug) }}"
                               data-url="{{ route('equipment.show', $eq->slug) }}"
                               class="equipment-card equipment-card-link animate-on-scroll"
                               aria-label="Открыть страницу оборудования">
                                <span class="equipment-card-tag">{{ $eq->tag }}</span>
                                <div class="equipment-card-image">
                                    @if ($eq->cardImagePublicUrl())
                                        <img src="{{ $eq->cardImagePublicUrl() }}" alt="{{ $eq->name }}" loading="lazy">
                                    @endif
                                </div>
                                <div class="equipment-card-content">
                                    <h3>{{ $eq->name }}</h3>
                                    <p>{{ $eq->summary }}</p>
                                </div>
                                <div class="equipment-chevron" aria-hidden="true">
                                    <span class="equipment-chevron-icon"><i class="fa-solid fa-chevron-right"></i></span>
                                </div>
                            </a>
                        @endforeach
                    @else
                        @foreach ($equipment as $item)
                            @php $isLink = ! empty($item['link']); @endphp
                            @if ($isLink)
                                <a href="{{ route($item['link']) }}" data-url="{{ route($item['link']) }}" class="equipment-card equipment-card-link animate-on-scroll" aria-label="Открыть страницу оборудования">
                            @else
                                <article class="equipment-card animate-on-scroll">
                            @endif
                                <span class="equipment-card-tag">{{ $item['tag'] }}</span>
                                <div class="equipment-card-image">
                                    <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}" loading="lazy">
                                </div>
                                <div class="equipment-card-content">
                                    <h3>{{ $item['title'] }}</h3>
                                    <p>{{ $item['text'] }}</p>
                                </div>
                                <div class="equipment-chevron" aria-hidden="true">
                                    <span class="equipment-chevron-icon"><i class="fa-solid fa-chevron-right"></i></span>
                                </div>
                            @if ($isLink)</a>@else</article>@endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('scripts/script-about-clinic-page.js') }}?v=6"></script>
@endpush
