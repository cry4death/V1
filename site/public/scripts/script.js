document.addEventListener('DOMContentLoaded', function() {
    initializeSmoothScroll();
    initializeFormValidation();
    initializeDropdown();
    initializeMobileMenu();
    initHeaderNavSubmenus();
    initializeSearch();
    initDirectionsAccordion();
    initBackToTopButton();
    initLicenseOverlaysOnHomepage();
    initScrollAnimations();
    initMap();
    initStatCounters();
    initFaqAccordion();
    initHomeHeroVideo();
    handleHeroButtonsLayout();
    initSiteHeader();
});

window.addEventListener('resize', handleHeroButtonsLayout);

document.addEventListener('site:header-loaded', function() {
    initSiteHeader();
});

let siteHeaderEventsBound = false;
let siteHeaderFrame = null;
let siteHeaderCondensed = false;

function getCurrentHeaderOffset() {
    const siteHeader = document.querySelector('header');
    return siteHeader ? Math.round(siteHeader.getBoundingClientRect().height) : 0;
}

function syncSiteHeaderState() {
    const siteHeader = document.querySelector('header');
    if (!siteHeader) return;

    const collapseAt = 96;
    const expandAt = 8;
    const scrollTop = window.scrollY;

    if (siteHeaderCondensed) {
        if (scrollTop <= expandAt) {
            siteHeaderCondensed = false;
        }
    } else if (scrollTop >= collapseAt) {
        siteHeaderCondensed = true;
    }

    siteHeader.classList.toggle('sticky', scrollTop > 8);
    siteHeader.classList.toggle('header--condensed', siteHeaderCondensed);
    document.documentElement.style.setProperty('--site-header-height', `${getCurrentHeaderOffset()}px`);
}

function queueSiteHeaderSync() {
    if (siteHeaderFrame) return;

    siteHeaderFrame = window.requestAnimationFrame(function() {
        siteHeaderFrame = null;
        syncSiteHeaderState();
    });
}

function initSiteHeader() {
    if (!siteHeaderEventsBound) {
        window.addEventListener('scroll', queueSiteHeaderSync, { passive: true });
        window.addEventListener('resize', queueSiteHeaderSync);
        siteHeaderEventsBound = true;
    }

    queueSiteHeaderSync();
}

window.initSiteHeader = initSiteHeader;

// Smooth scrolling for anchor links
function initializeSmoothScroll() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                window.scrollTo({
                    top: targetElement.offsetTop - getCurrentHeaderOffset(),
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Form validation
function initializeFormValidation() {
    const form = document.querySelector('.consultation-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nameInput = form.querySelector('input[name="name"]');
            const phoneInput = form.querySelector('input[name="phone"]');
            const emailInput = form.querySelector('input[name="email"]');
            const serviceInput = form.querySelector('input[name="service"]');
            
            let isValid = true;
            
            if (nameInput && nameInput.value.trim() === '') {
                markInvalid(nameInput);
                isValid = false;
            } else if (nameInput) {
                markValid(nameInput);
            }
            
            if (phoneInput && phoneInput.value.trim() === '') {
                markInvalid(phoneInput);
                isValid = false;
            } else if (phoneInput) {
                markValid(phoneInput);
            }
            
            if (emailInput && emailInput.value.trim() !== '') {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailInput.value)) {
                    markInvalid(emailInput);
                    isValid = false;
                } else {
                    markValid(emailInput);
                }
            }
            
            if (isValid) {
                showFormSuccess(form);
            }
        });
    }
    
    function markInvalid(input) {
        input.style.borderColor = 'red';
    }
    
    function markValid(input) {
        input.style.borderColor = '#b7b7b7';
    }
    
    function showFormSuccess(form) {
        form.style.display = 'none';
        const successMessage = document.createElement('div');
        successMessage.className = 'success-message';
        successMessage.innerHTML = `
            <h3>Спасибо за вашу заявку!</h3>
            <p>Мы получили ваш запрос на прием и свяжемся с вами в ближайшее время.</p>
        `;
        successMessage.style.textAlign = 'center';
        successMessage.style.padding = '30px';
        successMessage.style.color = '#0662c6';
        successMessage.style.fontSize = '24px';
        form.parentNode.appendChild(successMessage);
    }
}

