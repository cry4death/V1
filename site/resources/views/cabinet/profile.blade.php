@extends('layouts.app')

@section('title', 'Профиль')
@section('body_class', 'patient-booking-page')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=6">
@endpush

@section('content')
    <div class="cabinet-layout">
        @include('partials.cabinet-nav', ['navActive' => 'profile'])
        <main class="cabinet-main">
            <h1>Профиль</h1>
            <p style="color: #64748b; margin: 0 0 1.25rem;">Телефон — ваш логин и не редактируется здесь.</p>

            @if (session('status'))
                <div class="booking-alert booking-alert--ok" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="booking-alert booking-alert--error" role="alert">
                    <ul style="margin: 0; padding-left: 1.1rem;">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('cabinet.profile.update') }}" class="cabinet-card">
                @csrf
                @method('PATCH')

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem 1.5rem;">
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom:0.35rem; color: #334155;">Телефон</label>
                        <input type="text"
                               value="{{ preg_replace('/^\+?375(\d{2})(\d{3})(\d{2})(\d{2})$/', '+375 ($1) $2-$3-$4', $patient->phone) }}"
                               disabled class="cabinet-input">
                    </div>

                    <div>
                        <label for="birth_date" style="display:block; font-weight:600; margin-bottom:0.35rem; color: #334155;">Дата рождения</label>
                        <input type="text" id="birth_date" name="birth_date" placeholder="ДД.ММ.ГГГГ"
                               value="{{ old('birth_date', $patient->birth_date?->format('d.m.Y')) }}" required
                               class="datepicker-input">
                    </div>

                    <div>
                        <label for="last_name" style="display:block; font-weight:600; margin-bottom:0.35rem; color: #334155;">Фамилия</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $patient->last_name) }}" required
                               class="cabinet-input">
                    </div>

                    <div>
                        <label for="first_name" style="display:block; font-weight:600; margin-bottom:0.35rem; color: #334155;">Имя</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $patient->first_name) }}" required
                               class="cabinet-input">
                    </div>

                    <div>
                        <label for="middle_name" style="display:block; font-weight:600; margin-bottom:0.35rem; color: #334155;">Отчество</label>
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $patient->middle_name) }}"
                               class="cabinet-input">
                    </div>

                    <div>
                        <label for="gender" style="display:block; font-weight:600; margin-bottom:0.35rem; color: #334155;">Пол</label>
                        <select id="gender" name="gender" required class="cabinet-select">
                            <option value="male" @selected(old('gender', $patient->gender) === 'male')>Мужской</option>
                            <option value="female" @selected(old('gender', $patient->gender) === 'female')>Женский</option>
                        </select>
                    </div>

                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="cabinet-btn cabinet-btn--primary">Сохранить изменения</button>
                </div>
            </form>
        </main>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
<script>
(function () {
    var monthLabelEl = null, monthPanelEl = null;
    var yearLabelEl  = null, yearPanelEl  = null;

    /* ── helpers ── */
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
        /* stopPropagation prevents Flatpickr's document mousedown handler from
           treating a click inside the panel as an "outside click" and closing the calendar */
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

    /** Resolve min/max dates from Flatpickr config (Date, string, or hook). */
    function parseConfigDate(fp, raw) {
        if (raw == null) return null;
        if (typeof raw === 'function') {
            raw = raw();
        }
        if (raw instanceof Date) return raw;
        return fp.parseDate(raw);
    }

    /** Allowed month indices (0–11) for the given calendar year. */
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

    /** Rebuild month dropdown: only months valid for the selected year (e.g. current year → up to today). */
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

    /**
     * Toggle nav arrows via calendar classes — arrow buttons use display:flex !important in CSS,
     * so inline display:none never wins; classes use !important to hide.
     */
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

    /* ── month dropdown ── */
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

    /* ── year dropdown ── */
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

    /* ── close panels on outside click ── */
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

    /* ── Flatpickr init ── */
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
                /* centre calendar horizontally over the input */
                var calW    = cal.offsetWidth;
                var newLeft = ir.left + window.scrollX + ir.width / 2 - calW / 2;
                cal.style.left = Math.max(8, newLeft) + 'px';
                /* position calendar above the input */
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
