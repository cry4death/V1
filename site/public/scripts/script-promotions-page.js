(function () {
  'use strict';

  var CARDS_PER_PAGE = 8;

  function getCardCategory(card) {
    var badge = card.querySelector('.promo-badge');
    return badge ? badge.textContent.trim() : '';
  }

  function initScrollAnimations(container) {
    var cards = container.querySelectorAll('.promo-card.animate-on-scroll');
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

  function getUniqueCategories(container) {
    var badges = container.querySelectorAll('.promo-badge');
    var set = {};
    badges.forEach(function (b) {
      var cat = b.textContent.trim();
      if (cat) set[cat] = true;
    });
    return Object.keys(set).sort();
  }

  function createSelect(categories) {
    var DEFAULT_LABEL = 'Все акции';
    var currentValue = '';
    var changeCallbacks = [];

    var outerWrap = document.createElement('div');
    outerWrap.className = 'filter-select-wrap';

    var innerWrap = document.createElement('div');
    innerWrap.className = 'custom-select-wrap';

    var nativeSelect = document.createElement('select');
    nativeSelect.className = 'custom-hidden-select';
    nativeSelect.setAttribute('aria-label', 'Выбор категории акций');

    var allOpt = document.createElement('option');
    allOpt.value = '';
    allOpt.textContent = DEFAULT_LABEL;
    nativeSelect.appendChild(allOpt);
    categories.forEach(function (cat) {
      var opt = document.createElement('option');
      opt.value = cat;
      opt.textContent = cat;
      nativeSelect.appendChild(opt);
    });

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'custom-select-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.textContent = DEFAULT_LABEL;

    var dropdown = document.createElement('div');
    dropdown.className = 'custom-select-dropdown';
    dropdown.setAttribute('role', 'listbox');
    dropdown.setAttribute('aria-hidden', 'true');

    innerWrap.appendChild(nativeSelect);
    innerWrap.appendChild(trigger);
    innerWrap.appendChild(dropdown);
    outerWrap.appendChild(innerWrap);

    function buildOptions() {
      dropdown.innerHTML = '';
      for (var i = 0; i < nativeSelect.options.length; i++) {
        var opt = nativeSelect.options[i];
        var item = document.createElement('div');
        item.className = 'custom-select-option' + (opt.value === currentValue ? ' is-selected' : '');
        item.setAttribute('role', 'option');
        item.dataset.value = opt.value;
        item.textContent = opt.textContent;
        dropdown.appendChild(item);
      }
    }

    function updateTrigger() {
      var sel = nativeSelect.options[nativeSelect.selectedIndex];
      trigger.textContent = sel ? sel.textContent : DEFAULT_LABEL;
    }

    function close() {
      innerWrap.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
      dropdown.setAttribute('aria-hidden', 'true');
    }

    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      var wasOpen = innerWrap.classList.contains('is-open');
      document.querySelectorAll('.custom-select-wrap.is-open').forEach(function (w) {
        w.classList.remove('is-open');
        var t = w.querySelector('.custom-select-trigger');
        if (t) t.setAttribute('aria-expanded', 'false');
      });
      if (!wasOpen) {
        buildOptions();
        innerWrap.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        dropdown.setAttribute('aria-hidden', 'false');
      }
    });

    dropdown.addEventListener('click', function (e) {
      var option = e.target.closest('.custom-select-option');
      if (!option) return;
      currentValue = option.dataset.value || '';
      nativeSelect.value = currentValue;
      dropdown.querySelectorAll('.custom-select-option').forEach(function (el) {
        el.classList.toggle('is-selected', el.dataset.value === currentValue);
      });
      updateTrigger();
      close();
      changeCallbacks.forEach(function (cb) { cb(currentValue); });
    });

    document.addEventListener('click', function (e) {
      if (!innerWrap.contains(e.target)) close();
    });

    return {
      wrap: outerWrap,
      setValue: function (value) {
        currentValue = value;
        nativeSelect.value = value;
        updateTrigger();
        dropdown.querySelectorAll('.custom-select-option').forEach(function (el) {
          el.classList.toggle('is-selected', el.dataset.value === currentValue);
        });
      },
      onChange: function (cb) { changeCallbacks.push(cb); }
    };
  }

  function createTabs(tabsContainer, categories) {
    tabsContainer.setAttribute('role', 'tablist');
    tabsContainer.innerHTML = '';

    var allBtn = document.createElement('button');
    allBtn.type = 'button';
    allBtn.className = 'promo-tab active';
    allBtn.setAttribute('role', 'tab');
    allBtn.setAttribute('aria-selected', 'true');
    allBtn.textContent = 'Все акции';
    allBtn.dataset.category = '';
    tabsContainer.appendChild(allBtn);

    categories.forEach(function (cat) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'promo-tab';
      btn.setAttribute('role', 'tab');
      btn.setAttribute('aria-selected', 'false');
      btn.textContent = cat;
      btn.dataset.category = cat;
      tabsContainer.appendChild(btn);
    });
  }

  function setActiveTab(tabsWrap, category) {
    var tabs = tabsWrap.querySelectorAll('.promo-tab');
    tabs.forEach(function (t) {
      var isActive = (t.dataset.category || '') === (category || '');
      t.classList.toggle('active', isActive);
      t.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });
  }

  function getFilteredCards(container, category) {
    var cards = [].slice.call(container.querySelectorAll('.promo-card'));
    if (!category) return cards;
    return cards.filter(function (card) {
      return getCardCategory(card) === category;
    });
  }

  function updateCardsVisibility(container, filteredCards, state) {
    var start = (state.currentPage - 1) * CARDS_PER_PAGE;
    var endPage = state.currentPage * CARDS_PER_PAGE;
    var endShowMore = state.visibleCount;

    container.querySelectorAll('.promo-card').forEach(function (cardEl) {
      var idxInFiltered = filteredCards.indexOf(cardEl);
      if (idxInFiltered === -1) {
        cardEl.style.display = 'none';
        return;
      }
      var show = state.viewMode === 'page'
        ? idxInFiltered >= start && idxInFiltered < endPage
        : idxInFiltered < endShowMore;
      cardEl.style.display = show ? '' : 'none';
    });
  }

  function getPaginationItems(totalPages, activePage) {
    if (totalPages <= 5) {
      var all = [];
      for (var i = 1; i <= totalPages; i++) all.push(i);
      return all;
    }
    var items = [1];
    var start, end;
    if (activePage <= 2) { start = 2; end = 3; }
    else if (activePage === 3) { start = 2; end = 4; }
    else if (activePage >= totalPages - 1) { start = totalPages - 2; end = totalPages - 1; }
    else if (activePage === totalPages - 2) { start = totalPages - 3; end = totalPages - 1; }
    else { start = activePage - 1; end = activePage + 1; }
    if (start === 3) items.push(2);
    else if (start > 3) items.push('ellipsis-left');
    for (var p = start; p <= end; p++) {
      if (p > 1 && p < totalPages) items.push(p);
    }
    if (end === totalPages - 2) items.push(totalPages - 1);
    else if (end < totalPages - 2) items.push('ellipsis-right');
    items.push(totalPages);
    return items;
  }

  function renderPagination(paginationWrap, totalPages, state, onPageClick) {
    paginationWrap.innerHTML = '';
    if (totalPages <= 1) return;
    var activePage = state.viewMode === 'page'
      ? state.currentPage
      : Math.ceil(state.visibleCount / CARDS_PER_PAGE);
    var items = getPaginationItems(totalPages, activePage);
    items.forEach(function (item) {
      if (item === 'ellipsis-left' || item === 'ellipsis-right') {
        var span = document.createElement('span');
        span.className = 'promo-pagination-ellipsis';
        span.textContent = '…';
        span.setAttribute('aria-hidden', 'true');
        paginationWrap.appendChild(span);
        return;
      }
      var pageNum = item;
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'promo-pagination-btn' + (pageNum === activePage ? ' active' : '');
      btn.textContent = pageNum;
      btn.dataset.page = String(pageNum);
      btn.setAttribute('aria-label', 'Страница ' + pageNum);
      if (pageNum === activePage) btn.setAttribute('aria-current', 'page');
      (function (page) {
        btn.addEventListener('click', function () { onPageClick(page); });
      })(pageNum);
      paginationWrap.appendChild(btn);
    });
  }

  function updatePaginationActive(paginationWrap, state) {
    var activePage = state.viewMode === 'page'
      ? state.currentPage
      : Math.ceil(state.visibleCount / CARDS_PER_PAGE);
    paginationWrap.querySelectorAll('.promo-pagination-btn').forEach(function (btn) {
      var pageNum = parseInt(btn.dataset.page, 10);
      var isActive = pageNum === activePage;
      btn.classList.toggle('active', isActive);
      btn.setAttribute('aria-current', isActive ? 'page' : 'false');
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

  function scrollToContentArea(el) {
    if (!el) return;
    var header = document.querySelector('header');
    var offset = header ? header.offsetHeight : 80;
    var top = el.getBoundingClientRect().top + window.pageYOffset;
    window.scrollTo({ top: top - offset, behavior: 'smooth' });
  }

  function createPaginationAndShowMore(container, state, filteredCards, setState) {
    var totalFiltered = filteredCards.length;
    var totalPages = Math.ceil(totalFiltered / CARDS_PER_PAGE);
    var wrap = document.createElement('div');
    wrap.className = 'promo-pagination-wrap';

    var showMoreBtn = document.createElement('button');
    showMoreBtn.type = 'button';
    showMoreBtn.className = 'promo-show-more';
    showMoreBtn.textContent = 'Показать ещё';
    wrap.appendChild(showMoreBtn);

    var pagination = document.createElement('nav');
    pagination.className = 'promo-pagination';
    pagination.setAttribute('aria-label', 'Пагинация акций');

    function handlePageClick(pageNum) {
      setState({ currentPage: pageNum, viewMode: 'page' });
      var f = getFilteredCards(container, state.currentCategory);
      var totalPagesLocal = Math.ceil(f.length / CARDS_PER_PAGE);
      updateCardsVisibility(container, f, state);
      renderPagination(pagination, totalPagesLocal, state, handlePageClick);
      updateShowMoreButton(showMoreBtn, f.length, state);
      scrollToContentArea(container.closest('.promotions-section'));
    }

    showMoreBtn.addEventListener('click', function () {
      var f = getFilteredCards(container, state.currentCategory);
      var nextCount = Math.min(f.length, state.visibleCount + CARDS_PER_PAGE);
      setState({ visibleCount: nextCount, viewMode: 'showMore' });
      updateCardsVisibility(container, f, state);
      var totalPagesLocal = Math.ceil(f.length / CARDS_PER_PAGE);
      renderPagination(pagination, totalPagesLocal, state, handlePageClick);
      updateShowMoreButton(showMoreBtn, f.length, state);
    });

    renderPagination(pagination, totalPages, state, handlePageClick);
    wrap.appendChild(pagination);
    updateShowMoreButton(showMoreBtn, totalFiltered, state);
    return { wrap: wrap, pagination: pagination, showMoreBtn: showMoreBtn };
  }

  function init() {
    var section = document.querySelector('.promotions-section .container');
    if (!section) return;

    var tabsContainer = section.querySelector('.promotions-tabs');
    var container = section.querySelector('.promotions-container');
    if (!container || !tabsContainer) return;

    initScrollAnimations(container);

    var categories = getUniqueCategories(container);
    createTabs(tabsContainer, categories);

    var selectBlock = createSelect(categories);
    tabsContainer.parentNode.insertBefore(selectBlock.wrap, tabsContainer.nextSibling);

    var state = {
      currentCategory: '',
      currentPage: 1,
      viewMode: 'page',
      visibleCount: CARDS_PER_PAGE
    };

    function setState(partial) {
      if (partial.currentPage !== undefined) state.currentPage = partial.currentPage;
      if (partial.viewMode !== undefined) state.viewMode = partial.viewMode;
      if (partial.visibleCount !== undefined) state.visibleCount = partial.visibleCount;
      if (partial.currentCategory !== undefined) state.currentCategory = partial.currentCategory;
    }

    var filteredCards = getFilteredCards(container, state.currentCategory);
    updateCardsVisibility(container, filteredCards, state);

    var paginationBlock = createPaginationAndShowMore(container, state, filteredCards, setState);
    container.parentNode.appendChild(paginationBlock.wrap);

    var paginationEl = paginationBlock.pagination;
    var showMoreBtn = paginationBlock.showMoreBtn;

    function handlePageClickFromTabs(pageNum) {
      setState({ currentPage: pageNum, viewMode: 'page' });
      var f = getFilteredCards(container, state.currentCategory);
      updateCardsVisibility(container, f, state);
      renderPagination(paginationEl, Math.ceil(f.length / CARDS_PER_PAGE), state, handlePageClickFromTabs);
      updateShowMoreButton(showMoreBtn, f.length, state);
      scrollToContentArea(container.closest('.promotions-section'));
    }

    function applyCategory(category) {
      setActiveTab(tabsContainer, category);
      selectBlock.setValue(category);
      setState({
        currentCategory: category,
        currentPage: 1,
        viewMode: 'page',
        visibleCount: CARDS_PER_PAGE
      });
      var newFiltered = getFilteredCards(container, category);
      updateCardsVisibility(container, newFiltered, state);
      var totalPages = Math.ceil(newFiltered.length / CARDS_PER_PAGE);
      renderPagination(paginationEl, totalPages, state, handlePageClickFromTabs);
      updateShowMoreButton(showMoreBtn, newFiltered.length, state);
    }

    tabsContainer.addEventListener('click', function (e) {
      var tab = e.target.closest('.promo-tab');
      if (!tab) return;
      applyCategory(tab.dataset.category || '');
    });

    selectBlock.onChange(function (value) {
      applyCategory(value);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