// Dropdown menu functionality
function initializeDropdown() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            
            const submenu = this.querySelector('.submenu');
            if (submenu) {
                submenu.classList.toggle('show');
            }
            dropdowns.forEach(otherDropdown => {
                if (otherDropdown !== dropdown) {
                    const otherSubmenu = otherDropdown.querySelector('.submenu');
                    if (otherSubmenu && otherSubmenu.classList.contains('show')) {
                        otherSubmenu.classList.remove('show');
                    }
                }
            });
        });
    });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            const openDropdowns = document.querySelectorAll('.submenu.show');
            openDropdowns.forEach(submenu => {
                submenu.classList.remove('show');
            });
        }
    });
}

/** Мобильные подменю «Услуги» / «О нас»: класс .is-open (как в header-footer-loader.js статики) */
function closeAllNavSubmenus(exceptItem) {
    document.querySelectorAll('.nav-item--has-submenu.is-open').forEach(function (item) {
        if (item === exceptItem) return;
        item.classList.remove('is-open');
        const t = item.querySelector('.nav-submenu-toggle');
        if (t) t.setAttribute('aria-expanded', 'false');
    });
}

function syncNavSubmenuHeight(item) {
    if (!item) return;
    const submenu = item.querySelector('.nav-submenu');
    if (!submenu) return;
    submenu.style.setProperty('--submenu-open-height', submenu.scrollHeight + 'px');
}

function syncAllMobileNavSubmenuHeights() {
    const navContainer = document.querySelector('.nav-container');
    if (!navContainer) return;
    navContainer.querySelectorAll('.nav-item--has-submenu').forEach(function (item) {
        syncNavSubmenuHeight(item);
    });
}

function initHeaderNavSubmenus() {
    if (document.getElementById('header-placeholder')) {
        return;
    }

    document.addEventListener('click', function (e) {
        const toggle = e.target.closest('.nav-submenu-toggle');
        const navContainer = document.querySelector('.nav-container');
        const isMobile = !!(navContainer && navContainer.classList.contains('show-mobile'));

        if (toggle) {
            const item = toggle.closest('.nav-item--has-submenu');
            if (!item) return;
            e.preventDefault();
            e.stopPropagation();
            if (!isMobile) return;

            syncNavSubmenuHeight(item);
            const isOpen = item.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            closeAllNavSubmenus(isOpen ? item : null);
            return;
        }

        if (!e.target.closest('.nav-item--has-submenu')) {
            closeAllNavSubmenus(null);
        }
    }, true);

    window.addEventListener('resize', syncAllMobileNavSubmenuHeights);
}

// Mobile menu functionality
function initializeMobileMenu() {
    if (document.getElementById('header-placeholder')) {
        return;
    }

    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.nav-container');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('show-mobile');
            this.classList.toggle('active');
            this.setAttribute('aria-expanded', mobileMenu.classList.contains('show-mobile') ? 'true' : 'false');
            
            if (mobileMenu.classList.contains('show-mobile')) {
                closeAllNavSubmenus(null);
                requestAnimationFrame(function () {
                    syncAllMobileNavSubmenuHeights();
                });
            } else {
                closeAllNavSubmenus(null);
            }
        });
    }
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1200 && mobileMenu && mobileMenu.classList.contains('show-mobile')) {
            mobileMenu.classList.remove('show-mobile');
            closeAllNavSubmenus(null);
            if (mobileMenuBtn) {
                mobileMenuBtn.classList.remove('active');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
            }
        }
    });
}

// Search functionality
function initializeSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        initializeSearchInput(input);
    });
}

function initializeSearchInput(input) {
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(this.value);
            }
        });
        const searchBtn = input.closest('.search-box, .mobile-search-box')?.querySelector('.search-btn');
        
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                performSearch(input.value);
            });
        }
    }
}

function performSearch(query) {
    const q = query.trim();
    if (q !== '') {
        window.location.href = `/search?q=${encodeURIComponent(q)}`;
    }
}

