@php
    $phoneMain = $contacts['phone_main'] ?? '+375 (17) 215-02-89';
    $phoneMobile = $contacts['phone_mobile'] ?? '+375 (29) 652-93-27';
    $phoneShort = $contacts['phone_short'] ?? '7289';
    $phoneShortNote = $contacts['phone_short_note'] ?? 'А1, МТС, Life';
    $email = $contacts['email'] ?? 'info@lighthouse.by';
    $address = $contacts['address'] ?? 'г. Минск, ул. К. Туровского, 14';
    $weekdays = $schedule['weekdays'] ?? '08:00 – 20:30';
    $saturday = $schedule['saturday'] ?? '08:00 – 18:00';
    $sunday = $schedule['sunday'] ?? '09:00 – 16:00';
    $phoneMainTel = preg_replace('/[^\d+]/', '', $phoneMain);
    $phoneMobileTel = preg_replace('/[^\d+]/', '', $phoneMobile);
    $phoneShortTel = preg_replace('/[^\d+]/', '', $phoneShort);
@endphp
<header class="site-header">
    <div class="header-topbar" aria-label="Контактная информация клиники">
        <div class="container header-topbar__inner">
            <div class="header-topbar__item">
                <div class="header-topbar__copy">
                    <span class="header-topbar__title">Адрес: </span>
                    <span class="header-topbar__text">{{ $address }}</span>
                </div>
            </div>
            <div class="header-topbar__item">
                <div class="header-topbar__copy">
                    <span class="header-topbar__title">Время работы</span>
                    <span class="header-topbar__text">ПН-ПТ: {{ $weekdays }}; СБ: {{ $saturday }}; ВС: {{ $sunday }}</span>
                </div>
            </div>
            <div class="header-topbar__item header-topbar__item--phones">
                <div class="header-topbar__copy">
                    <span class="header-topbar__title">Контакты</span>
                    <div class="header-topbar__phones">
                        <a href="tel:{{ $phoneMainTel }}" class="header-topbar__phone">{{ $phoneMain }}</a>
                        <a href="tel:{{ $phoneMobileTel }}" class="header-topbar__phone">{{ $phoneMobile }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header-main">
        <div class="container header-container">
            <div class="logo">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/site-logo (8).svg') }}" alt="Логотип медицинской клиники" />
                </a>
            </div>
            <div class="nav-container">
                <nav aria-label="Основная навигация">
                    <ul class="nav-menu">
                        @php
                            $isServicesActive = request()->routeIs('services.*');
                            $isAboutActive = request()->routeIs('about', 'documents', 'vacancies', 'insurance');
                            $isDoctorsActive = request()->routeIs('doctors.*');
                            $isPromotionsActive = request()->routeIs('promotions.*');
                            $isBlogActive = request()->routeIs('blog.*');
                            $isContactsActive = request()->routeIs('contacts');
                            $currentRouteName = request()->route() ? request()->route()->getName() : '';
                        @endphp
                        <li class="nav-item nav-item--has-submenu nav-item--services @if ($isServicesActive) nav-item--active @endif">
                            <div class="nav-link-row">
                                <a href="{{ route('services.index') }}">Услуги</a>
                                <button type="button" class="nav-submenu-toggle" aria-expanded="false" aria-label="Открыть подменю Услуги">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                            @if (! empty($navDirections) && $navDirections->isNotEmpty())
                                <div class="nav-submenu nav-submenu--mega" aria-label="Подменю услуг">
                                    @foreach ($navDirections as $dir)
                                        @php
                                            // Only highlight when on a single service page whose direction matches this item
                                            $dirActive = $currentRouteName === 'services.show'
                                                && isset($service)
                                                && optional($service->direction)->slug === $dir->slug;
                                        @endphp
                                        <a href="{{ route('services.index') }}#{{ $dir->slug }}" class="nav-submenu-link @if ($dirActive) nav-submenu-link--active @endif">{{ $dir->name }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </li>
                        <li class="nav-item nav-item--has-submenu @if ($isAboutActive) nav-item--active @endif">
                            <div class="nav-link-row">
                                <a href="{{ route('about') }}">О нас</a>
                                <button type="button" class="nav-submenu-toggle" aria-expanded="false" aria-label="Открыть подменю О нас">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="nav-submenu" aria-label="Подменю о клинике">
                                <a href="{{ route('documents') }}" class="nav-submenu-link @if ($currentRouteName === 'documents') nav-submenu-link--active @endif">Документы</a>
                                <a href="{{ route('vacancies') }}" class="nav-submenu-link @if ($currentRouteName === 'vacancies') nav-submenu-link--active @endif">Вакансии</a>
                                <a href="{{ route('insurance') }}" class="nav-submenu-link @if ($currentRouteName === 'insurance') nav-submenu-link--active @endif">Страховым клиентам</a>
                            </div>
                        </li>
                        <li class="@if ($isDoctorsActive) nav-item--active @endif"><a href="{{ route('doctors.index') }}">Врачи</a></li>
                        <li class="@if ($isPromotionsActive) nav-item--active @endif"><a href="{{ route('promotions.index') }}">Акции</a></li>
                        <li class="@if ($isBlogActive) nav-item--active @endif"><a href="{{ route('blog.index') }}">Блог</a></li>
                        <li class="@if ($isContactsActive) nav-item--active @endif"><a href="{{ route('contacts') }}">Контакты</a></li>
                    </ul>
                </nav>
                <div class="nav-mobile-extras nav-mobile-patient" aria-label="Пациенту">
                    <div class="nav-mobile-info__item">
                        <span class="nav-mobile-info__title">Пациенту</span>
                        @auth('patient')
                            <span class="nav-mobile-info__text">{{ Auth::guard('patient')->user()->first_name }}</span>
                            <a href="{{ route('booking.index') }}" class="nav-mobile-info__link">Записаться на приём</a>
                            <form action="{{ route('patient.logout') }}" method="POST" class="nav-mobile-patient-logout" style="margin-top: 0.5rem;">
                                @csrf
                                <button type="submit" class="nav-mobile-info__link" style="background: none; border: none; padding: 0; cursor: pointer; font: inherit; color: #0ea5e9; text-decoration: underline;">Выход</button>
                            </form>
                        @else
                            <a href="{{ route('patient.login') }}" class="nav-mobile-info__link">Вход</a>
                            <a href="{{ route('patient.register.profile') }}" class="nav-mobile-info__link">Регистрация</a>
                        @endauth
                    </div>
                </div>
                <div class="nav-mobile-extras nav-mobile-info" aria-label="Контактная информация">
                    <div class="nav-mobile-info__item">
                        <span class="nav-mobile-info__title">Адрес</span>
                        <span class="nav-mobile-info__text">{{ $address }}</span>
                    </div>
                    <div class="nav-mobile-info__item">
                        <span class="nav-mobile-info__title">Время работы</span>
                        <span class="nav-mobile-info__text">ПН-ПТ: {{ $weekdays }}<br> СБ: {{ $saturday }}<br> ВС: {{ $sunday }}</span>
                    </div>
                    <div class="nav-mobile-info__item">
                        <span class="nav-mobile-info__title">Контакты</span>
                        <a href="tel:{{ $phoneMainTel }}" class="nav-mobile-info__link">{{ $phoneMain }}</a>
                        <a href="tel:{{ $phoneMobileTel }}" class="nav-mobile-info__link">{{ $phoneMobile }}</a>
                        <a href="mailto:{{ $email }}" class="nav-mobile-info__link">{{ $email }}</a>
                    </div>
                </div>
            </div>
            <div class="header-mobile-actions">
                <a href="{{ route('search') }}" class="header-mobile-icon-btn" aria-label="Поиск">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </a>
                <a href="tel:{{ $phoneShortTel }}" class="header-mobile-icon-btn header-mobile-icon-btn--phone" aria-label="Позвонить">
                    <i class="fa-solid fa-phone"></i>
                    <span class="header-mobile-phone-number">{{ $phoneShort }}</span>
                </a>
            </div>
            <div class="header-contact">
                <div class="header-patient-auth" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    @auth('patient')
                        <span class="header-patient-name" style="font-size: 0.9rem; color: #334155;">{{ Auth::guard('patient')->user()->first_name }}</span>
                        <a href="{{ route('booking.index') }}" class="btn primary-btn" style="font-size: 0.85rem; padding: 0.4rem 0.75rem;">
                            <i class="fa-regular fa-calendar-check"></i> Запись
                        </a>
                        <form action="{{ route('patient.logout') }}" method="POST" style="display: inline; margin: 0;">
                            @csrf
                            <button type="submit" class="btn" style="font-size: 0.85rem; padding: 0.4rem 0.75rem; background: #f1f5f9; border: 1px solid #e2e8f0;">Выход</button>
                        </form>
                    @else
                        <a href="{{ route('patient.login') }}" style="font-size: 0.9rem; color: #0f172a;">Вход</a>
                        <a href="{{ route('patient.register.profile') }}" style="font-size: 0.9rem; color: #0ea5e9;">Регистрация</a>
                    @endauth
                </div>
                <form action="{{ route('search') }}" method="GET" class="search-box">
                    <input type="text" name="q" placeholder="Поиск..." class="search-input" />
                    <button type="submit" class="search-btn">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
                <a href="tel:{{ $phoneShortTel }}" class="header-phone-link">
                    <i class="fa-solid fa-phone"></i> {{ $phoneShort }} ({{ $phoneShortNote }})
                </a>
                <div class="header-buttons">
                    <a href="{{ route('booking.index') }}" class="btn appointment-btn">
                        <i class="fa-regular fa-calendar-check"></i> Записаться
                    </a>
                </div>
            </div>
            <button type="button" class="mobile-menu-btn" aria-label="Открыть меню" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>
