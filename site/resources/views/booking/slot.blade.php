@extends('layouts.app')

@php
    $isReschedule = $isReschedule ?? false;
    /** @var \App\Models\Appointment|null $rescheduleAppointment */
    $rescheduleAppointment = $rescheduleAppointment ?? null;
    $isAuthed = auth('patient')->check();
    $availableDatesJson = json_encode($availableDates);
    $serviceSlug = $service->slug;
    $doctorSlug = $doctor->slug;
    $formAction = $isReschedule && $rescheduleAppointment
        ? route('cabinet.appointments.reschedule.store', $rescheduleAppointment)
        : ($isAuthed ? route('booking.confirm') : route('booking.slotIntent'));
@endphp

@section('title', $isReschedule ? 'Перенос записи' : 'Дата и время')
@section('body_class', 'patient-booking-page')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=6">
@endpush

@section('content')
    <div class="cabinet-layout">
        @include('partials.cabinet-nav', ['navActive' => 'booking'])
        <main class="cabinet-main">
        <div class="booking-wizard" style="padding: 0;">
        @unless ($isReschedule)
            @include('booking.partials.progress', [
                'step' => 3,
                'stepUrls' => [
                    1 => route('booking.pickService', ['from' => 'any']),
                    2 => route('booking.pickDoctor', ['service' => $service->slug]),
                ],
            ])
        @endunless

        <h1>{{ $isReschedule ? 'Перенос записи' : 'Дата и время' }}</h1>
        <p class="booking-lead">{{ $service->name }} — {{ $doctor->full_name }}</p>

        @if (session('error'))
            <div class="booking-alert booking-alert--error" role="alert">{{ session('error') }}</div>
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

        @if ($availableDates === [])
            <div class="booking-empty">
                Нет свободных слотов в ближайшие 30&nbsp;дней — оставьте заявку по телефону в разделе «Контакты», и мы свяжемся с вами.
            </div>
            <div class="cabinet-actions" style="margin-top: 1.5rem;">
                <a href="{{ route('booking.pickService', ['from' => 'any']) }}" class="cabinet-btn cabinet-btn--ghost">← К услугам</a>
            </div>
        @else
            <div class="booking-slot-layout">
                <div class="booking-slot-col">
                    <h2>Дата</h2>
                    <div id="booking-calendar"></div>
                    <div class="booking-calendar-legend">
                        <div class="booking-calendar-legend__item">
                            <span class="booking-calendar-legend__swatch booking-calendar-legend__swatch--available"></span>
                            <span>Доступно</span>
                        </div>
                        <div class="booking-calendar-legend__item">
                            <span class="booking-calendar-legend__swatch booking-calendar-legend__swatch--unavailable"></span>
                            <span>Недоступно</span>
                        </div>
                        <div class="booking-calendar-legend__item">
                            <span class="booking-calendar-legend__swatch booking-calendar-legend__swatch--selected"></span>
                            <span>Выбрано</span>
                        </div>
                    </div>
                </div>
                <div class="booking-slot-col">
                    <h2>Время</h2>
                    <div id="booking-slots-skeleton" class="booking-slot-skeleton" hidden>
                        @for ($i = 0; $i < 8; $i++)
                            <div class="pulse" aria-hidden="true"></div>
                        @endfor
                    </div>
                    <div id="booking-slots-grid" class="booking-slots-grid" style="display: flex; flex-wrap: wrap; gap: 0.5rem;"></div>
                    <p id="booking-slots-empty" style="display: none; color: #64748b; font-size: 0.95rem;">На этот день нет свободных окон.</p>
                </div>
            </div>

            <form id="booking-slot-form" method="post" action="{{ $formAction }}" style="margin-top: 2rem;">
                @csrf
                @if ($isReschedule && $rescheduleAppointment)
                    <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
                @elseif ($isAuthed)
                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                    <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
                @else
                    <input type="hidden" name="service" value="{{ $service->slug }}">
                    <input type="hidden" name="doctor" value="{{ $doctor->slug }}">
                @endif
                <input type="hidden" name="start_at" id="booking_start_at" value="{{ old('start_at', $prefillStartAt) }}">

                @unless ($isReschedule)
                    <label class="consent-label">
                        <input type="checkbox" name="processing_consent" value="1" @checked(old('processing_consent')) required>
                        <span>Согласен(на) на обработку персональных данных в целях записи на приём.</span>
                    </label>
                    @error('processing_consent') <p style="color:#b91c1c; margin-top: 0.35rem;">{{ $message }}</p> @enderror
                @endunless

                <div class="booking-slot-actions-wrap" style="margin-top: 1.25rem;">
                    <div id="booking-client-errors" class="booking-alert booking-alert--error" role="alert" hidden></div>
                    <div class="cabinet-actions--compact-equal">
                        @if ($isReschedule && $rescheduleAppointment)
                            <a href="{{ route('cabinet.appointments.show', $rescheduleAppointment) }}" class="cabinet-btn cabinet-btn--ghost">← К записи</a>
                        @elseif (! ($singleDoctor ?? false))
                            <a href="{{ route('booking.pickDoctor', ['service' => $service->slug]) }}" class="cabinet-btn cabinet-btn--ghost">← К врачам</a>
                        @else
                            <a href="{{ route('booking.pickService', ['from' => 'any']) }}" class="cabinet-btn cabinet-btn--ghost">← К услугам</a>
                        @endif
                        <button type="submit" class="cabinet-btn cabinet-btn--primary" id="booking-submit">
                            @if ($isReschedule)
                                Перенести запись
                            @elseif ($isAuthed)
                                Подтвердить запись
                            @else
                                Войти и подтвердить
                            @endif
                        </button>
                    </div>
                </div>
            </form>
        @endif

        <div id="booking-config" hidden
             data-available="{{ $availableDatesJson }}"
             data-service="{{ $serviceSlug }}"
             data-doctor="{{ $doctorSlug }}"
             data-selected="{{ $selectedDate ?? '' }}"
             data-prefill="{{ $prefillStartAt ?? '' }}"></div>
        </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
    <script>
        (function () {
            var cfg = document.getElementById('booking-config');
            var availableDates = cfg ? JSON.parse(cfg.dataset.available || '[]') : [];
            var service = cfg ? cfg.dataset.service : '';
            var doctor = cfg ? cfg.dataset.doctor : '';
            var selectedDate = cfg ? (cfg.dataset.selected || null) : null;
            var prefill = cfg ? (cfg.dataset.prefill || null) : null;

            var calEl = document.getElementById('booking-calendar');
            var form = document.getElementById('booking-slot-form');
            if (!calEl || !form || !availableDates.length) return;

            var startInput = document.getElementById('booking_start_at');
            var submitBtn = document.getElementById('booking-submit');
            var slotsGrid = document.getElementById('booking-slots-grid');
            var slotsEmpty = document.getElementById('booking-slots-empty');
            var skeleton = document.getElementById('booking-slots-skeleton');
            var summaryDatetime = document.getElementById('summary-datetime');
            var slotsLoadToken = 0;

            /* ── Calendar dropdown state ── */
            var monthPanelEl = null;
            var yearPanelEl = null;
            var monthLabelEl = null;
            var yearLabelEl = null;

            function openPanel(panel, pill, calendarContainer) {
                var r = pill.getBoundingClientRect();
                var topPos = r.bottom + 4;
                panel.style.left = r.left + 'px';
                panel.style.top = topPos + 'px';
                panel.style.minWidth = Math.max(r.width, 120) + 'px';
                if (calendarContainer) {
                    var calRect = calendarContainer.getBoundingClientRect();
                    var maxH = Math.floor(calRect.bottom - topPos - 8);
                    panel.style.maxHeight = (maxH < 0 ? 0 : maxH) + 'px';
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

            /** Get allowed month range for a year based on availableDates and today. */
            function getMonthIndexBoundsForYear(year) {
                var now = new Date();
                var minMonth = (year === now.getFullYear()) ? now.getMonth() : 0;
                var maxMonth = 11;
                var foundYear = false;
                for (var i = availableDates.length - 1; i >= 0; i--) {
                    var d = new Date(availableDates[i]);
                    if (d.getFullYear() === year) {
                        maxMonth = d.getMonth();
                        foundYear = true;
                        break;
                    }
                }
                if (!foundYear) {
                    maxMonth = minMonth;
                }
                if (year !== now.getFullYear()) {
                    for (var j = 0; j < availableDates.length; j++) {
                        var d2 = new Date(availableDates[j]);
                        if (d2.getFullYear() === year) {
                            minMonth = d2.getMonth();
                            break;
                        }
                    }
                }
                return { minMonth: minMonth, maxMonth: maxMonth };
            }

            function refreshMonthPanel(fp) {
                if (!monthPanelEl || !monthLabelEl) return;
                var names = fp.l10n.months.longhand;
                monthLabelEl.textContent = names[fp.currentMonth];
                var b = getMonthIndexBoundsForYear(fp.currentYear);
                var cur = Math.min(Math.max(fp.currentMonth, b.minMonth), b.maxMonth);
                if (cur !== fp.currentMonth) {
                    fp.changeMonth(cur, false);
                }
                while (monthPanelEl.firstChild) {
                    monthPanelEl.removeChild(monthPanelEl.firstChild);
                }
                for (var idx = b.minMonth; idx <= b.maxMonth; idx++) {
                    (function (monthIndex) {
                        var li = document.createElement('li');
                        li.dataset.monthIndex = String(monthIndex);
                        li.textContent = names[monthIndex];
                        if (monthIndex === fp.currentMonth) { li.className = 'is-active'; }
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
                var now = new Date();
                var atMin = fp.currentYear === now.getFullYear() && fp.currentMonth === now.getMonth();
                var atMax = false;
                if (availableDates.length) {
                    var last = new Date(availableDates[availableDates.length - 1]);
                    atMax = fp.currentYear === last.getFullYear() && fp.currentMonth === last.getMonth();
                }
                cal.classList.toggle('fp-nav-hide-prev', atMin);
                cal.classList.toggle('fp-nav-hide-next', atMax);
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
                    if (yearPanelEl) { yearPanelEl.hidden = true; }
                    openPanel(monthPanelEl, p.btn, fp.calendarContainer);
                });

                var wrap = makeWrap(p.btn);
                sel.style.display = 'none';
                sel.parentNode.insertBefore(wrap, sel);
            }

            function injectYearSelect(fp) {
                var wrapper = fp.calendarContainer.querySelector('.numInputWrapper');
                if (!wrapper) return;

                var now = new Date();
                var minYear = now.getFullYear();
                var maxYear = minYear;
                if (availableDates.length) {
                    maxYear = Math.max(minYear, new Date(availableDates[availableDates.length - 1]).getFullYear());
                }

                var p = makePill(fp.currentYear);
                yearLabelEl = p.label;

                yearPanelEl = makePanel();
                for (var y = maxYear; y >= minYear; y--) {
                    (function (year) {
                        var li = document.createElement('li');
                        li.textContent = year;
                        if (year === fp.currentYear) { li.className = 'is-active'; }
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

                p.btn.addEventListener('click', function () {
                    if (!yearPanelEl.hidden) { yearPanelEl.hidden = true; return; }
                    if (monthPanelEl) { monthPanelEl.hidden = true; }
                    openPanel(yearPanelEl, p.btn, fp.calendarContainer);
                    var active = yearPanelEl.querySelector('.is-active');
                    if (active) { active.scrollIntoView({ block: 'nearest' }); }
                });

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

            /* ── Slot loading ── */
            function slotsUrl(dateStr) {
                var p = new URLSearchParams({ service: service, doctor: doctor, date: dateStr });
                return '/api/v1/booking/slots?' + p.toString();
            }

            function normalizeSlotKey(s) {
                if (!s || typeof s !== 'string') return '';
                var t = s.trim().replace(' ', 'T');
                if (t.length >= 19) { return t.slice(0, 19); }
                return t;
            }

            function slotButtonLabel(iso) {
                if (typeof iso === 'string' && iso.length >= 16 && iso.charAt(10) === 'T') {
                    return iso.slice(11, 16);
                }
                var d = new Date(iso);
                return isNaN(d.getTime())
                    ? ''
                    : d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
            }

            function updateSummaryDatetime(iso) {
                if (!summaryDatetime) return;
                if (!iso) { summaryDatetime.textContent = '— не выбрано'; return; }
                var d = new Date(iso.replace ? iso.replace(' ', 'T') : iso);
                summaryDatetime.textContent = isNaN(d.getTime())
                    ? iso
                    : d.toLocaleString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            }

            function selectSlot(iso, btn) {
                if (startInput) { startInput.value = iso; }
                slotsGrid.querySelectorAll('.booking-slot-btn').forEach(function (b) {
                    b.classList.remove('is-selected');
                });
                if (btn) { btn.classList.add('is-selected'); }
                updateSummaryDatetime(iso);
            }

            function renderSlots(isoList) {
                slotsGrid.innerHTML = '';
                if (!isoList.length) {
                    slotsEmpty.textContent = 'На этот день нет свободных окон.';
                    slotsEmpty.style.display = '';
                    selectSlot('', null);
                    return;
                }
                slotsEmpty.style.display = 'none';
                isoList.forEach(function (iso) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'booking-slot-btn';
                    b.textContent = slotButtonLabel(iso);
                    b.addEventListener('click', function () { selectSlot(iso, b); });
                    slotsGrid.appendChild(b);
                });
                if (prefill) {
                    var pk = normalizeSlotKey(prefill);
                    var idx = isoList.map(normalizeSlotKey).indexOf(pk);
                    if (idx !== -1) {
                        var btns = slotsGrid.querySelectorAll('.booking-slot-btn');
                        if (btns[idx]) { selectSlot(isoList[idx], btns[idx]); }
                    }
                    prefill = null;
                }
            }

            function loadSlots(dateStr) {
                var token = ++slotsLoadToken;
                skeleton.hidden = false;
                slotsGrid.innerHTML = '';
                slotsEmpty.style.display = 'none';
                if (summaryDatetime) { summaryDatetime.textContent = '— выберите время'; }
                fetch(slotsUrl(dateStr), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(function (r) {
                        if (!r.ok) { return Promise.reject({ httpStatus: r.status }); }
                        return r.json();
                    })
                    .then(function (payload) {
                        if (token !== slotsLoadToken) return;
                        skeleton.hidden = true;
                        renderSlots(payload && payload.data ? payload.data : []);
                    })
                    .catch(function (err) {
                        if (token !== slotsLoadToken) return;
                        skeleton.hidden = true;
                        slotsEmpty.style.display = '';
                        slotsEmpty.textContent = err && err.httpStatus === 429
                            ? 'Слишком много запросов. Подождите минуту и снова выберите дату.'
                            : 'Не удалось загрузить слоты. Попробуйте ещё раз.';
                        selectSlot('', null);
                    });
            }

            flatpickr(calEl, {
                inline: true,
                locale: flatpickr.l10ns.ru,
                enable: availableDates,
                minDate: 'today',
                defaultDate: selectedDate || availableDates[0],
                disableMobile: true,
                onReady: function (dates, str, fp) {
                    fp.calendarContainer.classList.add('fp-booking-cal');
                    injectMonthSelect(fp);
                    injectYearSelect(fp);
                    syncCalendarNavArrows(fp);
                },
                onMonthChange: function (dates, str, fp) {
                    var names = fp.l10n.months.longhand;
                    if (monthLabelEl) { monthLabelEl.textContent = names[fp.currentMonth]; }
                    if (monthPanelEl) {
                        monthPanelEl.querySelectorAll('li').forEach(function (li) {
                            li.classList.toggle('is-active', li.dataset.monthIndex === String(fp.currentMonth));
                        });
                    }
                    if (yearLabelEl) { yearLabelEl.textContent = fp.currentYear; }
                    refreshMonthPanel(fp);
                    syncCalendarNavArrows(fp);
                },
                onYearChange: function (dates, str, fp) {
                    if (yearLabelEl) { yearLabelEl.textContent = fp.currentYear; }
                    refreshMonthPanel(fp);
                    syncCalendarNavArrows(fp);
                },
                onChange: function (dates, dateStr) {
                    if (dateStr) { loadSlots(dateStr); }
                },
            });

            var initial = selectedDate || availableDates[0];
            if (initial) { loadSlots(initial); }

            /* ── Client-side validation ── */
            var errBlock = document.getElementById('booking-client-errors');
            form.addEventListener('submit', function (e) {
                var msgs = [];
                if (!startInput || !startInput.value) {
                    msgs.push('Выберите дату и время приёма.');
                }
                var consent = form.querySelector('[name="processing_consent"]');
                if (consent && !consent.checked) {
                    msgs.push('Необходимо дать согласие на обработку персональных данных.');
                }
                if (!msgs.length) {
                    if (errBlock) { errBlock.hidden = true; }
                    return;
                }
                e.preventDefault();
                if (errBlock) {
                    errBlock.innerHTML = '<ul style="margin:0;padding-left:1.1rem;">'
                        + msgs.map(function (m) { return '<li>' + m + '</li>'; }).join('')
                        + '</ul>';
                    errBlock.hidden = false;
                    errBlock.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        })();
    </script>
@endpush