// Перестановка кнопок в hero на мобильных (после фото доктора)
function initHomeHeroVideo() {
    const video = document.querySelector('.home-page .hero-video');
    if (!video) return;

    const playVideo = () => {
        const playPromise = video.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(() => {});
        }
    };

    var stopped = false;
    var freezeImg = null;

    function captureToImage() {
        try {
            var w = video.videoWidth;
            var h = video.videoHeight;
            if (!w || !h) return null;
            var canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, w, h);
            var img = document.createElement('img');
            img.src = canvas.toDataURL('image/jpeg', 0.92);
            img.alt = '';
            img.setAttribute('aria-hidden', 'true');
            img.className = 'hero-video';
            img.dataset.frozenFrame = '1';
            var cs = window.getComputedStyle(video);
            img.style.cssText = video.style.cssText;
            img.style.position = cs.position;
            img.style.top = cs.top;
            img.style.left = cs.left;
            img.style.width = cs.width;
            img.style.height = cs.height;
            img.style.minWidth = cs.minWidth;
            img.style.minHeight = cs.minHeight;
            img.style.maxWidth = cs.maxWidth;
            img.style.maxHeight = cs.maxHeight;
            img.style.objectFit = cs.objectFit;
            img.style.objectPosition = cs.objectPosition;
            img.style.transform = cs.transform === 'none' ? '' : cs.transform;
            img.style.transformOrigin = cs.transformOrigin;
            img.style.zIndex = cs.zIndex;
            img.style.pointerEvents = 'none';
            return img;
        } catch (e) {
            return null;
        }
    }

    function freeze() {
        if (stopped) return;
        stopped = true;
        try { video.pause(); } catch (e) { /* ignore */ }
        var img = captureToImage();
        if (img && video.parentElement) {
            freezeImg = img;
            video.parentElement.insertBefore(img, video.nextSibling);
            video.style.opacity = '0';
            video.style.visibility = 'hidden';
        }
    }

    video.addEventListener('timeupdate', function () {
        if (stopped) return;
        var duration = Number.isFinite(video.duration) ? video.duration : 0;
        if (duration > 0 && video.currentTime >= duration - 0.25) {
            freeze();
        }
    });

    video.addEventListener('ended', freeze);

    if (video.readyState >= 2) {
        playVideo();
    } else {
        video.addEventListener('loadeddata', playVideo, { once: true });
    }

    window.addEventListener('resize', function () {
        if (!stopped || !freezeImg) return;
        var cs = window.getComputedStyle(video);
        freezeImg.style.top = cs.top;
        freezeImg.style.left = cs.left;
        freezeImg.style.width = cs.width;
        freezeImg.style.height = cs.height;
        freezeImg.style.minWidth = cs.minWidth;
        freezeImg.style.minHeight = cs.minHeight;
        freezeImg.style.transform = cs.transform === 'none' ? '' : cs.transform;
        freezeImg.style.objectFit = cs.objectFit;
        freezeImg.style.objectPosition = cs.objectPosition;
    });
}

function handleHeroButtonsLayout() {
    const heroContainer = document.querySelector('.hero-section .container');
    const heroContent = document.querySelector('.hero-section .hero-content');
    const heroButtons = document.querySelector('.hero-section .hero-buttons');
    const heroImage = document.querySelector('.hero-section .hero-image');
    const backgroundVideo = document.querySelector('.home-page .hero-video');

    if (!heroContainer || !heroContent || !heroButtons) return;

    if (backgroundVideo) {
        if (heroButtons.parentElement !== heroContent) {
            heroContent.appendChild(heroButtons);
        }
        return;
    }

    if (!heroImage) return;

    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
        // Кнопки сразу после блока с фотографией
        if (heroButtons.parentElement !== heroContainer) {
            heroContainer.insertBefore(heroButtons, heroImage.nextSibling);
        }
    } else {
        // На десктопе возвращаем кнопки обратно в текстовый блок
        if (heroButtons.parentElement !== heroContent) {
            heroContent.appendChild(heroButtons);
        }
    }
}

const heroSection = document.querySelector('.hero-section');

// Smooth Scrolling for Anchor Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            const headerOffset = getCurrentHeaderOffset();
            const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
            
            window.scrollTo({
                top: targetPosition - headerOffset,
                behavior: 'smooth'
            });
        }
    });
});

// Doctors Slider (главная: 3 карточки, стрелки по краям, карточки по центру)
function getVisibleDoctors() {
    const w = window.innerWidth;
    if (w <= 768) return 1;
    if (w < 1024) return 2;
    return 3;
}

