/**
 * Header & Footer Loader
 * Динамически загружает общий хедер и футер на все страницы.
 * Автоматически помечает активную ссылку навигации по текущей странице.
 */
(function () {
  // Базовый путь к папке с проектом
  // Определяем относительный путь от текущей страницы до корня проекта
  function getBasePath() {
    // Все HTML-файлы находятся в корне проекта, поэтому базовый путь — пустой
    return '';
  }

  function loadFragment(placeholderId, filePath, callback) {
    var placeholder = document.getElementById(placeholderId);
    if (!placeholder) return;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', filePath, true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        if (xhr.status === 200 || xhr.status === 0) {
          placeholder.outerHTML = xhr.responseText;
          if (typeof callback === 'function') callback();
        }
      }
    };
    xhr.send();
  }

  function getCurrentPage() {
    return window.location.pathname.split('/').pop() || 'index.html';
  }

  function getCurrentHash() {
    return (window.location.hash || '').replace(/^#/, '');
  }

  function parseLinkTarget(href) {
    var parts = (href || '').split('#');
    return {
      page: (parts[0] || '').split('/').pop(),
      hash: parts[1] || ''
    };
  }

  function setActiveNavLink() {
    var currentPage = getCurrentPage();
    var currentHash = getCurrentHash();
    var relatedNavPages = {
      'medical-services-page.html': ['medical-services-page.html', 'one-medical-service-page.html'],
      'about-clinic-page.html': ['about-clinic-page.html', 'documents-page.html', 'vacancies-page.html', 'insurance-page.html', 'medical-device-page.html'],
      'our-doctors-page.html': ['our-doctors-page.html', 'doctor-page.html'],
      'promotions-page.html': ['promotions-page.html', 'one-promotion-page.html'],
      'blog.html': ['blog.html', 'blog-post.html']
    };

    document.querySelectorAll('.nav-menu a.active').forEach(function (link) {
      link.classList.remove('active');
    });

    document.querySelectorAll('.nav-item--has-submenu.is-active').forEach(function (item) {
      item.classList.remove('is-active');
    });

    var navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(function (link) {
      var target = parseLinkTarget(link.getAttribute('href'));
      var hrefPage = target.page;
      var hrefHash = target.hash;
      var isSubmenuLink = link.classList.contains('nav-submenu-link');
      var isActive = false;

      if (isSubmenuLink) {
        isActive = hrefPage === currentPage && (!hrefHash || hrefHash === currentHash);
      } else {
        isActive = hrefPage === currentPage;

        if (!isActive && relatedNavPages[hrefPage]) {
          isActive = relatedNavPages[hrefPage].indexOf(currentPage) !== -1;
        }
      }

      if (isSubmenuLink && hrefHash && !currentHash) {
        isActive = false;
      }

      if (isActive) {
        link.classList.add('active');
        var navItem = link.closest('.nav-item--has-submenu');
        if (navItem) {
          navItem.classList.add('is-active');
        }
      } else {
        link.classList.remove('active');
      }
    });

    syncActiveMobileSubmenu();
  }

  function closeAllSubmenus(exceptItem) {
    document.querySelectorAll('.nav-item--has-submenu.is-open').forEach(function (item) {
      if (item === exceptItem) return;
      item.classList.remove('is-open');
      var toggle = item.querySelector('.nav-submenu-toggle');
      if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function syncSubmenuHeight(item) {
    if (!item) return;
    var submenu = item.querySelector('.nav-submenu');
    if (!submenu) return;
    submenu.style.setProperty('--submenu-open-height', submenu.scrollHeight + 'px');
  }

  function syncAllMobileSubmenuHeights() {
    var navContainer = document.querySelector('.nav-container');
    if (!navContainer) return;

    navContainer.querySelectorAll('.nav-item--has-submenu').forEach(function (item) {
      syncSubmenuHeight(item);
    });
  }

  function syncActiveMobileSubmenu() {
    var navContainer = document.querySelector('.nav-container');
    if (!navContainer || !navContainer.classList.contains('show-mobile')) return;

    var activeSubmenuLink = navContainer.querySelector('.nav-submenu-link.active');
    if (!activeSubmenuLink) return;

    var activeItem = activeSubmenuLink.closest('.nav-item--has-submenu');
    if (!activeItem) return;

    closeAllSubmenus(activeItem);
    syncSubmenuHeight(activeItem);
    activeItem.classList.add('is-open');

    var toggle = activeItem.querySelector('.nav-submenu-toggle');
    if (toggle) {
      toggle.setAttribute('aria-expanded', 'true');
    }
  }

  function initNavSubmenus() {
    document.addEventListener('click', function (e) {
      var toggle = e.target.closest('.nav-submenu-toggle');
      var navContainer = document.querySelector('.nav-container');
      var isMobile = !!(navContainer && navContainer.classList.contains('show-mobile'));

      if (toggle) {
        var item = toggle.closest('.nav-item--has-submenu');
        if (!item) return;
        e.preventDefault();
        e.stopPropagation();

        if (!isMobile) return;

        syncSubmenuHeight(item);
        var isOpen = item.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        closeAllSubmenus(isOpen ? item : null);
        return;
      }

      if (!e.target.closest('.nav-item--has-submenu')) {
        closeAllSubmenus(null);
      }
    }, true);
  }

  // Закрытие с анимацией только fade (пункты не двигаются вверх)
  function closeMobileMenu(navContainer, mobileBtn) {
    if (!navContainer || !navContainer.classList.contains('show-mobile')) return;
    closeAllSubmenus(null);
    navContainer.classList.add('menu-closing');
    if (mobileBtn) {
      mobileBtn.classList.remove('active');
      mobileBtn.setAttribute('aria-expanded', 'false');
      mobileBtn.setAttribute('aria-label', 'Открыть меню');
    }
    navContainer.addEventListener('transitionend', function onCloseEnd(e) {
      if (e.propertyName !== 'opacity') return;
      navContainer.removeEventListener('transitionend', onCloseEnd);
      navContainer.classList.remove('show-mobile', 'menu-closing');
    }, { once: true });
  }

  // Делегирование на document: бургер работает при появлении в DOM (асинхронный хедер).
  // Capture + stopPropagation — один обработчик, без двойного toggle с script.js.
  function initMobileMenu() {
    document.addEventListener('click', function (e) {
      var mobileBtn = e.target.closest('.mobile-menu-btn');
      var navContainer = document.querySelector('.nav-container');

      if (mobileBtn) {
        e.preventDefault();
        e.stopPropagation();
        if (navContainer) {
          if (navContainer.classList.contains('show-mobile')) {
            closeMobileMenu(navContainer, mobileBtn);
          } else {
            navContainer.classList.remove('menu-closing');
            navContainer.classList.add('show-mobile');
            syncAllMobileSubmenuHeights();
            closeAllSubmenus(null);
            syncActiveMobileSubmenu();
            mobileBtn.classList.add('active');
            mobileBtn.setAttribute('aria-expanded', 'true');
            mobileBtn.setAttribute('aria-label', 'Закрыть меню');
          }
        }
        return;
      }

      // Закрываем меню при клике вне кнопки и вне меню
      if (navContainer && navContainer.classList.contains('show-mobile')) {
        var navLink = e.target.closest('.nav-submenu-link, .nav-link-row > a, .nav-menu > li:not(.nav-item--has-submenu) > a');
        if (navLink && !e.target.closest('.nav-submenu-toggle')) {
          var btnForLink = document.querySelector('.mobile-menu-btn');
          closeMobileMenu(navContainer, btnForLink);
          return;
        }

        if (!e.target.closest('.nav-container') && !e.target.closest('.mobile-menu-btn')) {
          var btn = document.querySelector('.mobile-menu-btn');
          closeMobileMenu(navContainer, btn);
        }
      }
    }, true);
  }

  function initSearchBtn() {
    var searchBtn = document.querySelector('.search-btn');
    var searchInput = document.querySelector('.search-input');
    if (!searchBtn || !searchInput) return;

    searchBtn.addEventListener('click', function () {
      var query = searchInput.value.trim();
      if (query) {
        window.location.href = 'search-page.html?q=' + encodeURIComponent(query);
      }
    });

    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        var query = searchInput.value.trim();
        if (query) {
          window.location.href = 'search-page.html?q=' + encodeURIComponent(query);
        }
      }
    });
  }

  function afterHeaderLoaded() {
    setActiveNavLink();
    window.updateHeaderActiveNav = setActiveNavLink;
    window.addEventListener('hashchange', setActiveNavLink);
    window.addEventListener('popstate', setActiveNavLink);
    window.addEventListener('resize', syncAllMobileSubmenuHeights);
    initNavSubmenus();
    initSearchBtn();
    if (typeof window.initSiteHeader === 'function') {
      window.initSiteHeader();
    }
    document.dispatchEvent(new CustomEvent('site:header-loaded'));
  }

  var base = getBasePath();

  // Бургер: один обработчик на document (capture), работает при любой загрузке хедера
  initMobileMenu();

  // Загружаем хедер
  loadFragment('header-placeholder', base + 'header.html', afterHeaderLoaded);

  // Загружаем футер
  loadFragment('footer-placeholder', base + 'footer.html', null);
})();
