document.addEventListener('DOMContentLoaded', function () {
    initMedicalDirectionsAccordion();
    initLicenseModals();
    truncateEquipmentCardDescriptions();
    initEquipmentCardLinks();
});

const MEDICAL_DIRECTIONS_DATA = [
    { name: 'Эндокринология', text: 'Диагностика и лечение заболеваний щитовидной железы, надпочечников, поджелудочной железы, нарушений обмена веществ и гормонального фона. Подбор терапии при сахарном диабете, ожирении, остеопорозе.', items: ['УЗИ щитовидной железы', 'Анализы на гормоны', 'Подбор заместительной терапии'] },
    { name: 'Гинекология', text: 'Полный спектр услуг для женского здоровья: профилактические осмотры, диагностика и лечение воспалительных заболеваний, ведение беременности, подбор контрацепции, лечение нарушений цикла и бесплодия.', items: ['Кольпоскопия', 'УЗИ органов малого таза', 'Цитология и биопсия'] },
    { name: 'Лазерная гинекология', text: 'Современные лазерные методики в гинекологии — коррекция изменений шейки матки, лечение дисплазии, лазерная вапоризация и реконструктивные процедуры. Минимальная травматичность и короткий восстановительный период.', items: ['Лазерная вапоризация', 'Фотодинамическая терапия', 'Коррекция послеродовых изменений'] },
    { name: 'Проктология', text: 'Диагностика и лечение заболеваний прямой кишки и анального канала: геморрой, анальные трещины, парапроктит, новообразования. Применяем консервативные и малоинвазивные методы.', items: ['Колоноскопия', 'Ректороманоскопия', 'Склеротерапия, лигирование'] },
    { name: 'Хирургия', text: 'Плановая и экстренная хирургическая помощь. Операции на органах брюшной полости, мягких тканях, вскрытие гнойников. Используем современные шовные материалы и протоколы ведения после операций.', items: ['Грыжесечение', 'Удаление новообразований кожи', 'Экстренная хирургия'] },
    { name: 'Лазерная хирургия', text: 'Операции с применением лазерных технологий: малая кровопотеря, точность разреза, быстрое заживление. Удаление доброкачественных образований, лечение вросшего ногтя, сосудистой патологии.', items: ['Лазерное удаление образований', 'Лазерная коррекция вросшего ногтя', 'Варикоз и сосудистые звёздочки'] },
    { name: 'УЗИ', text: 'Ультразвуковая диагностика на аппаратах экспертного класса. УЗИ органов брюшной полости, почек, малого таза, щитовидной железы, сосудов, сердца, суставов. Исследования проводят врачи с большим опытом.', items: ['УЗИ брюшной полости и почек', 'УЗИ сердца (ЭхоКГ)', 'Дуплексное сканирование сосудов'] },
    { name: 'Функциональная диагностика', text: 'Оценка работы сердца, сосудов, дыхательной и нервной системы. ЭКГ, суточное мониторирование АД и ЭКГ, спирометрия, ЭЭГ. Помогаем в постановке диагноза и контроле лечения.', items: ['ЭКГ и Холтер', 'Спирометрия', 'ЭЭГ, велоэргометрия'] },
    { name: 'Терапия', text: 'Первичный приём терапевта, комплексное обследование при недомогании, ведение пациентов с хроническими заболеваниями (гипертония, ИБС, болезни лёгких, ЖКТ). Направление к узким специалистам при необходимости.', items: ['Диагностика и лечение ОРВИ', 'Ведение хронических заболеваний', 'Профосмотры и справки'] },
    { name: 'Флебология', text: 'Диагностика и лечение заболеваний вен: варикоз, тромбофлебит, трофические язвы. УЗИ вен, консервативное лечение, склеротерапия, рекомендации по компрессии и образу жизни.', items: ['УЗИ вен нижних конечностей', 'Склеротерапия', 'Консервативное лечение варикоза'] },
    { name: 'Кардиология', text: 'Профилактика, диагностика и лечение болезней сердца и сосудов. ЭКГ, ЭхоКГ, суточный мониторинг. Подбор терапии при гипертонии, ишемической болезни, аритмиях, сердечной недостаточности.', items: ['ЭКГ, ЭхоКГ', 'Холтер-мониторирование', 'Подбор гипотензивной терапии'] },
    { name: 'Гастроэнтерология', text: 'Заболевания желудка, кишечника, печени, поджелудочной железы. Диагностика по анализам и УЗИ, при необходимости — направление на ФГДС и колоноскопию. Лечение гастрита, ГЭРБ, СРК, заболеваний печени.', items: ['Диагностика ЖКТ', 'Лечение гастрита и ГЭРБ', 'Ведение пациентов с патологией печени'] },
    { name: 'Неврология', text: 'Головные боли, головокружение, боли в спине и шее, онемение конечностей, последствия инсультов. Осмотр невролога, при необходимости — ЭЭГ, УЗДГ сосудов, МРТ по направлению.', items: ['Лечение остеохондроза и болей', 'Вестибулярная реабилитация', 'ЭЭГ, УЗДГ'] },
    { name: 'Урология', text: 'Мужское и женское здоровье: заболевания почек, мочевого пузыря, мочеиспускательного канала, предстательной железы. УЗИ почек и мочевого пузыря, анализы, подбор терапии и направление на операции при необходимости.', items: ['УЗИ почек и мочевого пузыря', 'Лечение цистита, простатита', 'Мочекаменная болезнь'] },
    { name: 'Нефрология', text: 'Специализированная помощь при заболеваниях почек: гломерулонефрит, пиелонефрит, хроническая болезнь почек, подбор терапии при почечной недостаточности. Тесное взаимодействие с урологами и терапевтами.', items: ['Диагностика функции почек', 'Ведение ХБП', 'Подготовка к заместительной терапии'] },
    { name: 'Онкология', text: 'Ранняя диагностика и сопровождение онкологических пациентов. Осмотр онколога, УЗИ, анализы на онкомаркеры, биопсия. Направление в специализированные центры при необходимости хирургии и химиотерапии.', items: ['Скрининг и осмотр', 'Онкомаркеры, биопсия', 'Наблюдение после лечения'] },
    { name: 'Маммология', text: 'Профилактика и диагностика заболеваний молочных желёз. Осмотр маммолога, УЗИ молочных желёз, при необходимости — пункция и направление на маммографию. Ведение при мастопатии и после операций.', items: ['УЗИ молочных желёз', 'Пункция образований', 'Профилактические осмотры'] },
    { name: 'Диетология', text: 'Индивидуальный подбор питания при избыточном весе, диабете, заболеваниях ЖКТ, сердца и почек. Составление рациона, коррекция привычек, сопровождение для устойчивого результата.', items: ['Подбор рациона при заболеваниях', 'Коррекция веса', 'Нутритивная поддержка'] },
    { name: 'Психология', text: 'Консультации психолога: тревога, стресс, кризисные ситуации, проблемы в отношениях и на работе. Диагностика, поддержка, техники саморегуляции. Работа со взрослыми и подростками.', items: ['Личные консультации', 'Семейная психология', 'Стресс и адаптация'] },
    { name: 'Психотерапия', text: 'Психотерапевтическая помощь при депрессии, тревожных и панических расстройствах, нарушениях сна, ПТСР. Интегративный подход, при необходимости — совместное ведение с психиатром и назначение терапии.', items: ['Когнитивно-поведенческая терапия', 'Работа с тревогой и депрессией', 'Поддержка при кризисе'] }
];