function initDoctorsSlider() {
    const section = document.querySelector('.doctors-section.home-doctors');
    if (!section) return;
    const container = section.querySelector('.doctors-container');
    const prevBtn = section.querySelector('.prev-doctor');
    const nextBtn = section.querySelector('.next-doctor');
    if (!container || !prevBtn || !nextBtn) return;
    const cards = container.querySelectorAll('.doctor-card');
    if (!cards.length) return;

    let doctorsPerSlide = getVisibleDoctors();
    let currentGroup = 0;

    function getStepPx() {
        const first = cards[0];
        if (!first) return 0;
        const style = window.getComputedStyle(container);
        const gap = parseFloat(style.gap) || 32;
        const cardWidth = first.offsetWidth;
        return (cardWidth + gap) * doctorsPerSlide;
    }

    function updateSlider() {
        doctorsPerSlide = getVisibleDoctors();
        const totalGroups = Math.ceil(cards.length / doctorsPerSlide);
        currentGroup = Math.max(0, Math.min(currentGroup, totalGroups - 1));
        const step = getStepPx();
        container.style.transform = 'translateX(-' + (currentGroup * step) + 'px)';

        const isFirst = currentGroup <= 0;
        const isLast = currentGroup >= totalGroups - 1;
        prevBtn.disabled = isFirst;
        nextBtn.disabled = isLast;
        prevBtn.style.opacity = isFirst ? '0.5' : '1';
        nextBtn.style.opacity = isLast ? '0.5' : '1';
    }

    prevBtn.addEventListener('click', function() {
        if (currentGroup > 0) {
            currentGroup--;
            updateSlider();
        }
    });
    nextBtn.addEventListener('click', function() {
        const totalGroups = Math.ceil(cards.length / doctorsPerSlide);
        if (currentGroup < totalGroups - 1) {
            currentGroup++;
            updateSlider();
        }
    });

    window.addEventListener('resize', function() {
        const oldPer = doctorsPerSlide;
        const newPer = getVisibleDoctors();
        if (oldPer !== newPer) {
            const firstIdx = currentGroup * oldPer;
            currentGroup = Math.min(Math.floor(firstIdx / newPer), Math.ceil(cards.length / newPer) - 1);
        }
        updateSlider();
    });

    updateSlider();
}

window.addEventListener('doctorsLoaded', initDoctorsSlider);
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initDoctorsSlider, 50);
});

// Form validation
const consultationForm = document.querySelector('.consultation-form');
const phoneInput = document.getElementById('phone');

if (phoneInput) {
    var useBelarusPhoneMask =
        document.body.classList.contains('patient-auth-page') ||
        phoneInput.getAttribute('data-phone-mask') === '375';
    initPhoneMask(phoneInput, { countryCode: useBelarusPhoneMask ? '375' : '7' });
}

if (consultationForm) {
consultationForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const nameInput = document.getElementById('name');
    const serviceSelect = document.getElementById('service');
    const policyCheckbox = document.getElementById('policy');
    
    let isValid = true;
    
    if (nameInput.value.trim().length < 2) {
        nameInput.classList.add('error');
        isValid = false;
    } else {
        nameInput.classList.remove('error');
    }
    
    if (phoneInput.value.replace(/\D/g, '').length < 11) {
        phoneInput.classList.add('error');
        isValid = false;
    } else {
        phoneInput.classList.remove('error');
    }
    
    if (serviceSelect.value === '' || serviceSelect.value === null) {
        serviceSelect.classList.add('error');
        isValid = false;
    } else {
        serviceSelect.classList.remove('error');
    }
    
    if (!policyCheckbox.checked) {
        policyCheckbox.nextElementSibling.classList.add('error');
        isValid = false;
    } else {
        policyCheckbox.nextElementSibling.classList.remove('error');
    }
    
    if (isValid) {
        const successMessage = document.createElement('div');
        successMessage.classList.add('form-success');
        successMessage.textContent = 'Спасибо за заявку! Наш специалист свяжется с вами в ближайшее время.';
        
        consultationForm.innerHTML = '';
        consultationForm.appendChild(successMessage);
        successMessage.scrollIntoView({ behavior: 'smooth' });
    }
});
}

function handleResponsive() {
    document.querySelectorAll('.fade-in').forEach(element => {
        element.classList.add('visible');
    });
}

// Initialize
window.addEventListener('load', handleResponsive);

