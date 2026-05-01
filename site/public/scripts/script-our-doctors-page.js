(function () {
  'use strict';

  var CARDS_PER_PAGE = 8;

  function initDoctorsScrollAnimations(container) {
    var cards = container.querySelectorAll('.doctor-card.animate-on-scroll');
    if (!cards.length) return;
    var observer = new IntersectionObserver(function (entries, obs) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    cards.forEach(function (el) { observer.observe(el); });
  }

  function getFilteredCards(container, filters) {
    var cards = [].slice.call(container.querySelectorAll('.doctor-card'));
    var search = (filters.search || '').trim().toLowerCase();
    var spec = (filters.specialization || '').trim();
    var cat = (filters.category || '').trim();
    var age = (filters.patientAge || '').trim();
    return cards.filter(function (card) {
      if (search && card.dataset.fullName && card.dataset.fullName.indexOf(search) === -1) return false;
      if (spec && (card.dataset.specialization || '') !== spec) return false;
      if (cat && (card.dataset.category || '') !== cat) return false;
      if (age && (card.dataset.patientAge || '') !== age) return false;
      return true;
    });
  }

  function updateCardsVisibility(container, filteredCards, state) {
    var start = (state.currentPage - 1) * CARDS_PER_PAGE;
    var endPage = state.currentPage * CARDS_PER_PAGE;
    var endShowMore = state.visibleCount;
    var idxInFiltered, show;
    container.querySelectorAll('.doctor-card').forEach(function (cardEl) {
      idxInFiltered = filteredCards.indexOf(cardEl);
      if (idxInFiltered === -1) {
        cardEl.style.display = 'none';
        return;
      }
      if (state.viewMode === 'page') {
        show = idxInFiltered >= start && idxInFiltered < endPage;
      } else {
        show = idxInFiltered < endShowMore;
      }
      cardEl.style.display = show ? '' : 'none';
    });
  }

  function hasMoreCardsToLoad(state, totalFiltered) {
    if (totalFiltered <= 0) return false;
    if (state.viewMode === 'page') {
      return (state.currentPage * CARDS_PER_PAGE) < totalFiltered;
    }
    return state.visibleCount < totalFiltered;
  }

  function updateShowMoreButton(btn, totalFiltered, state) {
    btn.style.display = hasMoreCardsToLoad(state, totalFiltered) ? '' : 'none';
  }

  function renderPagination(paginationWrap, totalPages, state, onPageClick) {
    paginationWrap.innerHTML = '';
    if (totalPages <= 1) return;
    var activePage = state.viewMode === 'page' ? state.currentPage : Math.ceil(state.visibleCount / CARDS_PER_PAGE);
    for (var i = 1; i <= totalPages; i++) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'doctors-pagination-btn' + (i === activePage ? ' active' : '');
      btn.textContent = i;
      btn.dataset.page = String(i);
      btn.setAttribute('aria-label', 'Страница ' + i);
      if (i === activePage) btn.setAttribute('aria-current', 'page');
      (function (pageNum) {
        btn.addEventListener('click', function () { onPageClick(pageNum); });
      })(i);
      paginationWrap.appendChild(btn);
    }
  }

  function updatePaginationActive(paginationWrap, state) {
    var activePage = state.viewMode === 'page' ? state.currentPage : Math.ceil(state.visibleCount / CARDS_PER_PAGE);
    paginationWrap.querySelectorAll('.doctors-pagination-btn').forEach(function (btn) {
      var pageNum = parseInt(btn.dataset.page, 10);
      var isActive = pageNum === activePage;
      btn.classList.toggle('active', isActive);
      btn.setAttribute('aria-current', isActive ? 'page' : 'false');
    });
  }

  function initCustomSelects(section) {
    var wraps = section.querySelectorAll('.custom-select-wrap');
    wraps.forEach(function (wrap) {
      var select = wrap.querySelector('.doctors-filter-select');
      var trigger = wrap.querySelector('.custom-select-trigger');
      var dropdown = wrap.querySelector('.custom-select-dropdown');
      if (!select || !trigger || !dropdown) return;

      function buildOptions() {
        dropdown.innerHTML = '';
        var options = select.options;
        for (var i = 0; i < options.length; i++) {
          var opt = options[i];
          var div = document.createElement('div');
          div.className = 'custom-select-option' + (opt.value === select.value ? ' is-selected' : '');
          div.setAttribute('role', 'option');
          div.dataset.value = opt.value;
          div.textContent = opt.textContent;
          dropdown.appendChild(div);
        }
      }

      function updateTriggerText() {
        var selected = select.options[select.selectedIndex];
        trigger.textContent = selected ? selected.textContent : '';
        trigger.setAttribute('aria-expanded', wrap.classList.contains('is-open'));
      }

      function closeAll() {
        section.querySelectorAll('.custom-select-wrap.is-open').forEach(function (w) {
          w.classList.remove('is-open');
          var t = w.querySelector('.custom-select-trigger');
          if (t) t.setAttribute('aria-expanded', 'false');
        });
      }

      trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = wrap.classList.contains('is-open');
        closeAll();
        if (!isOpen) {
          buildOptions();
          dropdown.querySelectorAll('.custom-select-option').forEach(function (el) {
            el.classList.toggle('is-selected', el.dataset.value === select.value);
          });
          wrap.classList.add('is-open');
          trigger.setAttribute('aria-expanded', 'true');
        }
      });

      dropdown.addEventListener('click', function (e) {
        var option = e.target.closest('.custom-select-option');
        if (!option) return;
        select.value = option.dataset.value || '';
        dropdown.querySelectorAll('.custom-select-option').forEach(function (el) {
          el.classList.toggle('is-selected', el.dataset.value === select.value);
        });
        updateTriggerText();
        wrap.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        select.dispatchEvent(new Event('change', { bubbles: true }));
      });

      buildOptions();
      updateTriggerText();
    });

    document.addEventListener('click', function (e) {
      section.querySelectorAll('.custom-select-wrap.is-open').forEach(function (wrap) {
        if (!wrap.contains(e.target)) {
          wrap.classList.remove('is-open');
          var t = wrap.querySelector('.custom-select-trigger');
          if (t) t.setAttribute('aria-expanded', 'false');
        }
      });
    });
  }

  function scrollToContentArea(el) {
    if (!el) return;
    var header = document.querySelector('header');
    var offset = header ? header.offsetHeight : 80;
    var top = el.getBoundingClientRect().top + window.pageYOffset;
    window.scrollTo({ top: top - offset, behavior: 'smooth' });
  }

  function createPaginationAndShowMore(container, state, filteredCards, filters, setState) {
    var totalFiltered = filteredCards.length;
    var totalPages = Math.ceil(totalFiltered / CARDS_PER_PAGE);
    var wrap = document.createElement('div');
    wrap.className = 'doctors-pagination-wrap';

    var showMoreBtn = document.createElement('button');
    showMoreBtn.type = 'button';
    showMoreBtn.className = 'doctors-show-more';
    showMoreBtn.textContent = 'Показать ещё';
    wrap.appendChild(showMoreBtn);

    var pagination = document.createElement('nav');
    pagination.className = 'doctors-pagination';
    pagination.setAttribute('aria-label', 'Пагинация списка врачей');
    wrap.appendChild(pagination);

    function refresh() {
      var f = getFilteredCards(container, filters);
      updateCardsVisibility(container, f, state);
      updatePaginationActive(pagination, state);
      updateShowMoreButton(showMoreBtn, f.length, state);
    }

    showMoreBtn.addEventListener('click', function () {
      var f = getFilteredCards(container, filters);
      var totalInCategory = f.length;
      var nextCount = Math.min(totalInCategory, state.visibleCount + CARDS_PER_PAGE);
      setState({ visibleCount: nextCount, viewMode: 'showMore' });
      refresh();
    });

    renderPagination(pagination, totalPages, state, function (pageNum) {
      setState({ currentPage: pageNum, viewMode: 'page' });
      refresh();
      scrollToContentArea(container.closest('.doctors-section'));
    });
    updateShowMoreButton(showMoreBtn, totalFiltered, state);
    return { wrap: wrap, pagination: pagination, showMoreBtn: showMoreBtn, refresh: refresh };
  }

  function init() {
    var section = document.querySelector('.doctors-section .container');
    if (!section) return;
    var container = section.querySelector('.doctors-container');
    if (!container) return;

    initDoctorsScrollAnimations(container);
    initCustomSelects(section);

    var specSelect = document.getElementById('filter-specialization');
    var catSelect = document.getElementById('filter-category');
    var ageSelect = document.getElementById('filter-age');
    var searchInput = section.querySelector('.doctors-search-input');

    var filters = { search: '', specialization: '', category: '', patientAge: '' };
    var state = { currentPage: 1, viewMode: 'page', visibleCount: CARDS_PER_PAGE };

    function setState(partial) {
      if (partial.currentPage !== undefined) state.currentPage = partial.currentPage;
      if (partial.viewMode !== undefined) state.viewMode = partial.viewMode;
      if (partial.visibleCount !== undefined) state.visibleCount = partial.visibleCount;
    }

    var emptyMsg = document.createElement('div');
    emptyMsg.className = 'doctors-empty-message';
    emptyMsg.innerHTML = '<i class="fas fa-user-doctor"></i><p>Врачи по заданным критериям не найдены</p><span>Попробуйте изменить параметры поиска или сбросить фильтры</span>';
    emptyMsg.style.display = 'none';
    container.parentNode.insertBefore(emptyMsg, container.nextSibling);

    var filteredCards = getFilteredCards(container, filters);
    updateCardsVisibility(container, filteredCards, state);

    var paginationBlock = createPaginationAndShowMore(container, state, filteredCards, filters, setState);
    container.parentNode.appendChild(paginationBlock.wrap);

    function updateEmptyState(count) {
      var isEmpty = count === 0;
      emptyMsg.style.display = isEmpty ? '' : 'none';
      container.style.display = isEmpty ? 'none' : '';
      paginationBlock.wrap.style.display = isEmpty ? 'none' : '';
    }

    updateEmptyState(filteredCards.length);

    function applyFilters() {
      filters.search = searchInput ? searchInput.value : '';
      filters.specialization = specSelect ? specSelect.value : '';
      filters.category = catSelect ? catSelect.value : '';
      filters.patientAge = ageSelect ? ageSelect.value : '';
      setState({ currentPage: 1, viewMode: 'page', visibleCount: CARDS_PER_PAGE });
      var f = getFilteredCards(container, filters);
      var totalPages = Math.ceil(f.length / CARDS_PER_PAGE);
      renderPagination(paginationBlock.pagination, totalPages, state, function (pageNum) {
        setState({ currentPage: pageNum, viewMode: 'page' });
        paginationBlock.refresh();
        scrollToContentArea(container.closest('.doctors-section'));
      });
      updateCardsVisibility(container, f, state);
      updatePaginationActive(paginationBlock.pagination, state);
      updateShowMoreButton(paginationBlock.showMoreBtn, f.length, state);
      updateEmptyState(f.length);
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (specSelect) specSelect.addEventListener('change', applyFilters);
    if (catSelect) catSelect.addEventListener('change', applyFilters);
    if (ageSelect) ageSelect.addEventListener('change', applyFilters);

    var resetBtn = section.querySelector('.doctors-reset-btn');
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        if (searchInput) searchInput.value = '';
        [specSelect, catSelect, ageSelect].forEach(function (sel) {
          if (!sel) return;
          sel.value = '';
          sel.dispatchEvent(new Event('change', { bubbles: true }));
        });
        section.querySelectorAll('.custom-select-wrap').forEach(function (wrap) {
          var sel = wrap.querySelector('.doctors-filter-select');
          var trigger = wrap.querySelector('.custom-select-trigger');
          if (sel && trigger) {
            var first = sel.options[0];
            trigger.textContent = first ? first.textContent : '';
          }
        });
        applyFilters();
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
