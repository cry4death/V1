/**
 * Shared utility functions used across multiple pages
 */

/**
 * Initialize Back to Top button functionality
 * Creates a floating button that appears on scroll and smoothly scrolls to top when clicked
 */
function initBackToTopButtonLegacy() {
    if (!document.querySelector('.back-to-top')) {
        const backToTopBtn = document.createElement('button');
        backToTopBtn.className = 'back-to-top';
        backToTopBtn.setAttribute('aria-label', 'Вернуться наверх');
        backToTopBtn.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
        document.body.appendChild(backToTopBtn);
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// Override with viewport-aware positioning so the button stays stable on mobile.
function initBackToTopButton() {
    let backToTopBtn = document.querySelector('.back-to-top');

    if (!backToTopBtn) {
        backToTopBtn = document.createElement('button');
        backToTopBtn.className = 'back-to-top';
        backToTopBtn.setAttribute('aria-label', 'Back to top');
        backToTopBtn.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
        document.body.appendChild(backToTopBtn);
    }

    if (backToTopBtn.dataset.initialized === 'true') {
        return;
    }

    backToTopBtn.dataset.initialized = 'true';

    const updateBackToTopState = () => {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    };

    const updateBackToTopPosition = () => {
        const baseBottomOffset = window.innerWidth <= 768 ? 16 : 20;
        const visualViewport = window.visualViewport;
        let viewportCompensation = 0;

        if (visualViewport) {
            viewportCompensation = Math.max(
                window.innerHeight - visualViewport.height - visualViewport.offsetTop,
                0
            );
        }

        backToTopBtn.style.setProperty(
            '--back-to-top-bottom-offset',
            `${baseBottomOffset + viewportCompensation}px`
        );
    };

    updateBackToTopState();
    updateBackToTopPosition();

    window.addEventListener('scroll', updateBackToTopState, { passive: true });
    window.addEventListener('resize', updateBackToTopPosition, { passive: true });

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', updateBackToTopPosition, { passive: true });
        window.visualViewport.addEventListener('scroll', updateBackToTopPosition, { passive: true });
    }

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Initialize phone input mask with formatting
 * @param {HTMLInputElement} input - The phone input element
 * @param {Object} options - Format options
 * @param {string} options.countryCode - '7' for Russia (+7), '375' for Belarus (+375)
 */
function initCardLinks() {
    document.addEventListener('click', function(e) {
        var doctorCard = e.target.closest('.doctor-card');
        if (doctorCard) {
            if (e.target.closest('.doctor-btn')) return;
            if (e.target.closest('a')) return;
            var dUrl = doctorCard.dataset.url;
            if (dUrl) window.location.href = dUrl;
            return;
        }

        var blogCard = e.target.closest('.blog-card');
        if (blogCard) {
            if (e.target.closest('a')) return;
            var bUrl = blogCard.dataset.url;
            if (bUrl) window.location.href = bUrl;
            return;
        }

        var promoCard = e.target.closest('.promo-card');
        if (promoCard) {
            if (e.target.closest('a')) return;
            var pUrl = promoCard.dataset.url;
            if (pUrl) window.location.href = pUrl;
            return;
        }
    });
}

document.addEventListener('DOMContentLoaded', initCardLinks);

function initPhoneMask(input, options) {
    if (!input) return;

    var opts = options || {};
    var countryCode = opts.countryCode || '7';
    var maxDigits = countryCode === '375' ? 12 : 11;

    input.addEventListener('input', function() {
        var cursorPos = input.selectionStart;
        var oldLength = input.value.length;
        var digits = input.value.replace(/\D/g, '');

        digits = digits.substring(0, maxDigits);

        var formattedValue = '';
        if (digits.length > 0) {
            formattedValue = '+';
        }

        if (countryCode === '375') {
            if (digits.length > 0) {
                formattedValue += digits.substring(0, 3);
            }
            if (digits.length > 3) {
                formattedValue += ' (' + digits.substring(3, 5);
            }
            if (digits.length > 5) {
                formattedValue += ') ' + digits.substring(5, 8);
            }
            if (digits.length > 8) {
                formattedValue += '-' + digits.substring(8, 10);
            }
            if (digits.length > 10) {
                formattedValue += '-' + digits.substring(10, 12);
            }
        } else {
            if (digits.length > 0) {
                var first = digits.charAt(0);
                digits = (first === '8' || first === '7') ? '7' + digits.substring(1) : '7' + digits;
                digits = digits.substring(0, 11);
                formattedValue += '7';
            }
            if (digits.length > 1) {
                formattedValue += ' (' + digits.substring(1, 4);
            }
            if (digits.length > 4) {
                formattedValue += ') ' + digits.substring(4, 7);
            }
            if (digits.length > 7) {
                formattedValue += '-' + digits.substring(7, 9);
            }
            if (digits.length > 9) {
                formattedValue += '-' + digits.substring(9, 11);
            }
        }

        input.value = formattedValue;
        var lengthDiff = input.value.length - oldLength;
        input.setSelectionRange(
            cursorPos + (lengthDiff > 0 ? lengthDiff : 0),
            cursorPos + (lengthDiff > 0 ? lengthDiff : 0)
        );
    });
}

/**
 * URL старта онлайн-записи: сначала data-booking-url с body (серверный route), затем fallback.
 */
function getBookingStartUrl() {
    var body = document.body;
    var fromData = body && body.getAttribute('data-booking-url');
    if (fromData) {
        return fromData;
    }
    if (typeof window.BOOKING_INDEX_URL === 'string' && window.BOOKING_INDEX_URL) {
        return window.BOOKING_INDEX_URL;
    }
    return '/booking';
}