// Функция для инициализации аккордеона с направлениями
function initDirectionsAccordion() {
    const directionItems = document.querySelectorAll('.direction-item');
    
    if (directionItems.length === 0) return;
    
    directionItems.forEach(item => {
        const title = item.querySelector('.direction-title');
        const info = item.querySelector('.direction-info');
        
        title.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            directionItems.forEach(otherItem => {
                otherItem.classList.remove('active');
            });
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
}

// Initialize Yandex Map in the appointment section
function initMap() {
    if (document.getElementById('map')) {
        const script = document.createElement('script');
        script.src = 'https://api-maps.yandex.ru/2.1/?apikey=your-api-key&lang=ru_RU';
        script.async = true;
        script.onload = function() {
            ymaps.ready(createMap);
        };
        document.head.appendChild(script);
    }
}

function createMap() {
    const myMap = new ymaps.Map('map', {
        center: [55.76, 37.64], // Moscow coordinates, replace with your clinic coordinates
        zoom: 16,
        controls: ['zoomControl', 'geolocationControl', 'fullscreenControl']
    });
    const myPlacemark = new ymaps.Placemark([55.76, 37.64], {
        hintContent: 'Медицинская клиника',
        balloonContent: '<strong>Медицинская клиника</strong><br>' +
                        'г. Москва, ул. Медицинская, д. 123<br>' +
                        'Телефон: +7 (495) 123-45-67<br>' +
                        '<a href="' + (typeof getBookingStartUrl === 'function' ? getBookingStartUrl() : ((typeof window.BOOKING_INDEX_URL === 'string' && window.BOOKING_INDEX_URL) || '/booking')) + '">Записаться на прием</a>'
    }, {
        preset: 'islands#redMedicalIcon', // Using a predefined medical icon
        iconColor: '#4682b4',
        zIndex: 1000
    });
    myMap.geoObjects.add(myPlacemark);
    myMap.setCenter([55.76, 37.64]);
    myMap.behaviors.disable('scrollZoom');
    myMap.behaviors.enable('multiTouch');
    const addressControl = new ymaps.control.Button({
        data: {
            content: 'Показать адрес',
            title: 'Нажмите, чтобы увидеть адрес клиники'
        },
        options: {
            selectOnClick: false,
            maxWidth: 150
        }
    });
    addressControl.events.add('click', function() {
        myPlacemark.balloon.open();
    });
    myMap.controls.add(addressControl, {float: 'right', floatIndex: 100});
}

function initStatCounters() {
    const statValues = document.querySelectorAll('.stat-value[data-target]');
    if (!statValues.length) return;

    function animateCounter(el, from, to, duration) {
        const start = performance.now();
        const isLarge = to >= 1000;
        function step(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(from + (to - from) * eased);
            el.textContent = isLarge ? current.toLocaleString('ru-RU') : current;
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.target, 10);
                animateCounter(el, 0, target, 1600);
                obs.unobserve(el);
            }
        });
    }, { threshold: 0.4 });

    statValues.forEach(el => observer.observe(el));
}

function initFaqAccordion() {
    const items = document.querySelectorAll('.faq-item');
    if (!items.length) return;

    items.forEach(item => {
        const btn = item.querySelector('.faq-question');
        if (!btn) return;
        btn.addEventListener('click', () => {
            const isOpen = item.classList.contains('open');
            items.forEach(other => {
                other.classList.remove('open');
                const otherBtn = other.querySelector('.faq-question');
                if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
            });
            if (!isOpen) {
                item.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });
}

function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    if (!animatedElements.length) return;

    const viewportH = window.innerHeight;
    const belowFold = [];

    animatedElements.forEach(el => {
        const top = el.getBoundingClientRect().top;
        if (top < viewportH) {
            el.style.transition = 'none';
            el.classList.add('visible');
            el.offsetHeight;
            el.style.transition = '';
        } else {
            belowFold.push(el);
        }
    });

    if (!belowFold.length) return;

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    belowFold.forEach(el => observer.observe(el));
}

// Функция инициализации модального окна для лицензий на главной странице
function initLicenseOverlaysOnHomepage() {
    const modal = document.getElementById('licenseModal');
    const modalImg = document.getElementById('modalLicenseImage');
    const closeBtn = document.querySelector('.license-modal-close');
    const viewButtons = document.querySelectorAll('.view-license-btn');

    if (!modal || !modalImg || !closeBtn || viewButtons.length === 0) return;

    // Guard against double-init (script-about-clinic-page.js runs on the about page too)
    if (modal.dataset.licenseInitialized === '1') return;
    modal.dataset.licenseInitialized = '1';

    function handleEscKey(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleEscKey);
        }
    }

    viewButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const card = this.closest('.license-card');
            const img = card ? card.querySelector('img') : null;
            if (!img) return;
            modalImg.src = img.src;
            modalImg.alt = img.alt;
            modalImg.classList.remove('zoomed');
            const zoomBtn = modal.querySelector('.license-zoom-btn');
            if (zoomBtn) {
                zoomBtn.innerHTML = '<i class="fa-solid fa-magnifying-glass-plus"></i>';
                zoomBtn.setAttribute('aria-label', 'Увеличить изображение');
            }
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            document.addEventListener('keydown', handleEscKey);
        });
    });

    closeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        modal.classList.remove('show');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleEscKey);
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleEscKey);
        }
    });
}

