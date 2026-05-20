@php
    $navActive = $navActive ?? '';
@endphp
<nav class="cabinet-nav" aria-label="Разделы кабинета">
    <p class="cabinet-nav__title">Личный кабинет</p>
    <ul>
        <li>
            <a href="{{ route('cabinet.dashboard') }}" class="{{ in_array($navActive, ['dashboard', 'appointments']) ? 'is-active' : '' }}">
                <i class="fa-solid fa-calendar-check" style="width:1rem; text-align:center;"></i> Мои записи
            </a>
        </li>
        <li>
            <a href="{{ route('cabinet.profile.edit') }}" class="{{ $navActive === 'profile' ? 'is-active' : '' }}">
                <i class="fa-solid fa-user-pen" style="width:1rem; text-align:center;"></i> Профиль
            </a>
        </li>
        <li>
            <a href="{{ route('booking.start') }}" class="{{ $navActive === 'booking' ? 'is-active' : '' }}">
                <i class="fa-solid fa-circle-plus" style="width:1rem; text-align:center;"></i> Новая запись
            </a>
        </li>
    </ul>

    <form action="{{ route('patient.logout') }}" method="POST" class="cabinet-nav__logout">
        @csrf
        <button type="submit" class="cabinet-nav__logout-btn">Выйти</button>
    </form>
</nav>
