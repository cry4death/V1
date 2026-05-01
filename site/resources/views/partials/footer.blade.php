@php
    $phoneMain = $contacts['phone_main'] ?? '+375 (17) 215-02-89';
    $phoneMobile = $contacts['phone_mobile'] ?? '+375 (29) 652-93-27';
    $phoneExtra1 = $contacts['phone_extra_1'] ?? '+375 (29) 28-226-28';
    $phoneShort = $contacts['phone_short'] ?? '7289';
    $phoneShortNote = $contacts['phone_short_note'] ?? 'А1, МТС, Life';
    $extraPhones = $contacts['phones'] ?? [];
    $email = $contacts['email'] ?? 'info@lighthouse.by';
    $address = $contacts['address'] ?? 'г. Минск, ул. К. Туровского, 14';
    $postalAddress = $contacts['postal_address'] ?? '220114, Минск, ул. Филимонова, 25Г-303';
    $weekdays = $schedule['weekdays'] ?? '08:00 – 20:30';
    $saturday = $schedule['saturday'] ?? '08:00 – 18:00';
    $sunday = $schedule['sunday'] ?? '09:00 – 16:00';
    $companyName = $legal['company_name'] ?? 'ООО «ЗАРГА Медика»';
    $unp = $legal['unp'] ?? 'УНП 192035339';
    $licenseNumber = $legal['license_number'] ?? 'Лицензия № М-8399';
    $phoneMainTel = preg_replace('/[^\d+]/', '', $phoneMain);
    $phoneMobileTel = preg_replace('/[^\d+]/', '', $phoneMobile);
    $phoneShortTel = preg_replace('/[^\d+]/', '', $phoneShort);
    $phoneExtra1Tel = preg_replace('/[^\d+]/', '', $phoneExtra1);
    $instagram = $social['instagram'] ?? '';
    $telegram = $social['telegram'] ?? '';
    $facebook = $social['facebook'] ?? '';
    $vk = $social['vk'] ?? '';
    $youtube = $social['youtube'] ?? '';
@endphp
<footer>
    <div class="container">
        <div class="footer-container">
            <div class="footer-col">
                <div class="footer-logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/site-logo-footer.svg') }}" alt="Логотип медицинской клиники" />
                    </a>
                </div>
                <p class="footer-description">
                    Многопрофильная клиника в Минске. Приём взрослых и детей,
                    лабораторная и инструментальная диагностика, амбулаторная хирургия.
                </p>
                <div class="footer-legal">
                    <span>{{ $companyName }}</span>
                    <span>{{ $unp }}</span>
                    <span>{{ $licenseNumber }}</span>
                </div>
                <div class="social-links">
                    @if ($instagram)
                        <a href="{{ $instagram }}" class="social-link" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                    @endif
                    @if ($facebook)
                        <a href="{{ $facebook }}" class="social-link" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                    @endif
                    @if ($vk)
                        <a href="{{ $vk }}" class="social-link" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-vk"></i>
                        </a>
                    @endif
                    @if ($youtube)
                        <a href="{{ $youtube }}" class="social-link" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                    @endif
                    @if ($telegram)
                        <a href="{{ $telegram }}" class="social-link" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-telegram"></i>
                        </a>
                    @endif
                </div>
            </div>
            <div class="footer-col">
                <h4>Информация</h4>
                <ul class="footer-menu">
                    <li><a href="{{ route('services.index') }}"><i class="fa-solid fa-chevron-right"></i> Услуги</a></li>
                    <li><a href="{{ route('doctors.index') }}"><i class="fa-solid fa-chevron-right"></i> Врачи</a></li>
                    <li><a href="{{ route('about') }}"><i class="fa-solid fa-chevron-right"></i> О клинике</a></li>
                    <li><a href="{{ route('documents') }}"><i class="fa-solid fa-chevron-right"></i> Лицензии</a></li>
                    <li><a href="{{ route('contacts') }}"><i class="fa-solid fa-chevron-right"></i> Контакты</a></li>
                </ul>
            </div>
            <div class="footer-col footer-col--contacts">
                <h4>Контакты</h4>
                <div class="footer-contacts-grid">
                    <ul class="footer-contacts">
                        <li>
                            <span class="contact-label"><i class="fa-solid fa-location-dot"></i> Адрес:</span>
                            <span class="contact-value">{{ $address }}</span>
                        </li>
                        <li>
                            <span class="contact-label"><i class="fa-solid fa-envelope-open"></i> Почтовый адрес:</span>
                            <span class="contact-value">{{ $postalAddress }}</span>
                        </li>
                        <li class="footer-contact-phones">
                            <span class="contact-label"><i class="fa-solid fa-phone"></i> Телефоны:</span>
                            <ul class="footer-phones">
                                <li class="footer-phones-main">
                                    <a href="tel:{{ $phoneShortTel }}">{{ $phoneShort }}</a>
                                    <span class="phone-note">{{ $phoneShortNote }}</span>
                                </li>
                                <li><a href="tel:{{ $phoneMainTel }}">{{ $phoneMain }}</a></li>
                                @if ($phoneExtra1)
                                    <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $phoneExtra1) }}">{{ $phoneExtra1 }}</a></li>
                                @endif
                                @foreach ($extraPhones as $extraPhone)
                                    <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $extraPhone) }}">{{ $extraPhone }}</a></li>
                                @endforeach
                                <li><a href="tel:{{ $phoneMobileTel }}">{{ $phoneMobile }}</a></li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="footer-contacts">
                        <li>
                            <span class="contact-label"><i class="fa-solid fa-envelope"></i> Email:</span>
                            <span class="contact-value">
                                <a href="mailto:{{ $email }}">{{ $email }}</a>
                            </span>
                        </li>
                        <li class="footer-contact-hours">
                            <span class="contact-label"><i class="fa-solid fa-clock"></i> Часы работы:</span>
                            <span class="contact-value">ПН-ПТ: {{ $weekdays }}<br>СБ: {{ $saturday }}<br>ВС: {{ $sunday }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} {{ $companyName }}. Все права защищены.</p>
            <div class="footer-bottom-links">
                <a href="#">Положение о скидках</a>
                <a href="#">Политика обработки персональных данных</a>
                <a href="#">Политика обработки файлов cookies</a>
                <a href="#">Выбор настроек cookie</a>
            </div>
        </div>
    </div>
</footer>
