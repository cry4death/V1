(function () {
  'use strict';

  function initReviewsSlider() {
    var section   = document.querySelector('.reviews-slider-section');
    var track     = document.getElementById('reviews-track');
    var dotsCont  = document.getElementById('reviews-dots');
    var prevBtn   = section && section.querySelector('.reviews-slider-prev');
    var nextBtn   = section && section.querySelector('.reviews-slider-next');

    if (!section || !track || !dotsCont || !prevBtn || !nextBtn) return;

    var cards   = [].slice.call(track.querySelectorAll('.review-card'));
    var total   = cards.length;
    if (total === 0) return;

    var current = 0;
    var GAP     = 16;

    function getVisible() {
      var w = window.innerWidth;
      if (w <= 600) return 1;
      if (w <= 900) return 2;
      return 3;
    }

    function getMaxStep() {
      return Math.max(0, total - getVisible());
    }

    function buildDots() {
      dotsCont.innerHTML = '';
      var max = getMaxStep();
      for (var i = 0; i <= max; i++) {
        var dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'reviews-dot' + (i === current ? ' is-active' : '');
        dot.setAttribute('aria-label', 'Страница ' + (i + 1));
        (function (idx) {
          dot.addEventListener('click', function () { goTo(idx); });
        })(i);
        dotsCont.appendChild(dot);
      }
    }

    function updateSlider() {
      var cardW = cards[0].offsetWidth;
      var step  = cardW + GAP;
      track.style.transform = 'translateX(-' + (current * step) + 'px)';

      var dots = [].slice.call(dotsCont.querySelectorAll('.reviews-dot'));
      dots.forEach(function (dot, i) {
        dot.classList.toggle('is-active', i === current);
      });

      prevBtn.disabled = current === 0;
      nextBtn.disabled = current >= getMaxStep();
    }

    function goTo(idx) {
      current = Math.max(0, Math.min(getMaxStep(), idx));
      updateSlider();
    }

    prevBtn.addEventListener('click', function () { goTo(current - 1); });
    nextBtn.addEventListener('click', function () { goTo(current + 1); });

    var touchStartX = 0;
    var touchDeltaX = 0;
    track.addEventListener('touchstart', function (e) {
      touchStartX = e.touches[0].clientX;
      touchDeltaX = 0;
    }, { passive: true });
    track.addEventListener('touchmove', function (e) {
      touchDeltaX = e.touches[0].clientX - touchStartX;
    }, { passive: true });
    track.addEventListener('touchend', function () {
      if (Math.abs(touchDeltaX) > 40) {
        goTo(touchDeltaX < 0 ? current + 1 : current - 1);
      }
    });

    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        if (current > getMaxStep()) current = getMaxStep();
        buildDots();
        updateSlider();
      }, 150);
    });

    cards.forEach(function (card) {
      var textEl = card.querySelector('.review-card-text');
      if (!textEl) return;
      if (textEl.scrollHeight > textEl.clientHeight + 4) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'review-read-more';
        btn.textContent = 'Читать полностью';
        card.appendChild(btn);
      }
    });

    buildDots();
    updateSlider();
  }

  function initReviewModal() {
    var overlay   = document.getElementById('review-modal-overlay');
    var closeBtn  = document.getElementById('review-modal-close');
    if (!overlay) return;

    function openModal(card) {
      var textEl   = card.querySelector('.review-card-text');
      var avatarEl = card.querySelector('.review-card-avatar');
      var nameEl   = card.querySelector('.review-card-name');
      var dateEl   = card.querySelector('.review-card-date');
      var starsEl  = card.querySelector('.review-card-stars');

      document.getElementById('review-modal-avatar').textContent = avatarEl ? avatarEl.textContent.trim() : '';
      document.getElementById('review-modal-name').textContent   = nameEl   ? nameEl.textContent.trim()   : '';
      document.getElementById('review-modal-date').textContent   = dateEl   ? dateEl.textContent.trim()   : '';
      document.getElementById('review-modal-stars').innerHTML    = starsEl  ? starsEl.innerHTML           : '';
      document.getElementById('review-modal-text').textContent   = textEl   ? textEl.textContent.trim()   : '';

      overlay.hidden = false;
      document.body.style.overflow = 'hidden';
      if (closeBtn) setTimeout(function () { closeBtn.focus(); }, 50);
    }

    function closeModal() {
      overlay.hidden = true;
      document.body.style.overflow = '';
    }

    document.addEventListener('click', function (e) {
      if (e.target && e.target.classList.contains('review-read-more')) {
        var card = e.target.closest('.review-card');
        if (card) openModal(card);
      }
    });

    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closeModal();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !overlay.hidden) closeModal();
    });
  }

  function initReviewForm() {
    var openBtn    = document.getElementById('reviews-open-form-btn');
    var overlay    = document.getElementById('review-form-modal-overlay');
    var closeBtn   = document.getElementById('review-form-modal-close');
    var cancelBtn  = document.getElementById('reviews-cancel-btn');
    var form       = document.getElementById('review-form');
    if (!openBtn || !overlay) return;

    function openFormModal() {
      overlay.hidden = false;
      document.body.style.overflow = 'hidden';
      var firstInput = overlay.querySelector('input, textarea');
      if (firstInput) setTimeout(function () { firstInput.focus(); }, 100);
    }

    function closeFormModal() {
      overlay.classList.add('is-closing');
      overlay.addEventListener('animationend', function onClose() {
        overlay.removeEventListener('animationend', onClose);
        overlay.classList.remove('is-closing');
        overlay.hidden = true;
        document.body.style.overflow = '';
        openBtn.focus({ preventScroll: true });
      }, { once: true });
    }

    openBtn.addEventListener('click', openFormModal);

    var heroOpenBtn = document.getElementById('hero-open-review-btn');
    if (heroOpenBtn) heroOpenBtn.addEventListener('click', openFormModal);

    if (closeBtn) closeBtn.addEventListener('click', closeFormModal);

    if (cancelBtn) cancelBtn.addEventListener('click', closeFormModal);

    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closeFormModal();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !overlay.hidden) closeFormModal();
    });

    var starBtns = [].slice.call(document.querySelectorAll('.review-star-btn'));
    var ratingHidden = document.getElementById('review-rating-input');
    var selectedRating = ratingHidden ? parseInt(ratingHidden.value, 10) : 0;
    if (!selectedRating || selectedRating < 1 || selectedRating > 5) {
      selectedRating = 5;
    }
    if (ratingHidden) {
      ratingHidden.value = String(selectedRating);
    }

    function renderStars(hovered, selected) {
      starBtns.forEach(function (btn) {
        var val = parseInt(btn.dataset.value, 10);
        btn.classList.toggle('is-active', val <= selected);
        btn.classList.toggle('is-hovered', hovered > 0 && val <= hovered);
      });
    }

    starBtns.forEach(function (btn) {
      var val = parseInt(btn.dataset.value, 10);
      btn.addEventListener('mouseenter', function () { renderStars(val, selectedRating); });
      btn.addEventListener('mouseleave', function () { renderStars(0, selectedRating); });
      btn.addEventListener('click', function () {
        selectedRating = val;
        if (ratingHidden) {
          ratingHidden.value = String(selectedRating);
        }
        renderStars(0, selectedRating);
        var errEl = document.getElementById('review-stars-error');
        if (errEl) errEl.hidden = true;
      });
      btn.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          selectedRating = val;
          if (ratingHidden) {
            ratingHidden.value = String(selectedRating);
          }
          renderStars(0, selectedRating);
        }
      });
    });

    renderStars(0, selectedRating);

    if (!form) return;

    form.addEventListener('submit', function (e) {
      var valid = true;

      var nameInput = document.getElementById('review-name');
      var nameError = document.getElementById('review-name-error');
      if (nameInput && !nameInput.value.trim()) {
        nameInput.classList.add('is-error');
        if (nameError) nameError.hidden = false;
        valid = false;
      } else if (nameInput) {
        nameInput.classList.remove('is-error');
        if (nameError) nameError.hidden = true;
      }

      var starsError = document.getElementById('review-stars-error');
      if (selectedRating < 1 || selectedRating > 5) {
        if (starsError) starsError.hidden = false;
        valid = false;
      } else {
        if (starsError) starsError.hidden = true;
      }

      var textInput = document.getElementById('review-text');
      var textError = document.getElementById('review-text-error');
      if (textInput && !textInput.value.trim()) {
        textInput.classList.add('is-error');
        if (textError) textError.hidden = false;
        valid = false;
      } else if (textInput) {
        textInput.classList.remove('is-error');
        if (textError) textError.hidden = true;
      }

      if (!valid) {
        e.preventDefault();
        return;
      }

      if (ratingHidden) {
        ratingHidden.value = String(selectedRating);
      }
    });

    [document.getElementById('review-name'), document.getElementById('review-text')].forEach(function (el) {
      if (!el) return;
      el.addEventListener('input', function () {
        el.classList.remove('is-error');
        var errEl = document.getElementById(el.id + '-error');
        if (errEl) errEl.hidden = true;
      });
    });
  }

  function initDoctorNav() {
    var nav = document.querySelector('.doctor-nav');
    var links = [].slice.call(document.querySelectorAll('.doctor-nav-link[href^="#"]'));
    if (!nav || !links.length) return;

    var items = links.map(function (link) {
      var id = (link.getAttribute('href') || '').slice(1);
      var section = id ? document.getElementById(id) : null;
      return section ? { id: id, link: link, section: section } : null;
    }).filter(Boolean);

    if (!items.length) return;

    function clearDoctorSectionHash() {
      var currentHash = window.location.hash;
      var isDoctorSectionHash = false;

      items.forEach(function (item) {
        if ('#' + item.id === currentHash) {
          isDoctorSectionHash = true;
        }
      });

      if (!isDoctorSectionHash) return;

      if (window.history && typeof window.history.replaceState === 'function') {
        window.history.replaceState(null, '', window.location.pathname + window.location.search);
      }
    }

    function readMetric(name) {
      return parseFloat(getComputedStyle(document.documentElement).getPropertyValue(name)) || 0;
    }

    function syncDoctorNavMetrics() {
      var siteHeader = document.querySelector('header');
      var headerHeight = siteHeader ? Math.round(siteHeader.getBoundingClientRect().height) : 0;

      document.documentElement.style.setProperty('--doctor-sticky-offset', headerHeight + 'px');
    }

    function scrollToSection(section) {
      syncDoctorNavMetrics();

      var headerHeight = readMetric('--doctor-sticky-offset');
      var offset = headerHeight + 28;
      var targetTop = section.getBoundingClientRect().top + window.pageYOffset - offset;

      window.scrollTo({
        top: Math.max(0, targetTop),
        behavior: 'smooth'
      });
    }

    var currentLinkId = '';

    function setCurrentLink(activeId) {
      var hasChanged = activeId !== currentLinkId;
      currentLinkId = activeId;

      items.forEach(function (item) {
        item.link.classList.toggle('is-current', item.id === activeId);
      });
    }

    function updateCurrentSection() {
      var headerHeight = readMetric('--doctor-sticky-offset');
      var marker = headerHeight + 56;
      var currentId = items[0].id;

      items.forEach(function (item) {
        var sectionTop = item.section.getBoundingClientRect().top;
        if (sectionTop - marker <= 0) {
          currentId = item.id;
        }
      });

      setCurrentLink(currentId);
    }

    syncDoctorNavMetrics();
    clearDoctorSectionHash();
    window.addEventListener('resize', syncDoctorNavMetrics);
    window.addEventListener('load', syncDoctorNavMetrics);
    window.addEventListener('scroll', updateCurrentSection, { passive: true });
    setTimeout(syncDoctorNavMetrics, 150);
    setTimeout(updateCurrentSection, 180);

    if ('MutationObserver' in window && document.body) {
      var layoutObserver = new MutationObserver(function () {
        if (document.querySelector('header')) {
          syncDoctorNavMetrics();
          updateCurrentSection();
        }
      });
      layoutObserver.observe(document.body, { childList: true, subtree: true });
    }

    items.forEach(function (item) {
      item.link.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        setCurrentLink(item.id);
        scrollToSection(item.section);
      }, true);
    });

    updateCurrentSection();
  }

  function initCareerTimelineReveal() {
    var timelines = [].slice.call(document.querySelectorAll('#education-experience .doctor-timeline'));
    var items = [].slice.call(document.querySelectorAll('#education-experience .doctor-timeline li'));
    if (!items.length) return;

    timelines.forEach(function (timeline) {
      timeline.classList.add('is-animated');
    });

    items.forEach(function (item, index) {
      item.style.setProperty('--timeline-index', index);
    });

    if (!('IntersectionObserver' in window)) {
      items.forEach(function (item) {
        item.classList.add('is-visible');
      });
      return;
    }

    var observer = new IntersectionObserver(function (entries, currentObserver) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        currentObserver.unobserve(entry.target);
      });
    }, {
      rootMargin: '0px 0px -12% 0px',
      threshold: 0.2
    });

    items.forEach(function (item) {
      observer.observe(item);
    });
  }

  function init() {
    initDoctorNav();
    initCareerTimelineReveal();
    initReviewsSlider();
    initReviewModal();
    initReviewForm();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