document.addEventListener('click', function (e) {
    var card = e.target.closest('.blog-card');
    if (!card) return;
    if (e.target.closest('a')) return;
    var url = card.dataset.url;
    if (url) window.location.href = url;
});

document.addEventListener('click', function (e) {
    var card = e.target.closest('.doctor-card');
    if (!card) return;
    if (e.target.closest('.doctor-btn')) return;
    if (e.target.closest('a')) return;
    var url = card.dataset.url;
    if (url) window.location.href = url;
});

// Highlight services submenu item matching the URL hash (services.index page)
(function () {
    function syncServicesSubmenuActive() {
        var links = document.querySelectorAll('.nav-submenu--mega .nav-submenu-link');
        if (!links.length) return;
        var hash = (window.location.hash || '').replace(/^#/, '').toLowerCase();
        var path = (window.location.pathname || '').toLowerCase();
        var onServicesIndex = /\/services\/?$/.test(path);
        var hasAnyActive = false;
        links.forEach(function (link) {
            var href = link.getAttribute('href') || '';
            var idx = href.indexOf('#');
            var linkHash = idx >= 0 ? href.substring(idx + 1).toLowerCase() : '';
            if (hash && linkHash && linkHash === hash) {
                link.classList.add('nav-submenu-link--active');
                hasAnyActive = true;
            } else {
                link.classList.remove('nav-submenu-link--active');
            }
        });
        if (!hasAnyActive && onServicesIndex && !hash) {
            links[0].classList.add('nav-submenu-link--active');
        }
    }
    document.addEventListener('DOMContentLoaded', syncServicesSubmenuActive);
    window.addEventListener('hashchange', syncServicesSubmenuActive);
})();

// Generic image lightbox: clicking promo hero image or .zoomable-image opens enlarged view
(function () {
    function open(src, alt) {
        var lb = document.getElementById('imageLightbox');
        var img = document.getElementById('imageLightboxImg');
        if (!lb || !img) return;
        img.src = src;
        img.alt = alt || '';
        lb.classList.add('show');
        lb.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
    function close() {
        var lb = document.getElementById('imageLightbox');
        var img = document.getElementById('imageLightboxImg');
        if (!lb) return;
        lb.classList.remove('show');
        lb.setAttribute('aria-hidden', 'true');
        if (img) img.src = '';
        document.body.style.overflow = '';
    }
    document.addEventListener('click', function (e) {
        var zoomBtn = e.target.closest('.promo-zoom-btn');
        if (zoomBtn) {
            e.preventDefault();
            var wrap = zoomBtn.closest('.promo-hero-image');
            var heroImg = wrap ? wrap.querySelector('img') : null;
            if (heroImg) open(heroImg.getAttribute('src'), heroImg.getAttribute('alt'));
            return;
        }
        var trigger = e.target.closest('.promo-hero-image img, .zoomable-image, .promo-content-wrap .promo-image img');
        if (trigger) {
            e.preventDefault();
            open(trigger.getAttribute('src'), trigger.getAttribute('alt'));
            return;
        }
        var lb = document.getElementById('imageLightbox');
        if (!lb || !lb.classList.contains('show')) return;
        if (e.target.closest('.image-lightbox-close') || e.target.id === 'imageLightbox') {
            close();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') close();
    });
})();
