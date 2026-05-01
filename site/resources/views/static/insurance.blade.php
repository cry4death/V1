@extends('layouts.app')

@section('title', 'Страховым клиентам — Маяк Здоровья')
@section('meta_description', 'Страховым клиентам медицинского центра «Маяк Здоровья»: перечень страховых партнёров, необходимые документы и способы записи по полису ДМС.')
@section('body_class', 'insurance-page-view')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-insurance-page.css') }}">
@endpush

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Страховым клиентам</span>
        </nav>
    </div>

    <main class="info-page insurance-page">
        <div class="insurance-sea-decor" aria-hidden="true">
            <i class="insurance-sea insurance-sea--anchor-sm fa-solid fa-anchor"></i>
            <i class="insurance-sea insurance-sea--ship fa-solid fa-ship"></i>
            <i class="insurance-sea insurance-sea--fish fa-solid fa-fish"></i>
            <i class="insurance-sea insurance-sea--wave fa-solid fa-water"></i>
            <i class="insurance-sea insurance-sea--compass fa-solid fa-compass"></i>
            <i class="insurance-sea insurance-sea--binoculars fa-solid fa-binoculars"></i>
            <i class="insurance-sea insurance-sea--lifebuoy fa-solid fa-life-ring"></i>
            <i class="insurance-sea insurance-sea--anchor-mid fa-solid fa-anchor"></i>
            <i class="insurance-sea insurance-sea--lighthouse fa-solid fa-tower-observation"></i>
            <i class="insurance-sea insurance-sea--wave-mid fa-solid fa-water"></i>
            <i class="insurance-sea insurance-sea--ship-low fa-solid fa-ship"></i>
            <i class="insurance-sea insurance-sea--compass-low fa-solid fa-compass"></i>
        </div>

        <section class="insurance-intro-section">
            <div class="insurance-intro-sea" aria-hidden="true">
                <i class="fa-solid fa-anchor ins-intro-sea--anchor-lg"></i>
                <i class="fa-solid fa-tower-observation ins-intro-sea--lighthouse"></i>
                <i class="fa-solid fa-ship ins-intro-sea--ship"></i>
                <i class="fa-solid fa-anchor ins-intro-sea--anchor-sm"></i>
                <i class="fa-solid fa-water ins-intro-sea--wave"></i>
                <i class="fa-solid fa-compass ins-intro-sea--compass"></i>
                <i class="fa-solid fa-fish ins-intro-sea--fish"></i>
            </div>
            <div class="container">
                <div class="section-header insurance-page-header">
                    <h1>Страховым клиентам</h1>
                    <p>Медицинский центр «Маяк Здоровья» принимает по полисам добровольного медицинского страхования! Мы работаем с ведущими страховыми компаниями Беларуси.</p>
                </div>
                <div class="insurance-partners-wrap">
                    <div class="insurance-partners-card animate-on-scroll">
                        <img src="{{ asset('images/insurance_companies/companies.png') }}"
                             alt="Страховые компании-партнёры: Белгосстрах, Ингосстрах, Промтрансинвест, Белнефтестрах, Евроинс, БелВЭБ Страхование, ТАСК, Белэксимгарант, Asoba, Imkliva, Купала"
                             class="insurance-partners-img" loading="lazy">
                    </div>
                </div>
            </div>
        </section>

        <section class="insurance-docs-section">
            <div class="container">
                <div class="section-header">
                    <h2>Что необходимо предъявить?</h2>
                    <p>При каждом посещении медицинского центра «Маяк Здоровья» застрахованное лицо обязано предъявить:</p>
                </div>
                <div class="insurance-docs-grid">
                    <article class="insurance-doc-card animate-on-scroll">
                        <div class="insurance-doc-card__icon" aria-hidden="true"><i class="fa-solid fa-id-card"></i></div>
                        <div class="insurance-doc-card__body">
                            <h3>Гражданам Республики Беларусь</h3>
                            <ul class="insurance-doc-list">
                                <li><span class="insurance-doc-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span>Документ, удостоверяющий личность — паспорт, вид на жительство или удостоверение беженца</span></li>
                                <li><span class="insurance-doc-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span>Карточка страховой компании</span></li>
                            </ul>
                        </div>
                    </article>

                    <article class="insurance-doc-card animate-on-scroll">
                        <div class="insurance-doc-card__icon" aria-hidden="true"><i class="fa-solid fa-passport"></i></div>
                        <div class="insurance-doc-card__body">
                            <h3>Иностранным гражданам</h3>
                            <ul class="insurance-doc-list">
                                <li><span class="insurance-doc-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span>Действительный паспорт или иной документ, его заменяющий, предназначенный для выезда за границу</span></li>
                            </ul>
                        </div>
                    </article>

                    <article class="insurance-doc-card animate-on-scroll">
                        <div class="insurance-doc-card__icon" aria-hidden="true"><i class="fa-solid fa-child"></i></div>
                        <div class="insurance-doc-card__body">
                            <h3>Несовершеннолетним</h3>
                            <ul class="insurance-doc-list">
                                <li><span class="insurance-doc-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span>При обращении несовершеннолетнего лица законным представителям необходимо предъявить паспорт ребёнка или свидетельство о рождении</span></li>
                            </ul>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="insurance-register-section">
            <div class="container">
                <div class="section-header">
                    <h2>Как записаться?</h2>
                    <p>Выберите удобный для вас способ записи</p>
                </div>

                <div class="insurance-register-grid">
                    <article class="insurance-register-card insurance-register-card--via animate-on-scroll">
                        <div class="insurance-register-card__head">
                            <div class="insurance-register-card__icon" aria-hidden="true"><i class="fa-solid fa-life-ring"></i></div>
                            <div class="insurance-register-card__title">
                                <span class="insurance-register-card__eyebrow">Маршрут записи</span>
                                <h3>Через страховую компанию</h3>
                            </div>
                        </div>
                        <p>Свяжитесь с представителем страховой компании и сообщите о необходимости получения услуг в МЦ «Маяк Здоровья». Сотрудник страховой свяжется с нами и подберёт для Вас подходящее время приёма.</p>
                        <div class="insurance-register-flow" aria-label="Порядок записи через страховую компанию">
                            <div class="insurance-register-flow__label">Как это происходит</div>
                            <ol class="insurance-register-flow__steps">
                                <li><div><strong>1. Обратитесь в страховую</strong><span>Сообщите о планируемом визите.</span></div></li>
                                <li><div><strong>2. Мы согласуем приём</strong><span>Страховая связывается с центром.</span></div></li>
                                <li><div><strong>3. Получите подтверждение</strong><span>Вам сообщат дату и время.</span></div></li>
                            </ol>
                            <div class="insurance-register-flow__footer">
                                <i class="fa-solid fa-water"></i>
                                <span>Страховая компания координирует весь маршрут записи.</span>
                            </div>
                        </div>
                    </article>

                    <article class="insurance-register-card insurance-register-card--contacts animate-on-scroll">
                        <div class="insurance-register-card__head">
                            <div class="insurance-register-card__icon" aria-hidden="true"><i class="fa-solid fa-phone-volume"></i></div>
                            <h3>Самостоятельная запись</h3>
                        </div>
                        <p>Записаться на приём по договору медицинского страхования можно самостоятельно:</p>
                        <ul class="insurance-phones-list">
                            <li>
                                <a href="tel:+375445549783" class="insurance-phone-link">
                                    <i class="fa-solid fa-phone"></i>
                                    <span>7289</span>
                                    <span class="insurance-phone-operator">(А1, МТС, Life)</span>
                                </a>
                            </li>
                            <li>
                                <a href="tel:+375172150289" class="insurance-phone-link">
                                    <i class="fa-solid fa-phone"></i>
                                    <span>+375 (17) 215 02 89</span>
                                </a>
                            </li>
                            <li>
                                <a href="tel:+375292822628" class="insurance-phone-link">
                                    <i class="fa-solid fa-phone"></i>
                                    <span>+375 (29) 28 226 28</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('contacts') }}" class="insurance-online-link">
                                    <i class="fa-regular fa-calendar-check"></i>
                                    <span>Онлайн-запись</span>
                                    <i class="fa-solid fa-arrow-right insurance-online-arrow"></i>
                                </a>
                            </li>
                        </ul>
                    </article>
                </div>

                <div class="insurance-register-note animate-on-scroll">
                    <div class="insurance-register-note__icon" aria-hidden="true"><i class="fa-solid fa-circle-info"></i></div>
                    <div class="insurance-register-note__body">
                        <strong>Важно!</strong> После самостоятельной записи любым из вышеперечисленных способов необходимо <strong>ОБЯЗАТЕЛЬНО</strong> связаться со своим страховым агентом, для того чтобы страховая выслала в центр гарантийное письмо на оплату медицинской услуги.
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
