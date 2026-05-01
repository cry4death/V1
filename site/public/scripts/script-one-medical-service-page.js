document.addEventListener('DOMContentLoaded', function () {
    initBackToTopButton();
    initServiceScrollAnimations();

    (function () {
        var VISIBLE_LIMIT = 4;
        var container = document.querySelector('.service-doctors-container');
        var btn = document.getElementById('service-doctors-show-more');
        if (!container || !btn) return;
        var cards = container.querySelectorAll('.doctor-card');
        if (cards.length <= VISIBLE_LIMIT) return;
        for (var i = VISIBLE_LIMIT; i < cards.length; i++) {
            cards[i].style.display = 'none';
        }
        btn.hidden = false;
        btn.addEventListener('click', function () {
            for (var i = 0; i < cards.length; i++) {
                cards[i].style.display = '';
            }
            btn.hidden = true;
        });
    })();
});

function initServiceScrollAnimations() {
    var elements = document.querySelectorAll('.service-page .animate-on-scroll');
    if (!elements.length) return;

    var viewportH = window.innerHeight;
    var belowFold = [];

    elements.forEach(function (el, index) {
        var top = el.getBoundingClientRect().top;
        if (top < viewportH) {
            el.style.transition = 'none';
            el.classList.add('visible');
            el.offsetHeight;
            el.style.transition = '';
        } else {
            if (el.classList.contains('service-step')) {
                el.style.transitionDelay = (index % 5) * 0.08 + 's';
            }
            belowFold.push(el);
        }
    });

    if (!belowFold.length) return;

    var observer = new IntersectionObserver(function (entries, obs) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    belowFold.forEach(function (el) { observer.observe(el); });
}
