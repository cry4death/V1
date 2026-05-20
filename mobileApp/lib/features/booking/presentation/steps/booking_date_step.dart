import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import '../../../../core/constants/app_colors.dart';
import '../../data/booking_providers.dart';
import '../../state/booking_wizard_notifier.dart';

class BookingDateStep extends ConsumerStatefulWidget {
  final BookingWizardParams params;

  const BookingDateStep({super.key, required this.params});

  @override
  ConsumerState<BookingDateStep> createState() => _BookingDateStepState();
}

class _BookingDateStepState extends ConsumerState<BookingDateStep> {
  late DateTime _displayMonth;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _displayMonth = DateTime(now.year, now.month);
  }

  void _prevMonth() {
    final now = DateTime.now();
    final current = DateTime(now.year, now.month);
    if (_displayMonth.isAfter(current)) {
      setState(() {
        _displayMonth = DateTime(_displayMonth.year, _displayMonth.month - 1);
      });
    }
  }

  void _nextMonth() {
    setState(() {
      _displayMonth = DateTime(_displayMonth.year, _displayMonth.month + 1);
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(bookingWizardProvider(widget.params));
    final serviceSlug = state.service?.slug ?? widget.params.serviceSlug ?? '';
    final doctorSlug = state.doctor?.slug ?? widget.params.doctorSlug ?? '';

    // Load dates for a 60-day window starting from today
    final now = DateTime.now();
    final from = DateFormat('yyyy-MM-dd').format(now);
    final to = DateFormat('yyyy-MM-dd')
        .format(now.add(const Duration(days: 60)));

    final datesAsync = ref.watch(bookingDatesProvider(bookingDatesParams(
      serviceSlug: serviceSlug,
      doctorSlug: doctorSlug,
      from: from,
      to: to,
    )));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 24, 20, 4),
          child: Text(
            'Выберите дату',
            style: GoogleFonts.inter(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
        ),
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 2, 20, 16),
          child: Text(
            'Доступные даты выделены синим',
            style: GoogleFonts.inter(
              fontSize: 13,
              color: AppColors.textSecondary,
            ),
          ),
        ),
        Expanded(
          child: datesAsync.when(
            loading: () => const Center(
              child: CircularProgressIndicator(
                color: AppColors.primary,
                strokeWidth: 2.5,
              ),
            ),
            error: (e, _) => Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'Не удалось загрузить даты',
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      color: AppColors.textSecondary,
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextButton(
                    onPressed: () => ref.invalidate(
                      bookingDatesProvider(bookingDatesParams(
                        serviceSlug: serviceSlug,
                        doctorSlug: doctorSlug,
                        from: from,
                        to: to,
                      )),
                    ),
                    child: Text(
                      'Повторить',
                      style: GoogleFonts.inter(
                        fontWeight: FontWeight.w600,
                        color: AppColors.primary,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            data: (availableDates) {
              final available = availableDates.toSet();
              return SingleChildScrollView(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
                child: _MonthCalendar(
                  displayMonth: _displayMonth,
                  availableDates: available,
                  onPrev: _prevMonth,
                  onNext: _nextMonth,
                  onSelect: (dateStr) => ref
                      .read(bookingWizardProvider(widget.params).notifier)
                      .selectDate(dateStr),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}

class _MonthCalendar extends StatelessWidget {
  final DateTime displayMonth;
  final Set<String> availableDates;
  final VoidCallback onPrev;
  final VoidCallback onNext;
  final ValueChanged<String> onSelect;

  const _MonthCalendar({
    required this.displayMonth,
    required this.availableDates,
    required this.onPrev,
    required this.onNext,
    required this.onSelect,
  });

  static const _weekdays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
  static const _monthNames = [
    'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
    'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь',
  ];

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final currentMonth = DateTime(now.year, now.month);
    final isFirstMonth = !displayMonth.isAfter(currentMonth);

    final firstDay = DateTime(displayMonth.year, displayMonth.month, 1);
    final daysInMonth =
        DateTime(displayMonth.year, displayMonth.month + 1, 0).day;

    // weekday: 1=Mon…7=Sun, offset so Monday is column 0
    final startOffset = firstDay.weekday - 1;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFEEF2F7)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha(10),
            blurRadius: 12,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Month navigator
          Row(
            children: [
              IconButton(
                icon: const Icon(Icons.chevron_left),
                color: isFirstMonth
                    ? const Color(0xFFCDD5DF)
                    : AppColors.primary,
                onPressed: isFirstMonth ? null : onPrev,
                visualDensity: VisualDensity.compact,
              ),
              Expanded(
                child: Text(
                  '${_monthNames[displayMonth.month - 1]} ${displayMonth.year}',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textPrimary,
                  ),
                ),
              ),
              IconButton(
                icon: const Icon(Icons.chevron_right),
                color: AppColors.primary,
                onPressed: onNext,
                visualDensity: VisualDensity.compact,
              ),
            ],
          ),
          const SizedBox(height: 8),
          // Weekday headers
          Row(
            children: _weekdays
                .map(
                  (d) => Expanded(
                    child: Text(
                      d,
                      textAlign: TextAlign.center,
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ),
                )
                .toList(),
          ),
          const SizedBox(height: 8),
          // Days grid
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 7,
              childAspectRatio: 1,
              mainAxisSpacing: 4,
              crossAxisSpacing: 2,
            ),
            itemCount: startOffset + daysInMonth,
            itemBuilder: (context, index) {
              if (index < startOffset) return const SizedBox.shrink();
              final day = index - startOffset + 1;
              final date =
                  DateTime(displayMonth.year, displayMonth.month, day);
              final dateStr = DateFormat('yyyy-MM-dd').format(date);
              final isPast = date.isBefore(today);
              final isToday = date == today;
              final isAvailable = availableDates.contains(dateStr);

              return _DayCell(
                day: day,
                isToday: isToday,
                isAvailable: isAvailable,
                isPast: isPast,
                onTap: isAvailable && !isPast
                    ? () => onSelect(dateStr)
                    : null,
              );
            },
          ),
        ],
      ),
    );
  }
}

class _DayCell extends StatelessWidget {
  final int day;
  final bool isToday;
  final bool isAvailable;
  final bool isPast;
  final VoidCallback? onTap;

  const _DayCell({
    required this.day,
    required this.isToday,
    required this.isAvailable,
    required this.isPast,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final Color bg;
    final Color textColor;

    if (isAvailable && !isPast) {
      bg = AppColors.primary;
      textColor = Colors.white;
    } else if (isToday) {
      bg = const Color(0xFFE8F4FD);
      textColor = AppColors.primary;
    } else {
      bg = Colors.transparent;
      textColor = isPast
          ? const Color(0xFFCDD5DF)
          : AppColors.textPrimary;
    }

    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        margin: const EdgeInsets.all(2),
        decoration: BoxDecoration(
          color: bg,
          shape: BoxShape.circle,
        ),
        child: Center(
          child: Text(
            '$day',
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: isAvailable && !isPast
                  ? FontWeight.w700
                  : FontWeight.w400,
              color: textColor,
            ),
          ),
        ),
      ),
    );
  }
}
