(function () {
  'use strict';

  function syncCategoryHash(id) {
    var currentPage = window.location.pathname.split('/').pop() || 'index.html';
    var nextHash = id ? '#' + id : '';

    if (currentPage !== 'medical-services-page.html') return;

    if (window.location.hash !== nextHash) {
      if (window.history && window.history.replaceState) {
        window.history.replaceState(null, '', window.location.pathname + window.location.search + nextHash);
      } else {
        window.location.hash = id;
      }
    }

    if (typeof window.updateHeaderActiveNav === 'function') {
      window.updateHeaderActiveNav();
    }
  }

  function switchCategory(id, options) {
    options = options || {};

    document.querySelectorAll('.services-nav-item').forEach(function (item) {
      var isActive = item.dataset.categoryId === id;
      item.classList.toggle('active', isActive);
      item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });

    document.querySelectorAll('.services-tab').forEach(function (tab) {
      var isActive = tab.dataset.categoryId === id;
      tab.classList.toggle('active', isActive);
      tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    document.querySelectorAll('.services-panel').forEach(function (panel) {
      panel.classList.remove('panel-animate');
      var isVisible = panel.dataset.categoryId === id;
      panel.hidden = !isVisible;
      if (isVisible) {
        void panel.offsetWidth;
        panel.classList.add('panel-animate');
      }
    });

    if (options.persistHash) {
      syncCategoryHash(id);
    }
  }

  function scrollToContentArea(el) {
    if (!el) return;
    var header = document.querySelector('header');
    var offset = header ? header.offsetHeight : 80;
    var top = el.getBoundingClientRect().top + window.pageYOffset;
    window.scrollTo({ top: top - offset, behavior: 'smooth' });
  }

  function getPanelScrollTarget(categoryId) {
    var panel = document.querySelector('.services-panel[data-category-id="' + categoryId + '"]');
    if (!panel) return document.querySelector('.services-content');
    var hero = panel.querySelector('.category-detail-hero');
    return hero || panel;
  }

  function bindEvents() {
    var navEl = document.querySelector('.services-nav');
    var tabsWrap = document.querySelector('.services-tabs-wrap');
    var contentEl = document.querySelector('.services-content');

    if (navEl) {
      navEl.addEventListener('click', function (e) {
        var item = e.target.closest('.services-nav-item');
        if (!item) return;
        switchCategory(item.dataset.categoryId, { persistHash: true });
        scrollToContentArea(contentEl);
      });
    }

    if (tabsWrap) {
      tabsWrap.addEventListener('click', function (e) {
        var tab = e.target.closest('.services-tab');
        if (!tab) return;
        switchCategory(tab.dataset.categoryId, { persistHash: true });
        scrollToContentArea(getPanelScrollTarget(tab.dataset.categoryId));
      });
    }

    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.category-more-btn');
      if (!btn) return;
      var list = btn.previousElementSibling;
      if (!list) return;
      var hidden = list.querySelectorAll('.category-service-row-hidden');
      if (hidden.length) {
        var toShow = Math.min(5, hidden.length);
        for (var i = 0; i < toShow; i++) {
          hidden[i].classList.remove('category-service-row-hidden');
        }
        if (list.querySelectorAll('.category-service-row-hidden').length === 0) {
          btn.hidden = true;
        }
        return;
      }
      var hiddenPromos = list.querySelectorAll('.category-promo-card-hidden');
      if (hiddenPromos.length) {
        hiddenPromos.forEach(function (el) { el.classList.remove('category-promo-card-hidden'); });
        btn.hidden = true;
        return;
      }
      var hiddenDoctors = list.querySelectorAll('.category-doctor-card-hidden');
      if (hiddenDoctors.length) {
        hiddenDoctors.forEach(function (el) { el.classList.remove('category-doctor-card-hidden'); });
        btn.hidden = true;
        return;
      }
    });
  }

  function initShowMoreForGrids() {
    var PROMO_LIMIT = 3;
    var DOCTOR_LIMIT = 3;

    document.querySelectorAll('.category-detail-promos').forEach(function (block) {
      var cards = block.querySelectorAll('.promo-card');
      if (cards.length === 0) {
        block.hidden = true;
      }
    });

    document.querySelectorAll('.category-promos-grid').forEach(function (grid) {
      var cards = grid.querySelectorAll('.promo-card');
      if (cards.length <= PROMO_LIMIT) return;
      for (var i = PROMO_LIMIT; i < cards.length; i++) {
        cards[i].classList.add('category-promo-card-hidden');
      }
      var btn = grid.parentElement.querySelector('.category-promos-more-btn');
      if (btn) btn.hidden = false;
    });

    document.querySelectorAll('.category-doctors-grid').forEach(function (grid) {
      var cards = grid.querySelectorAll('.doctor-card');
      if (cards.length <= DOCTOR_LIMIT) return;
      for (var i = DOCTOR_LIMIT; i < cards.length; i++) {
        cards[i].classList.add('category-doctor-card-hidden');
      }
      var btn = grid.parentElement.querySelector('.category-doctors-more-btn');
      if (btn) btn.hidden = false;
    });
  }

  function loadPromosFromPage() {
    fetch('promotions-page.html')
      .then(function (res) { return res.text(); })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var allCards = doc.querySelectorAll('.promo-card[data-category]');

        var grouped = {};
        allCards.forEach(function (card) {
          var cat = card.dataset.category.trim();
          if (!grouped[cat]) grouped[cat] = [];
          grouped[cat].push(card);
        });

        document.querySelectorAll('.services-panel').forEach(function (panel) {
          var titleEl = panel.querySelector('.services-category-title') ||
                        panel.querySelector('.category-detail-hero-title');
          if (!titleEl) return;
          var categoryName = titleEl.textContent.trim();
          var cards = grouped[categoryName];
          if (!cards || cards.length === 0) return;

          var block = document.createElement('div');
          block.className = 'category-detail-promos';

          var heading = document.createElement('h3');
          heading.className = 'category-detail-section-title';
          heading.textContent = 'Акции по направлению';
          block.appendChild(heading);

          var grid = document.createElement('div');
          grid.className = 'category-promos-grid';
          cards.forEach(function (card) {
            var clone = card.cloneNode(true);
            clone.classList.remove('animate-on-scroll');
            grid.appendChild(clone);
          });
          block.appendChild(grid);

          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'category-more-btn category-promos-more-btn';
          btn.hidden = true;
          btn.textContent = 'Показать ещё';
          block.appendChild(btn);

          var ref = panel.querySelector('.category-detail-services') ||
                    panel.querySelector('.category-services-list');
          if (ref) {
            ref.parentNode.insertBefore(block, ref);
          }
        });

        initShowMoreForGrids();
      })
      .catch(function (err) {
        console.warn('Failed to load promotions:', err);
      });
  }

  var CATEGORY_FAQ_DATA = {
    endokrinologiya: [
      { q: 'Как часто нужно посещать эндокринолога?', a: 'При отсутствии жалоб рекомендуется профилактический осмотр раз в год. Пациентам с хроническими заболеваниями (сахарный диабет, патологии щитовидной железы) — каждые 3–6 месяцев по назначению врача.' },
      { q: 'Нужно ли сдавать анализы перед приёмом эндокринолога?', a: 'Желательно иметь свежие результаты общего анализа крови и уровня глюкозы. Врач может назначить дополнительные исследования (гормоны щитовидной железы, УЗИ) после осмотра.' },
      { q: 'Какие симптомы указывают на необходимость визита к эндокринологу?', a: 'Резкое изменение веса, повышенная утомляемость, жажда и частое мочеиспускание, нарушения менструального цикла, выпадение волос, увеличение щитовидной железы — всё это поводы для консультации.' }
    ],
    ginekologiya: [
      { q: 'Как часто нужно посещать гинеколога?', a: 'Рекомендуется проходить осмотр у гинеколога не реже одного раза в год, даже при отсутствии жалоб. При наличии хронических заболеваний визиты могут быть чаще.' },
      { q: 'Нужна ли специальная подготовка к приёму гинеколога?', a: 'Перед приёмом рекомендуется провести гигиенические процедуры. Не рекомендуется использовать вагинальные свечи и спринцевание за 1–2 дня до визита. Оптимальное время для осмотра — 5–10 день цикла.' },
      { q: 'Какие анализы может назначить гинеколог?', a: 'Мазок на флору, цитологическое исследование (ПАП-тест), анализы на ИППП, гормональный профиль, УЗИ органов малого таза и другие — в зависимости от показаний.' }
    ],
    'lazernaya-ginekologiya': [
      { q: 'Болезненны ли лазерные процедуры в гинекологии?', a: 'Большинство лазерных процедур проводятся с минимальным дискомфортом. При необходимости применяется местная анестезия. Восстановительный период обычно короткий.' },
      { q: 'Сколько процедур необходимо для достижения результата?', a: 'Количество сеансов зависит от конкретной проблемы и индивидуальных особенностей. В среднем курс составляет 1–3 процедуры с интервалом 4–6 недель.' },
      { q: 'Есть ли противопоказания к лазерным процедурам?', a: 'Да, к ним относятся: острые воспалительные процессы, онкологические заболевания, беременность. Окончательное решение о возможности проведения процедуры принимает врач после осмотра.' }
    ],
    proktologiya: [
      { q: 'Когда необходимо обратиться к проктологу?', a: 'При появлении боли, зуда, кровянистых выделений в области прямой кишки, нарушениях стула, выпадении геморроидальных узлов. Также рекомендован профилактический осмотр после 40 лет.' },
      { q: 'Как подготовиться к приёму проктолога?', a: 'Перед приёмом рекомендуется выполнить очистительную клизму или принять слабительное по рекомендации врача. Желательно ограничить приём пищи за 2–3 часа до визита.' },
      { q: 'Болезнен ли осмотр у проктолога?', a: 'Осмотр проводится максимально деликатно. При необходимости применяется обезболивание. Современное оборудование позволяет минимизировать дискомфорт.' }
    ],
    hirurgiya: [
      { q: 'Какие операции проводятся амбулаторно?', a: 'Многие малые хирургические вмешательства: удаление новообразований кожи, вскрытие абсцессов, удаление вросшего ногтя и другие. Пациент может уйти домой в день операции.' },
      { q: 'Нужно ли сдавать анализы перед операцией?', a: 'Да, перед любым хирургическим вмешательством необходимо пройти предоперационное обследование: анализы крови, ЭКГ, консультацию терапевта. Перечень определяет хирург.' },
      { q: 'Какой вид анестезии используется?', a: 'Вид анестезии зависит от объёма и характера операции: от местной до общей. Врач обсудит с вами оптимальный вариант на консультации.' }
    ],
    'lazernaya-hirurgiya': [
      { q: 'В чём преимущества лазерной хирургии?', a: 'Минимальная травматичность, отсутствие кровотечений, быстрое заживление, снижение риска инфицирования. Период восстановления значительно короче, чем при классической хирургии.' },
      { q: 'Какие заболевания можно лечить лазером?', a: 'Геморрой, варикозное расширение вен, новообразования кожи, вросший ноготь, анальные трещины и другие патологии. Врач определит показания на консультации.' },
      { q: 'Сколько длится восстановление после лазерной операции?', a: 'В зависимости от объёма вмешательства восстановление занимает от нескольких дней до 2 недель. Большинство пациентов возвращаются к обычной жизни уже на следующий день.' }
    ],
    uzi: [
      { q: 'Нужна ли подготовка к УЗИ?', a: 'Зависит от вида исследования. Для УЗИ брюшной полости необходимо голодание 6–8 часов. Для УЗИ мочевого пузыря — наполненный мочевой пузырь. Для других исследований подготовка обычно не требуется.' },
      { q: 'Безопасно ли УЗИ?', a: 'Да, ультразвуковая диагностика абсолютно безопасна. Исследование не использует ионизирующее излучение и может проводиться так часто, как это необходимо, включая беременных.' },
      { q: 'Сколько длится УЗИ-исследование?', a: 'Стандартное УЗИ длится 15–30 минут. Комплексное исследование может занять до 40–60 минут. Результаты выдаются сразу после процедуры.' }
    ],
    'funkcionalnaya-diagnostika': [
      { q: 'Что включает функциональная диагностика?', a: 'ЭКГ, холтеровское мониторирование, суточное мониторирование артериального давления (СМАД), спирометрия и другие методы оценки функционального состояния органов и систем.' },
      { q: 'Как подготовиться к ЭКГ?', a: 'Специальной подготовки не требуется. Рекомендуется избегать физических нагрузок и стресса перед исследованием, не употреблять крепкий кофе. Процедура занимает 5–10 минут.' },
      { q: 'Что такое холтеровское мониторирование?', a: 'Это запись ЭКГ в течение 24 часов с помощью портативного устройства. Позволяет выявить нарушения ритма, ишемию и другие изменения, которые не удаётся зафиксировать при обычной ЭКГ.' }
    ],
    terapiya: [
      { q: 'Когда стоит обратиться к терапевту?', a: 'При любых общих жалобах: повышение температуры, слабость, головная боль, боли в грудной клетке и животе. Терапевт проведёт первичную диагностику и направит к узкому специалисту при необходимости.' },
      { q: 'Какие обследования назначает терапевт?', a: 'Общие и биохимические анализы крови, общий анализ мочи, ЭКГ, флюорографию, УЗИ. Перечень зависит от жалоб и клинической картины.' }
    ],
    flebologiya: [
      { q: 'Какие симптомы варикоза требуют обращения к флебологу?', a: 'Тяжесть и отёки в ногах, видимые расширенные вены, сосудистые звёздочки, судороги в икроножных мышцах, изменение цвета кожи голеней — всё это повод для консультации.' },
      { q: 'Как проводится лечение варикоза?', a: 'Современные методы включают лазерную коагуляцию, склеротерапию и минифлебэктомию. Выбор метода зависит от стадии заболевания. Большинство процедур выполняются амбулаторно.' },
      { q: 'Нужна ли подготовка к УЗИ вен?', a: 'Специальная подготовка не требуется. Рекомендуется надеть удобную одежду, обеспечивающую лёгкий доступ к ногам. Исследование занимает около 20–30 минут.' }
    ],
    kardiologiya: [
      { q: 'Когда нужно обратиться к кардиологу?', a: 'При болях в области сердца, одышке, учащённом сердцебиении, повышенном артериальном давлении, головокружениях, обмороках. Профилактический осмотр рекомендован после 40 лет ежегодно.' },
      { q: 'Какие обследования проводит кардиолог?', a: 'ЭКГ, ЭхоКГ (УЗИ сердца), холтеровское мониторирование, СМАД, нагрузочные пробы, лабораторные исследования (липидный профиль, маркеры повреждения миокарда).' },
      { q: 'Можно ли предотвратить заболевания сердца?', a: 'Да, во многих случаях. Контроль артериального давления, здоровое питание, регулярная физическая активность, отказ от курения и регулярные осмотры кардиолога значительно снижают риски.' }
    ],
    gastroenterologiya: [
      { q: 'Какие симптомы указывают на проблемы с ЖКТ?', a: 'Боли в животе, изжога, тошнота, нарушения стула (запоры, диарея), вздутие, горечь во рту, снижение аппетита и потеря веса — поводы для обращения к гастроэнтерологу.' },
      { q: 'Нужно ли готовиться к приёму гастроэнтеролога?', a: 'Желательно прийти натощак или не менее чем через 3 часа после лёгкого приёма пищи. При наличии — возьмите результаты предыдущих обследований (УЗИ, ФГДС, анализы).' },
      { q: 'Как часто нужно обследовать ЖКТ?', a: 'При отсутствии жалоб — раз в 1–2 года. При наличии хронических заболеваний ЖКТ — по графику, который определит гастроэнтеролог, обычно каждые 6–12 месяцев.' }
    ],
    nevrologiya: [
      { q: 'Когда нужно обратиться к неврологу?', a: 'Головные боли, головокружения, боли в спине и шее, онемение конечностей, нарушения сна, тремор, снижение памяти — основные поводы для консультации невролога.' },
      { q: 'Какие методы диагностики использует невролог?', a: 'Неврологический осмотр, ЭЭГ, электронейромиография, МРТ головного мозга и позвоночника, допплерография сосудов. Набор исследований определяется индивидуально.' },
      { q: 'Можно ли лечить мигрень?', a: 'Да, современная неврология располагает эффективными методами лечения мигрени: медикаментозная терапия, ботулинотерапия, физиотерапия и коррекция образа жизни.' }
    ],
    urologiya: [
      { q: 'Когда мужчине нужно обратиться к урологу?', a: 'При нарушениях мочеиспускания, болях в пояснице и промежности, изменениях в моче, снижении потенции. Профилактический осмотр рекомендован мужчинам после 40 лет ежегодно.' },
      { q: 'Нужна ли подготовка к приёму уролога?', a: 'Специальной подготовки не требуется. Рекомендуется провести гигиенические процедуры. Если планируется УЗИ мочевого пузыря — прийти с наполненным мочевым пузырём.' },
      { q: 'Какие заболевания лечит уролог?', a: 'Мочекаменная болезнь, простатит, аденома простаты, инфекции мочевыводящих путей, цистит, пиелонефрит, эректильная дисфункция и другие заболевания мочеполовой системы.' }
    ],
    nefrologiya: [
      { q: 'Чем нефролог отличается от уролога?', a: 'Нефролог занимается терапевтическим лечением заболеваний почек (гломерулонефрит, хроническая болезнь почек, нефропатии), а уролог — хирургическим лечением мочеполовой системы.' },
      { q: 'Какие симптомы указывают на заболевания почек?', a: 'Отёки (особенно утренние), изменение цвета и количества мочи, боли в поясничной области, повышение артериального давления, слабость и утомляемость.' }
    ],
    onkologiya: [
      { q: 'Как часто нужно проходить онкоскрининг?', a: 'Рекомендуется ежегодный профилактический осмотр. Пациентам из группы риска (наследственная предрасположенность, возраст старше 50) — по индивидуальному графику, определённому врачом.' },
      { q: 'Какие обследования входят в онкоскрининг?', a: 'В зависимости от возраста и факторов риска: осмотр кожных покровов, УЗИ, маммография, анализы на онкомаркеры, цитологические исследования и другие методы.' },
      { q: 'Всегда ли новообразование — это рак?', a: 'Нет, большинство новообразований являются доброкачественными. Однако любое новообразование требует обследования для исключения злокачественного процесса. Ранняя диагностика значительно улучшает прогноз.' }
    ],
    mammologiya: [
      { q: 'Как часто нужно посещать маммолога?', a: 'Женщинам рекомендуется посещать маммолога ежегодно после 35 лет. Маммографию — раз в 2 года после 40 лет и ежегодно после 50. При наличии факторов риска — чаще.' },
      { q: 'Когда лучше делать УЗИ молочных желёз?', a: 'Оптимальное время — 5–12 день менструального цикла. В этот период ткань молочной железы наименее плотная, что обеспечивает наилучшую визуализацию.' },
      { q: 'Какие симптомы требуют обращения к маммологу?', a: 'Уплотнения в молочной железе, боль, выделения из сосков, изменение формы или размера груди, втяжение соска, изменение кожи — любой из этих симптомов является поводом для визита.' }
    ],
    dietologiya: [
      { q: 'Чем диетолог отличается от нутрициолога?', a: 'Диетолог — это врач с медицинским образованием, который может ставить диагнозы и назначать лечебное питание при заболеваниях. Нутрициолог занимается вопросами здорового питания без медицинских назначений.' },
      { q: 'Как проходит приём у диетолога?', a: 'Врач изучает вашу историю болезни, пищевые привычки, результаты анализов, проводит антропометрию. На основании этого составляет индивидуальный план питания с учётом ваших целей и состояния здоровья.' }
    ],
    psihologiya: [
      { q: 'Когда стоит обратиться к психологу?', a: 'При повышенной тревожности, проблемах в отношениях, трудностях с самооценкой, переживании стресса, потери или кризиса, трудностях адаптации — в любой ситуации, когда вы чувствуете, что не справляетесь сами.' },
      { q: 'Сколько сеансов обычно нужно?', a: 'Зависит от запроса. Для решения конкретной ситуации может быть достаточно 3–5 встреч. Длительная терапия (работа с глубинными паттернами) может занимать несколько месяцев.' },
      { q: 'Конфиденциальна ли работа с психологом?', a: 'Да, психолог обязан соблюдать конфиденциальность. Всё, что обсуждается на сеансе, остаётся между вами и специалистом, за исключением случаев, предусмотренных законодательством.' }
    ],
    psihoterapiya: [
      { q: 'Чем психотерапевт отличается от психолога?', a: 'Психотерапевт — это врач с медицинским образованием, который может назначать медикаментозное лечение в сочетании с психотерапией. Психолог работает только немедикаментозными методами.' },
      { q: 'При каких состояниях помогает психотерапия?', a: 'Депрессия, тревожные расстройства, панические атаки, фобии, обсессивно-компульсивное расстройство, расстройства пищевого поведения, посттравматическое стрессовое расстройство и другие.' },
      { q: 'Можно ли совмещать психотерапию с медикаментозным лечением?', a: 'Да, во многих случаях комбинированный подход даёт лучшие результаты. Психотерапевт подберёт оптимальное сочетание методов с учётом вашего состояния.' }
    ]
  };

  function injectCategoryFaq() {
    document.querySelectorAll('.services-panel').forEach(function (panel) {
      if (panel.querySelector('.category-faq-section')) return;

      var catId = panel.dataset.categoryId;
      var faqItems = CATEGORY_FAQ_DATA[catId];
      if (!faqItems || faqItems.length === 0) return;

      var section = document.createElement('div');
      section.className = 'category-faq-section';

      var title = document.createElement('h3');
      title.className = 'category-faq-title';
      title.textContent = 'Часто задаваемые вопросы';
      section.appendChild(title);

      var list = document.createElement('div');
      list.className = 'category-faq-list';

      faqItems.forEach(function (item) {
        var accItem = document.createElement('div');
        accItem.className = 'accordion-item';

        var header = document.createElement('button');
        header.className = 'accordion-header';
        header.type = 'button';
        header.setAttribute('aria-expanded', 'false');

        var h3 = document.createElement('h3');
        h3.textContent = item.q;
        header.appendChild(h3);

        var icon = document.createElement('span');
        icon.className = 'accordion-icon';
        icon.innerHTML = '<i class="fas fa-plus"></i>';
        header.appendChild(icon);

        accItem.appendChild(header);

        var content = document.createElement('div');
        content.className = 'accordion-content';
        var p = document.createElement('p');
        p.textContent = item.a;
        content.appendChild(p);

        accItem.appendChild(content);
        list.appendChild(accItem);
      });

      section.appendChild(list);
      var detailContainer = panel.querySelector('.services-category-detail');
      (detailContainer || panel).appendChild(section);
    });
  }

  function bindCategoryAccordion() {
    document.addEventListener('click', function (e) {
      var header = e.target.closest('.category-faq-list .accordion-header');
      if (!header) return;

      var item = header.closest('.accordion-item');
      var content = item.querySelector('.accordion-content');
      var isActive = item.classList.contains('active');

      var parent = header.closest('.category-faq-list');
      parent.querySelectorAll('.accordion-item.active').forEach(function (el) {
        if (el !== item) {
          el.classList.remove('active');
          el.querySelector('.accordion-header').setAttribute('aria-expanded', 'false');
          el.querySelector('.accordion-content').style.maxHeight = null;
        }
      });

      if (isActive) {
        item.classList.remove('active');
        header.setAttribute('aria-expanded', 'false');
        content.style.maxHeight = null;
      } else {
        item.classList.add('active');
        header.setAttribute('aria-expanded', 'true');
        content.style.maxHeight = content.scrollHeight + 'px';
      }
    });
  }

  function init() {
    var firstPanel = document.querySelector('.services-panel');
    var startId = firstPanel ? firstPanel.dataset.categoryId : '';

    function applyHashCategory() {
      var hash = location.hash.replace('#', '');

      if (hash && document.querySelector('.services-panel[data-category-id="' + hash + '"]')) {
        switchCategory(hash);
        return true;
      }

      return false;
    }

    var initialHash = location.hash.replace('#', '');
    if (!applyHashCategory() && startId) {
      switchCategory(startId);
    } else if (initialHash) {
      setTimeout(function () {
        scrollToContentArea(getPanelScrollTarget(initialHash));
      }, 150);
    }

    window.addEventListener('hashchange', function () {
      var hash = location.hash.replace('#', '');
      if (applyHashCategory()) {
        scrollToContentArea(getPanelScrollTarget(hash));
      }
    });

    bindEvents();
    initShowMoreForGrids();
    loadPromosFromPage();
    injectCategoryFaq();
    bindCategoryAccordion();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
