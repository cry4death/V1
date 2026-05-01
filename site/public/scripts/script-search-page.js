document.addEventListener('DOMContentLoaded', function () {
  initializeSearchPage();
});

function initializeSearchPage() {
  var params = new URLSearchParams(window.location.search);
  var query = params.get('q') || params.get('query') || '';
  var searchQueryInput = document.getElementById('search-query');
  var searchQueryDisplay = document.getElementById('search-query-display');

  if (searchQueryInput) {
    searchQueryInput.value = query;
  }

  if (searchQueryDisplay) {
    searchQueryDisplay.textContent = query || '—';
  }

  var searchForm = document.getElementById('page-search-form');
  if (searchForm) {
    searchForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var newQuery = searchQueryInput.value.trim();
      if (newQuery) {
        window.location.href = 'search-page.html?q=' + encodeURIComponent(newQuery);
      }
    });
  }

  initializeCategories();

  if (query) {
    executeSearch(query);
  } else {
    showPlaceholder();
  }
}

function initializeCategories() {
  var categoryTabs = document.querySelectorAll('.category-tab');

  categoryTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      categoryTabs.forEach(function (t) { t.classList.remove('active'); });
      this.classList.add('active');
      filterResultsByCategory(this.getAttribute('data-category'));
    });
  });
}

function filterResultsByCategory(category) {
  var resultItems = document.querySelectorAll('.result-item');
  var noResultsMessage = document.querySelector('.no-results-message');
  var visibleCount = 0;

  if (noResultsMessage) noResultsMessage.style.display = 'none';

  resultItems.forEach(function (item) {
    if (category === 'all' || item.getAttribute('data-type') === category) {
      item.style.display = 'block';
      visibleCount++;
    } else {
      item.style.display = 'none';
    }
  });

  if (visibleCount === 0 && noResultsMessage) {
    noResultsMessage.style.display = 'block';
  }

  updateResultsCount(visibleCount);
}

function updateResultsCount(count) {
  var el = document.getElementById('results-count');
  if (el) el.textContent = count || 0;
}

function showPlaceholder() {
  var placeholder = document.getElementById('search-placeholder');
  var noResults = document.querySelector('.no-results-message');
  var info = document.getElementById('search-info');
  if (placeholder) placeholder.style.display = 'block';
  if (noResults) noResults.style.display = 'none';
  if (info) info.style.display = 'none';
}

function executeSearch(query) {
  var resultsContainer = document.getElementById('search-results');
  var placeholder = document.getElementById('search-placeholder');
  var info = document.getElementById('search-info');
  if (!resultsContainer) return;

  if (placeholder) placeholder.style.display = 'none';
  if (info) info.style.display = '';

  var noResultsMessage = resultsContainer.querySelector('.no-results-message');
  var existingResults = resultsContainer.querySelectorAll('.result-item');
  existingResults.forEach(function (item) { item.remove(); });

  var results = generateSearchResults(query);
  renderSearchResults(results, noResultsMessage);
  updateCategoryCounts(results);
}

function generateSearchResults(query) {
  var index = window.SEARCH_INDEX || [];
  var terms = query.toLowerCase().split(/\s+/).filter(Boolean);
  var results = [];

  index.forEach(function (item) {
    var searchable = (item.title + ' ' + item.description + ' ' + item.category + ' ' + item.meta).toLowerCase();
    var allMatch = terms.every(function (term) {
      return searchable.indexOf(term) !== -1;
    });
    if (allMatch) {
      results.push(item);
    }
  });

  return results;
}

function highlightText(text, query) {
  if (!query) return escapeHtml(text);
  var terms = query.toLowerCase().split(/\s+/).filter(Boolean);
  var escaped = escapeHtml(text);

  terms.forEach(function (term) {
    var regex = new RegExp('(' + escapeRegex(term) + ')', 'gi');
    escaped = escaped.replace(regex, '<mark>$1</mark>');
  });

  return escaped;
}

function escapeHtml(text) {
  var div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function escapeRegex(str) {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

var TYPE_LABELS = {
  doctor: 'Врач',
  service: 'Услуга',
  article: 'Статья',
  promotion: 'Акция',
  direction: 'Направление'
};

function renderSearchResults(results, noResultsMessage) {
  var resultsContainer = document.getElementById('search-results');
  if (!resultsContainer) return;

  if (noResultsMessage) noResultsMessage.style.display = 'none';

  if (results.length === 0) {
    if (noResultsMessage) noResultsMessage.style.display = 'block';
    updateResultsCount(0);
    return;
  }

  var params = new URLSearchParams(window.location.search);
  var query = params.get('q') || params.get('query') || '';

  results.forEach(function (result) {
    var resultItem = document.createElement('div');
    resultItem.className = 'result-item ' + result.type;
    resultItem.setAttribute('data-type', result.type);

    var label = TYPE_LABELS[result.type] || result.type;
    var metaHtml = result.meta ? '<span class="result-meta">' + escapeHtml(result.meta) + '</span>' : '';

    resultItem.innerHTML =
      '<div class="result-category ' + result.type + '">' + label + '</div>' +
      '<h3 class="result-title"><a href="' + result.url + '">' + highlightText(result.title, query) + '</a></h3>' +
      '<p class="result-content">' + highlightText(result.description, query) + '</p>' +
      '<div class="result-footer">' +
        '<span class="result-badge">' + escapeHtml(result.category) + '</span>' +
        metaHtml +
        '<a href="' + result.url + '" class="result-link">Подробнее <i class="fas fa-arrow-right"></i></a>' +
      '</div>';

    resultsContainer.appendChild(resultItem);
  });

  updateResultsCount(results.length);

  var activeCategory = document.querySelector('.category-tab.active');
  if (activeCategory && activeCategory.getAttribute('data-category') !== 'all') {
    filterResultsByCategory(activeCategory.getAttribute('data-category'));
  }
}

function updateCategoryCounts(results) {
  var counts = {};
  results.forEach(function (r) {
    counts[r.type] = (counts[r.type] || 0) + 1;
  });

  document.querySelectorAll('.category-tab').forEach(function (tab) {
    var cat = tab.getAttribute('data-category');
    var countSpan = tab.querySelector('.tab-count');
    if (countSpan) countSpan.remove();

    var count = cat === 'all' ? results.length : (counts[cat] || 0);
    if (count > 0) {
      var span = document.createElement('span');
      span.className = 'tab-count';
      span.textContent = count;
      tab.appendChild(span);
    }
  });
}
