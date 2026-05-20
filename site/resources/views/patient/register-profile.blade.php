@extends('layouts.app')

@section('title', 'Регистрация — личные данные')
@section('body_class', 'patient-auth-page')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=6">
@endpush

@section('content')
    <div class="container" style="max-width: 560px; padding: 3rem 1rem;">
        <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Регистрация</h1>
        <p style="color: #64748b; margin-bottom: 1.5rem;">Шаг 1 из 3: личные данные</p>

        @if (session('error'))
            <p style="color: #b91c1c; margin-bottom: 1rem;">{{ session('error') }}</p>
        @endif

        <form method="post" action="{{ route('patient.register.profile.store') }}" class="patient-form">
            @csrf
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="last_name">Фамилия</label>
                <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $profile['last_name'] ?? '') }}" required
                       class="patient-input" style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                @error('last_name') <span style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="first_name">Имя</label>
                <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $profile['first_name'] ?? '') }}" required
                       class="patient-input" style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                @error('first_name') <span style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="middle_name">Отчество</label>
                <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $profile['middle_name'] ?? '') }}"
                       class="patient-input" style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                @error('middle_name') <span style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="birth_date">Дата рождения</label>
                <input type="text" id="birth_date" name="birth_date" placeholder="ДД.ММ.ГГГГ"
                       value="{{ old('birth_date', $profile['birth_date'] ?? '') }}" required
                       class="datepicker-input" style="box-sizing:border-box;">
                @error('birth_date') <span style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <fieldset style="border: none; padding: 0; margin-bottom: 1.25rem;">
                <legend style="font-weight: 600; margin-bottom: 0.5rem;">Пол</legend>
                <label style="margin-right: 1rem;">
                    <input type="radio" name="gender" value="male" @checked(old('gender', $profile['gender'] ?? '') === 'male') required> Мужской
                </label>
                <label>
                    <input type="radio" name="gender" value="female" @checked(old('gender', $profile['gender'] ?? '') === 'female')> Женский
                </label>
                @error('gender') <div style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</div> @enderror
            </fieldset>
            <div style="display: flex; gap: 0.75rem; margin-top: 0.25rem;">
                <a href="{{ route('patient.login') }}" class="cabinet-btn cabinet-btn--ghost" style="flex: 1; justify-content: center;">Уже есть аккаунт? Войти</a>
                <button type="submit" class="cabinet-btn cabinet-btn--primary" style="flex: 1;">Далее</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
