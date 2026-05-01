(function () {
  'use strict';

  function formatExperienceYears(years) {
    var n = years % 100;
    if (n >= 11 && n <= 14) return years + ' лет';
    var d = years % 10;
    if (d === 1) return years + ' год';
    if (d >= 2 && d <= 4) return years + ' года';
    return years + ' лет';
  }

  function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function buildDoctorCard(item) {
    var nameParts = (item.fullName || '').trim().split(/\s+/);
    var nameHtml = nameParts.length > 1
      ? nameParts.slice(1).join(' ') + '<br>' + nameParts[0]
      : escapeHtml(item.fullName || '');
    var expText = item.experienceYears != null ? formatExperienceYears(item.experienceYears) : '';
    var card = document.createElement('article');
    card.className = 'doctor-card';
    card.innerHTML =
      '<div class="doctor-photo-wrap">' +
        '<div class="doctor-photo">' +
          '<img src="' + escapeHtml(item.photo || '') + '" alt="' + escapeHtml(item.alt || item.fullName || '') + '">' +
        '</div>' +
      '</div>' +
      '<div class="doctor-content">' +
        '<span class="doctor-badge">' + escapeHtml(item.specialization || '') + '</span>' +
        '<h3 class="doctor-name">' + nameHtml + '</h3>' +
        '<p class="doctor-row"><span class="doctor-label">Стаж работы:</span> <span class="doctor-experience-value">' + escapeHtml(expText) + '</span></p>' +
        '<p class="doctor-row"><span class="doctor-label">Категория:</span> <span class="doctor-category-value">' + escapeHtml(item.category || '') + '</span></p>' +
        '<a href="contacts-page.html" class="doctor-btn">Записаться</a>' +
      '</div>';
    return card;
  }

  function init() {
    var data = window.DOCTORS_DATA;
    if (!data || !data.length) return;
    var container = document.querySelector('#doctors .doctors-container');
    if (!container) return;
    var sorted = data.slice().sort(function (a, b) {
      return (a.fullName || '').localeCompare(b.fullName || '', 'ru');
    });
    container.innerHTML = '';
    sorted.forEach(function (item) {
      container.appendChild(buildDoctorCard(item));
    });
  }

  function run() {
    init();
    try {
      window.dispatchEvent(new CustomEvent('doctorsLoaded'));
    } catch (e) {}
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();
