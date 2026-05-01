/**
 * Слайдер акций:
 * — навигация стрелками и точками
 * — свайп пальцем (touch)
 * — автопрокрутка каждые 7 секунд
 * — пауза при наведении или касании
 */
(function () {
    'use strict';

    var AUTOPLAY_DELAY = 7000;

    var track        = document.querySelector('.promo-slider-track');
    var viewport     = document.querySelector('.promo-slider-viewport');
    var slides       = document.querySelectorAll('.promo-slide');
    var btnPrev      = document.querySelector('.promo-slider-prev');
    var btnNext      = document.querySelector('.promo-slider-next');
    var dotsContainer = document.querySelector('.promo-slider-dots');

    if (!track || !slides.length) return;

    var total   = slides.length;
    var current = 0;
    var autoplayTimer = null;

    /* ---------- Инициализация ширины ---------- */
    track.style.width = (total * 100) + '%';
    slides.forEach(function (el) {
        var pct = 100 / total;
        el.style.flex  = '0 0 ' + pct + '%';
        el.style.width = pct + '%';
    });

    /* ---------- Навигация ---------- */
    function goTo(index) {
        if (index < 0)      index = total - 1;   // зацикливание
        if (index >= total) index = 0;
        current = index;
        var percent = (100 / total) * current;
        track.style.transform = 'translate3d(-' + percent + '%, 0, 0)';
        updateDots();
        updateArrows();
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function updateDots() {
        if (!dotsContainer) return;
        dotsContainer.querySelectorAll('.promo-dot').forEach(function (dot, i) {
            var active = i === current;
            dot.classList.toggle('active', active);
            dot.setAttribute('aria-selected', active ? 'true' : 'false');
        });
    }

    function updateArrows() {
        if (btnPrev) btnPrev.disabled = false;  // зациклено — всегда активны
        if (btnNext) btnNext.disabled = false;
    }

    /* ---------- Точки ---------- */
    function buildDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        for (var i = 0; i < total; i++) {
            var dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'promo-dot' + (i === 0 ? ' active' : '');
            dot.setAttribute('role', 'tab');
            dot.setAttribute('aria-label', 'Акция ' + (i + 1));
            dot.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
            dot.dataset.index = String(i);
            dot.addEventListener('click', function () {
                var idx = parseInt(this.dataset.index, 10);
                if (!isNaN(idx)) { goTo(idx); restartAutoplay(); }
            });
            dotsContainer.appendChild(dot);
        }
    }

    /* ---------- Автопрокрутка ---------- */
    function startAutoplay() {
        if (total <= 1) return;
        autoplayTimer = setInterval(next, AUTOPLAY_DELAY);
    }

    function stopAutoplay() {
        if (autoplayTimer) { clearInterval(autoplayTimer); autoplayTimer = null; }
    }

    function restartAutoplay() {
        stopAutoplay();
        startAutoplay();
    }

    /* Пауза при наведении мыши */
    if (viewport) {
        viewport.addEventListener('mouseenter', stopAutoplay);
        viewport.addEventListener('mouseleave', startAutoplay);
    }

    /* ---------- Стрелки ---------- */
    if (btnPrev) btnPrev.addEventListener('click', function () { prev(); restartAutoplay(); });
    if (btnNext) btnNext.addEventListener('click', function () { next(); restartAutoplay(); });

    /* ---------- Свайп пальцем (touch) ---------- */
    var touchStartX = null;
    var touchStartY = null;

    if (viewport) {
        viewport.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].clientX;
            touchStartY = e.changedTouches[0].clientY;
        }, { passive: true });

        viewport.addEventListener('touchend', function (e) {
            if (touchStartX === null) return;
            var dx = e.changedTouches[0].clientX - touchStartX;
            var dy = e.changedTouches[0].clientY - touchStartY;

            /* Засчитываем только горизонтальный свайп (не скролл страницы) */
            if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 40) {
                if (dx < 0) next(); else prev();
                restartAutoplay();
            }
            touchStartX = null;
            touchStartY = null;
        }, { passive: true });
    }

    /* ---------- Ресайз ---------- */
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () { goTo(current); }, 100);
    });

    /* ---------- Старт ---------- */
    buildDots();
    updateArrows();
    goTo(0);
    startAutoplay();
})();