<script>
(function () {
    var monthLabelEl = null, monthPanelEl = null;
    var yearLabelEl  = null, yearPanelEl  = null;

    function openPanel(panel, pill, calendarContainer) {
        var r = pill.getBoundingClientRect();
        var topPos = r.bottom + 4;
        panel.style.left     = r.left + 'px';
        panel.style.top      = topPos + 'px';
        panel.style.minWidth = Math.max(r.width, 120) + 'px';
        if (calendarContainer) {
            var calRect = calendarContainer.getBoundingClientRect();
            var gap = 8;
            var maxH = Math.floor(calRect.bottom - topPos - gap);
            if (maxH < 0) maxH = 0;
            panel.style.maxHeight = maxH + 'px';
            panel.style.overflowY = 'auto';
        } else {
            panel.style.maxHeight = '';
            panel.style.overflowY = '';
        }
        panel.hidden = false;
    }

    function makePanel(extraClass) {
        var ul = document.createElement('ul');
        ul.className = 'fp-year-panel' + (extraClass ? ' ' + extraClass : '');
        ul.hidden = true;
        ul.addEventListener('mousedown', function (e) { e.stopPropagation(); });
        document.body.appendChild(ul);
        return ul;
    }

    function makePill(text) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'fp-year-pill';
        var span = document.createElement('span');
        span.textContent = text;
        btn.appendChild(span);
        return { btn: btn, label: span };
    }

    function makeWrap(pill) {
        var div = document.createElement('div');
        div.className = 'fp-year-wrap';
        div.appendChild(pill);
        return div;
    }

    function parseConfigDate(fp, raw) {
        if (raw == null) return null;
        if (typeof raw === 'function') {
            raw = raw();
        }
        if (raw instanceof Date) return raw;
        return fp.parseDate(raw);
    }

    function getMonthIndexBoundsForYear(fp, year) {
        var minMonth = 0;
        var maxMonth = 11;
        var maxD = parseConfigDate(fp, fp.config.maxDate);
        if (maxD && year === maxD.getFullYear()) {
            maxMonth = maxD.getMonth();
        }
        var minD = parseConfigDate(fp, fp.config.minDate);
        if (minD && year === minD.getFullYear()) {
            minMonth = minD.getMonth();
        }
        return { minMonth: minMonth, maxMonth: maxMonth };
    }

    function clampViewingMonthToConfig(fp) {
        var b = getMonthIndexBoundsForYear(fp, fp.currentYear);
        var t = Math.min(Math.max(fp.currentMonth, b.minMonth), b.maxMonth);
        if (t !== fp.currentMonth) {
            fp.changeMonth(t, false);
        }
    }

    function refreshMonthPanel(fp) {
        if (!monthPanelEl || !monthLabelEl) return;
        clampViewingMonthToConfig(fp);
        var names = fp.l10n.months.longhand;
        monthLabelEl.textContent = names[fp.currentMonth];
        var b = getMonthIndexBoundsForYear(fp, fp.currentYear);
        while (monthPanelEl.firstChild) {
            monthPanelEl.removeChild(monthPanelEl.firstChild);
        }
        for (var idx = b.minMonth; idx <= b.maxMonth; idx++) {
            (function (monthIndex) {
                var li = document.createElement('li');
                li.dataset.monthIndex = String(monthIndex);
                li.textContent = names[monthIndex];
                if (monthIndex === fp.currentMonth) li.className = 'is-active';
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    fp.changeMonth(monthIndex, false);
                    monthLabelEl.textContent = names[monthIndex];
                    monthPanelEl.querySelectorAll('li').forEach(function (el) { el.classList.remove('is-active'); });
                    li.classList.add('is-active');
                    monthPanelEl.hidden = true;
                });
                monthPanelEl.appendChild(li);
            })(idx);
        }
    }

    function syncCalendarNavArrows(fp) {
        var cal = fp.calendarContainer;
        if (!cal) return;
        var maxD = parseConfigDate(fp, fp.config.maxDate);
        var minD = parseConfigDate(fp, fp.config.minDate);
        var atMax = maxD &&
            fp.currentYear === maxD.getFullYear() &&
            fp.currentMonth === maxD.getMonth();
        var atMin = minD &&
            fp.currentYear === minD.getFullYear() &&
            fp.currentMonth === minD.getMonth();
        cal.classList.toggle('fp-nav-hide-next', !!atMax);
        cal.classList.toggle('fp-nav-hide-prev', !!atMin);
    }

    function injectMonthSelect(fp) {
        var sel = fp.calendarContainer.querySelector('.flatpickr-monthDropdown-months');
        if (!sel || sel.dataset.replaced) return;
        sel.dataset.replaced = '1';

        var names = fp.l10n.months.longhand;
        var p = makePill(names[fp.currentMonth]);
        monthLabelEl = p.label;

        monthPanelEl = makePanel('fp-month-panel');
        refreshMonthPanel(fp);

        p.btn.addEventListener('click', function () {
            if (!monthPanelEl.hidden) { monthPanelEl.hidden = true; return; }
            if (yearPanelEl) yearPanelEl.hidden = true;
            openPanel(monthPanelEl, p.btn, fp.calendarContainer);
        });

        var wrap = makeWrap(p.btn);
        sel.style.display = 'none';
        sel.parentNode.insertBefore(wrap, sel);
    }

    function injectYearSelect(fp) {
        var wrapper = fp.calendarContainer.querySelector('.numInputWrapper');
        if (!wrapper) return;

        var maxYear = new Date().getFullYear();
        var minYear = 1906;

        var p = makePill(fp.currentYear);
        yearLabelEl = p.label;

        yearPanelEl = makePanel();
        for (var y = maxYear; y >= minYear; y--) {
            (function (year) {
                var li = document.createElement('li');
                li.textContent = year;
                if (year === fp.currentYear) li.className = 'is-active';
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    fp.changeYear(year);
                    yearLabelEl.textContent = year;
                    yearPanelEl.querySelectorAll('li').forEach(function (el) { el.classList.remove('is-active'); });
                    li.classList.add('is-active');
                    yearPanelEl.hidden = true;
                });
                yearPanelEl.appendChild(li);
            })(y);
        }

        var clickTimer = null;
        p.btn.addEventListener('click', function () {
            if (clickTimer !== null) {
                clearTimeout(clickTimer); clickTimer = null;
                yearPanelEl.hidden = true;
                switchToInput();
                return;
            }
            clickTimer = setTimeout(function () {
                clickTimer = null;
                if (!yearPanelEl.hidden) { yearPanelEl.hidden = true; return; }
                if (monthPanelEl) monthPanelEl.hidden = true;
                openPanel(yearPanelEl, p.btn, fp.calendarContainer);
                var active = yearPanelEl.querySelector('.is-active');
                if (active) active.scrollIntoView({ block: 'nearest' });
            }, 280);
        });

        function switchToInput() {
            var inp = document.createElement('input');
            inp.type = 'number'; inp.className = 'fp-year-input';
            inp.value = fp.currentYear; inp.min = minYear; inp.max = maxYear;
            var wrap = p.btn.parentNode;
            p.btn.style.display = 'none';
            wrap.insertBefore(inp, p.btn.nextSibling);
            inp.focus(); inp.select();
            function commit() {
                var val = parseInt(inp.value, 10);
                if (!isNaN(val) && val >= minYear && val <= maxYear) {
                    fp.changeYear(val); yearLabelEl.textContent = val;
                    yearPanelEl.querySelectorAll('li').forEach(function (el) {
                        el.classList.toggle('is-active', parseInt(el.textContent, 10) === val);
                    });
                    syncCalendarNavArrows(fp);
                }
                if (inp.parentNode) wrap.removeChild(inp);
                p.btn.style.display = '';
            }
            inp.addEventListener('blur', commit);
            inp.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') inp.blur();
                if (e.key === 'Escape') { inp.removeEventListener('blur', commit); if (inp.parentNode) wrap.removeChild(inp); p.btn.style.display = ''; }
            });
        }

        var wrap = makeWrap(p.btn);
        wrapper.style.display = 'none';
        wrapper.parentNode.insertBefore(wrap, wrapper.nextSibling);
    }

    document.addEventListener('mousedown', function (e) {
        if (monthPanelEl && !monthPanelEl.contains(e.target) &&
            !(e.target.closest && e.target.closest('.fp-year-wrap'))) {
            monthPanelEl.hidden = true;
        }
        if (yearPanelEl && !yearPanelEl.contains(e.target) &&
            !(e.target.closest && e.target.closest('.fp-year-wrap'))) {
            yearPanelEl.hidden = true;
        }
    });

    flatpickr('#birth_date', {
        locale: 'ru',
        dateFormat: 'd.m.Y',
        minDate: new Date(1906, 0, 1),
        maxDate: 'today',
        allowInput: true,
        disableMobile: false,
        onReady: function (dates, str, fp) {
            injectMonthSelect(fp);
            injectYearSelect(fp);
            syncCalendarNavArrows(fp);
        },
        onOpen: function (dates, str, fp) {
            requestAnimationFrame(function () {
                var cal   = fp.calendarContainer;
                var input = fp.altInput || fp.input;
                var ir    = input.getBoundingClientRect();
                var calW    = cal.offsetWidth;
                var newLeft = ir.left + window.scrollX + ir.width / 2 - calW / 2;
                cal.style.left = Math.max(8, newLeft) + 'px';
                var top = ir.top + window.scrollY - cal.offsetHeight - 6;
                if (top > 0) {
                    cal.style.top = top + 'px';
                    cal.classList.remove('arrowTop');
                    cal.classList.add('arrowBottom');
                }
                syncCalendarNavArrows(fp);
            });
        },
        onMonthChange: function (dates, str, fp) {
            var names = fp.l10n.months.longhand;
            if (monthLabelEl) monthLabelEl.textContent = names[fp.currentMonth];
            if (monthPanelEl) monthPanelEl.querySelectorAll('li').forEach(function (li) {
                var mi = parseInt(li.dataset.monthIndex, 10);
                li.classList.toggle('is-active', mi === fp.currentMonth);
            });
            if (yearLabelEl) yearLabelEl.textContent = fp.currentYear;
            syncCalendarNavArrows(fp);
        },
        onYearChange: function (dates, str, fp) {
            refreshMonthPanel(fp);
            syncCalendarNavArrows(fp);
        },
    });
})();
</script>
@endpush
