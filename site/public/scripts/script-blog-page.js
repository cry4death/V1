(function () {
  'use strict';

  var CARDS_PER_PAGE = 8;

  var MONTHS_RU = {
    'января': 0, 'февраля': 1, 'марта': 2, 'апреля': 3, 'мая': 4, 'июня': 5,
    'июля': 6, 'августа': 7, 'сентября': 8, 'октября': 9, 'ноября': 10, 'декабря': 11
  };

  function parseDate(dateStr) {
    if (!dateStr) return new Date(0);
    var parts = dateStr.trim().split(/\s+/);
    if (parts.length !== 3) return new Date(0);
    var day = parseInt(parts[0], 10);
    var month = MONTHS_RU[parts[1]];
    var year = parseInt(parts[2], 10);
    if (isNaN(day) || month === undefined || isNaN(year)) return new Date(0);
    return new Date(year, month, day);
  }

  function getCardCategory(card) {
    var badge = card.querySelector('.blog-badge');
    return badge ? badge.textContent.trim() : '';
  }

  function getCardDate(card) {
    var dateEl = card.querySelector('.blog-date');
    return dateEl ? dateEl.textContent.trim() : '';
  }

  function initBlogScrollAnimations(container) {
    var cards = container.querySelectorAll('.blog-card.animate-on-scroll');
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

  function sortCardsByDate(container) {
    var cards = [].slice.call(container.querySelectorAll('.blog-card'));
    cards.sort(function (a, b) {
      var dateA = parseDate(getCardDate(a));
      var dateB = parseDate(getCardDate(b));
      return dateB - dateA;
    });
    cards.forEach(function (card) { container.appendChild(card); });
  }

  function getUniqueCategories(container) {
    var badges = container.querySelectorAll('.blog-badge');
    var set = {};
    badges.forEach(function (b) {
      var cat = b.textContent.trim();
      if (cat) set[cat] = true;
    });
    return Object.keys(set).sort();
  }

  function createSelect(categories) {
    var DEFAULT_LABEL = 'Все статьи';
    var currentValue = '';
    var changeCallbacks = [];

    var outerWrap = document.createElement('div');
    outerWrap.className = 'filter-select-wrap';

    var innerWrap = document.createElement('div');
    innerWrap.className = 'custom-select-wrap';

    var nativeSelect = document.createElement('select');
    nativeSelect.className = 'custom-hidden-select';
    nativeSelect.setAttribute('aria-label', 'Выбор категории статей');

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

  function createTabs(categories) {
    var wrap = document.createElement('div');
    wrap.className = 'blog-tabs';
    wrap.setAttribute('role', 'tablist');

    var allBtn = document.createElement('button');
    allBtn.type = 'button';
    allBtn.className = 'blog-tab active';
    allBtn.setAttribute('role', 'tab');
    allBtn.setAttribute('aria-selected', 'true');
    allBtn.textContent = 'Все статьи';
    allBtn.dataset.category = '';
    wrap.appendChild(allBtn);

    categories.forEach(function (cat) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'blog-tab';
      btn.setAttribute('role', 'tab');
      btn.setAttribute('aria-selected', 'false');
      btn.textContent = cat;
      btn.dataset.category = cat;
      wrap.appendChild(btn);
    });

    return wrap;
  }

  function setActiveTab(tabsWrap, category) {
    var tabs = tabsWrap.querySelectorAll('.blog-tab');
    tabs.forEach(function (t) {
      var isActive = (t.dataset.category || '') === (category || '');
      t.classList.toggle('active', isActive);
      t.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });
  }

  function getFilteredCards(container, category) {
    var cards = [].slice.call(container.querySelectorAll('.blog-card'));
    if (!category) return cards;
    return cards.filter(function (card) {
      return getCardCategory(card) === category;
    });
  }

  function updateCardsVisibility(container, filteredCards, state) {
    var start = (state.currentPage - 1) * CARDS_PER_PAGE;
    var endPage = state.currentPage * CARDS_PER_PAGE;
    var endShowMore = state.visibleCount;
    var idxInFiltered;
    var show;

    container.querySelectorAll('.blog-card').forEach(function (cardEl) {
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
        span.className = 'blog-pagination-ellipsis';
        span.textContent = '…';
        span.setAttribute('aria-hidden', 'true');
        paginationWrap.appendChild(span);
        return;
      }
      var pageNum = item;
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'blog-pagination-btn' + (pageNum === activePage ? ' active' : '');
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
    paginationWrap.querySelectorAll('.blog-pagination-btn').forEach(function (btn) {
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
    wrap.className = 'blog-pagination-wrap';

    var showMoreBtn = document.createElement('button');
    showMoreBtn.type = 'button';
    showMoreBtn.className = 'blog-show-more';
    showMoreBtn.textContent = 'Показать ещё';
    showMoreBtn.addEventListener('click', function () {
      var newFiltered = getFilteredCards(container, state.currentCategory);
      var totalInCategory = newFiltered.length;
      var nextCount = Math.min(totalInCategory, state.visibleCount + CARDS_PER_PAGE);
      setState({ visibleCount: nextCount, viewMode: 'showMore' });
      updateCardsVisibility(container, newFiltered, state);
      var totalPagesLocal = Math.ceil(totalInCategory / CARDS_PER_PAGE);
      renderPagination(pagination, totalPagesLocal, state, handlePageClick);
      updateShowMoreButton(showMoreBtn, totalInCategory, state);
    });
    wrap.appendChild(showMoreBtn);

    var pagination = document.createElement('nav');
    pagination.className = 'blog-pagination';
    pagination.setAttribute('aria-label', 'Пагинация блога');

    function handlePageClick(pageNum) {
      setState({ currentPage: pageNum, viewMode: 'page' });
      var newFiltered = getFilteredCards(container, state.currentCategory);
      var totalPagesLocal = Math.ceil(newFiltered.length / CARDS_PER_PAGE);
      updateCardsVisibility(container, newFiltered, state);
      renderPagination(pagination, totalPagesLocal, state, handlePageClick);
      updateShowMoreButton(showMoreBtn, newFiltered.length, state);
      scrollToContentArea(container.closest('.blog-section'));
    }

    renderPagination(pagination, totalPages, state, handlePageClick);
    wrap.appendChild(pagination);
    updateShowMoreButton(showMoreBtn, totalFiltered, state);
    return { wrap: wrap, pagination: pagination, showMoreBtn: showMoreBtn };
  }

  function init() {
    var section = document.querySelector('.blog-section .container');
    if (!section) return;

    var tabsWrapContainer = section.querySelector('.blog-tabs-wrap');
    var container = section.querySelector('.blog-container');
    if (!container) return;

    sortCardsByDate(container);
    initBlogScrollAnimations(container);

    var categories = getUniqueCategories(container);
    var tabsWrap = createTabs(categories);

    if (tabsWrapContainer) {
      tabsWrapContainer.appendChild(tabsWrap);
    } else {
      container.parentNode.insertBefore(tabsWrap, container);
    }

    var selectBlock = createSelect(categories);
    tabsWrap.parentNode.insertBefore(selectBlock.wrap, tabsWrap.nextSibling);

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

    var paginationShowMore = createPaginationAndShowMore(container, state, filteredCards, setState);
    container.parentNode.appendChild(paginationShowMore.wrap);

    var paginationEl = paginationShowMore.pagination;
    var showMoreBtn = paginationShowMore.showMoreBtn;

    function handlePageClickFromTabs(pageNum) {
      setState({ currentPage: pageNum, viewMode: 'page' });
      var f = getFilteredCards(container, state.currentCategory);
      var totalPages = Math.ceil(f.length / CARDS_PER_PAGE);
      updateCardsVisibility(container, f, state);
      renderPagination(paginationEl, totalPages, state, handlePageClickFromTabs);
      updateShowMoreButton(showMoreBtn, f.length, state);
      scrollToContentArea(container.closest('.blog-section'));
    }

    function applyCategory(category) {
      setActiveTab(tabsWrap, category);
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

    tabsWrap.addEventListener('click', function (e) {
      var tab = e.target.closest('.blog-tab');
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