const DIRECTIONS_VISIBLE_STEP = 5;

function initMedicalDirectionsAccordion() {
    var container = document.getElementById('medical-directions-accordion');
    var showMoreBtn = document.getElementById('show-more-directions');
    var collapseBtn = document.getElementById('collapse-directions');
    if (!container || !showMoreBtn) return;

    var visibleCount = 0;

    function buildContentHtml(data) {
        var html = '<div class="accordion-content-inner"><p>' + escapeHtml(data.text) + '</p>';
        if (data.items && data.items.length) {
            html += '<ul>';
            data.items.forEach(function (point) {
                html += '<li>' + escapeHtml(point) + '</li>';
            });
            html += '</ul>';
        }
        html += '<a href="medical-services-page.html" class="btn secondary-btn btn-sm">Подробнее об услугах</a></div>';
        return html;
    }

    function createAccordionItem(data, index) {
        var item = document.createElement('div');
        item.className = 'accordion-item medical-direction-item';
        item.setAttribute('data-direction-index', index);
        item.innerHTML =
            '<button type="button" class="accordion-header" aria-expanded="false" aria-controls="direction-content-' + index + '" id="direction-header-' + index + '">' +
            '<h3>' + escapeHtml(data.name) + '</h3>' +
            '<span class="accordion-icon" aria-hidden="true"><i class="fas fa-plus"></i></span>' +
            '</button>' +
            '<div class="accordion-content" id="direction-content-' + index + '" role="region" aria-labelledby="direction-header-' + index + '">' +
            buildContentHtml(data) +
            '</div>';
        return item;
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function updateButtonsVisibility() {
        var allVisible = visibleCount >= MEDICAL_DIRECTIONS_DATA.length;
        showMoreBtn.style.display = allVisible ? 'none' : '';
        if (collapseBtn) {
            collapseBtn.style.display = allVisible ? '' : 'none';
        }
    }

    function renderNext() {
        var from = visibleCount;
        var to = Math.min(from + DIRECTIONS_VISIBLE_STEP, MEDICAL_DIRECTIONS_DATA.length);
        for (var i = from; i < to; i++) {
            var el = createAccordionItem(MEDICAL_DIRECTIONS_DATA[i], i);
            container.appendChild(el);
            bindAccordionHeader(el);
        }
        visibleCount = to;
        updateButtonsVisibility();
    }

    function collapseToDefault() {
        var items = container.querySelectorAll('.medical-direction-item');
        items.forEach(function (item) {
            var index = parseInt(item.getAttribute('data-direction-index'), 10);
            if (index >= DIRECTIONS_VISIBLE_STEP) {
                item.remove();
            } else {
                item.classList.remove('active');
                var content = item.querySelector('.accordion-content');
                if (content) content.style.maxHeight = '';
                var header = item.querySelector('.accordion-header');
                if (header) header.setAttribute('aria-expanded', 'false');
            }
        });
        visibleCount = DIRECTIONS_VISIBLE_STEP;
        updateButtonsVisibility();
    }

    function bindAccordionHeader(itemEl) {
        var header = itemEl.querySelector('.accordion-header');
        var content = itemEl.querySelector('.accordion-content');
        if (!header || !content) return;

        header.addEventListener('click', function () {
            var isOpen = itemEl.classList.contains('active');
            if (isOpen) {
                content.style.maxHeight = content.scrollHeight + 'px';
                requestAnimationFrame(function () {
                    content.style.maxHeight = '0px';
                });
                itemEl.classList.remove('active');
                header.setAttribute('aria-expanded', 'false');
            } else {
                itemEl.classList.add('active');
                content.style.maxHeight = content.scrollHeight + 'px';
                header.setAttribute('aria-expanded', 'true');
                content.addEventListener('transitionend', function handler() {
                    if (itemEl.classList.contains('active')) {
                        content.style.maxHeight = 'none';
                    }
                    content.removeEventListener('transitionend', handler);
                });
            }
        });
    }

    showMoreBtn.addEventListener('click', function () {
        renderNext();
    });

    if (collapseBtn) {
        collapseBtn.addEventListener('click', function () {
            collapseToDefault();
        });
    }

    renderNext();
}

function initLicenseModals() {
    var modal = document.getElementById('licenseModal');
    var modalImg = document.getElementById('modalLicenseImage');
    if (!modal || !modalImg) return;

    // Prevent double-init when script.js already bound the open/close handlers
    var alreadyInit = modal.dataset.licenseInitialized === '1';

    var closeBtn = modal.querySelector('.license-modal-close');
    var zoomBtn = modal.querySelector('.license-zoom-btn');
    var viewButtons = document.querySelectorAll('.view-license-btn');

    function openModal(imgSrc, imgAlt) {
        modalImg.src = imgSrc;
        modalImg.alt = imgAlt;
        modalImg.classList.remove('zoomed');
        if (zoomBtn) {
            zoomBtn.innerHTML = '<i class="fa-solid fa-magnifying-glass-plus"></i>';
            zoomBtn.setAttribute('aria-label', 'Увеличить изображение');
        }
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        modalImg.classList.remove('zoomed');
    }

    // Only bind open/close/ESC if not already done by script.js
    if (!alreadyInit) {
        if (!viewButtons.length) return;

        modal.dataset.licenseInitialized = '1';

        viewButtons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var card = this.closest('.license-card');
                var img = card ? card.querySelector('img') : null;
                if (img) openModal(img.src, img.alt);
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
        });
    }

    // Zoom button: always bound exclusively here (not in script.js)
    if (zoomBtn) {
        zoomBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!modal.classList.contains('show')) return;
            var isZoomed = modalImg.classList.toggle('zoomed');
            if (isZoomed) {
                zoomBtn.innerHTML = '<i class="fa-solid fa-magnifying-glass-minus"></i>';
                zoomBtn.setAttribute('aria-label', 'Уменьшить изображение');
            } else {
                zoomBtn.innerHTML = '<i class="fa-solid fa-magnifying-glass-plus"></i>';
                zoomBtn.setAttribute('aria-label', 'Увеличить изображение');
            }
        });
    }
}

function truncateEquipmentCardDescriptions() {
    var maxLength = 177;
    var ellipsis = '...';
    var descriptions = document.querySelectorAll('.equipment-card-content p');

    if (!descriptions.length) return;

    descriptions.forEach(function (description) {
        var fullText = description.textContent.replace(/\s+/g, ' ').trim();
        if (!fullText) return;

        description.textContent = fullText;
        description.removeAttribute('title');

        if (fullText.length <= maxLength) return;

        description.textContent = fullText.slice(0, maxLength - ellipsis.length).trimEnd() + ellipsis;
    });
}

function initEquipmentCardLinks() {
    var cards = document.querySelectorAll('.equipment-grid .equipment-card');
    if (!cards.length) return;

    cards.forEach(function (card) {
        if (card.tagName === 'A') return;

        var url = card.dataset.url || '';
        if (!url) return;

        card.setAttribute('role', 'link');
        card.setAttribute('tabindex', '0');
        card.setAttribute('aria-label', 'Открыть страницу оборудования');

        card.addEventListener('click', function () {
            window.location.href = url;
        });

        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                window.location.href = url;
            }
        });
    });
}
