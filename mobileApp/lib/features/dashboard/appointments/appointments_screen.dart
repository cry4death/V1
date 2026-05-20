import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';
import '../dashboard_tab_provider.dart';
import 'appointment_model.dart';
import 'appointments_providers.dart';
import 'appointments_repository.dart';

// ─── Types ────────────────────────────────────────────────────────────────────

enum _AptStatus { upcoming, completed, cancelled }

class _Apt {
  final int id;
  final String doctor;
  final String doctorSlug;
  final String specialty;
  final String date;
  final DateTime dateRaw;
  final String time;
  final String service;
  final String serviceSlug;
  final String address;
  final String phone;
  final String avatar;
  final _AptStatus status;
  final String? diagnosis;
  final String price;
  final bool canCancel;
  final bool canReview;
  final bool reviewed;

  const _Apt({
    required this.id,
    required this.doctor,
    required this.doctorSlug,
    required this.specialty,
    required this.date,
    required this.dateRaw,
    required this.time,
    required this.service,
    required this.serviceSlug,
    required this.address,
    required this.phone,
    required this.avatar,
    required this.status,
    this.diagnosis,
    required this.price,
    this.canCancel = false,
    this.canReview = false,
    this.reviewed = false,
  });

  static const _months = [
    'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
    'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
  ];

  static String _formatDate(DateTime dt) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final day = DateTime(dt.year, dt.month, dt.day);
    final prefix = day == today ? 'Сегодня, ' : '';
    return '$prefix${dt.day} ${_months[dt.month - 1]} ${dt.year}';
  }

  static String _formatTime(DateTime dt) =>
      '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';

  factory _Apt.fromApi(AppointmentModel apt) {
    final startAt = apt.startAt?.toLocal() ?? DateTime.now();

    final _AptStatus uiStatus;
    if (apt.isCompleted) {
      uiStatus = _AptStatus.completed;
    } else if (apt.isCancelled) {
      uiStatus = _AptStatus.cancelled;
    } else {
      uiStatus = _AptStatus.upcoming;
    }

    return _Apt(
      id: apt.id,
      doctor: apt.doctor?.fullName.isNotEmpty == true
          ? apt.doctor!.fullName
          : 'Врач',
      doctorSlug: apt.doctor?.slug ?? '',
      specialty: apt.doctor?.specialty ?? '—',
      date: _formatDate(startAt),
      dateRaw: DateTime(startAt.year, startAt.month, startAt.day),
      time: _formatTime(startAt),
      service: apt.service?.name ?? '—',
      serviceSlug: apt.service?.slug ?? '',
      address: 'г. Минск, ул. К. Туровского, 14',
      phone: '+375 (17) 215 02 89',
      avatar: apt.doctor?.photoUrl ?? '',
      status: uiStatus,
      diagnosis: apt.note?.isNotEmpty == true ? apt.note : null,
      price: apt.service?.priceLabel ?? '—',
      canCancel: apt.isUpcoming,
      canReview: apt.isCompleted,
      reviewed: false,
    );
  }
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

class _StatusCfg {
  final String label;
  final Color bg;
  final Color color;
  final Color dot;
  final IconData icon;

  const _StatusCfg({
    required this.label,
    required this.bg,
    required this.color,
    required this.dot,
    required this.icon,
  });
}

const _cfgMap = {
  _AptStatus.upcoming: _StatusCfg(
    label: 'Предстоящий',
    bg: Color(0xFFE8F4FD),
    color: Color(0xFF1E5A99),
    dot: Color(0xFF4682B4),
    icon: Icons.access_time_outlined,
  ),
  _AptStatus.completed: _StatusCfg(
    label: 'Завершён',
    bg: Color(0xFFE8FDF0),
    color: Color(0xFF2E7D50),
    dot: Color(0xFF3DAA6E),
    icon: Icons.check_circle_outline,
  ),
  _AptStatus.cancelled: _StatusCfg(
    label: 'Отменён',
    bg: Color(0xFFFDE8E8),
    color: Color(0xFFC94F4F),
    dot: Color(0xFFD94F4F),
    icon: Icons.cancel_outlined,
  ),
};

/// Повторная запись к тому же врачу на ту же услугу (визард: дата → слот → подтверждение).
String _bookAgainBookingPath(_Apt apt) {
  final q = <String, String>{};
  if (apt.doctorSlug.isNotEmpty) q['doctor'] = apt.doctorSlug;
  if (apt.serviceSlug.isNotEmpty) q['service'] = apt.serviceSlug;
  if (q.isEmpty) return '/booking';
  return Uri(path: '/booking', queryParameters: q).toString();
}


// ─── Main Screen ─────────────────────────────────────────────────────────────

class AppointmentsScreen extends ConsumerStatefulWidget {
  const AppointmentsScreen({super.key});

  @override
  ConsumerState<AppointmentsScreen> createState() => _AppointmentsScreenState();
}

class _AppointmentsScreenState extends ConsumerState<AppointmentsScreen> {
  bool _showUpcoming = true;
  _Apt? _selected;

  void _openDetail(_Apt apt) => setState(() => _selected = apt);
  void _closeDetail() => setState(() => _selected = null);

  Future<void> _refresh() async {
    ref.invalidate(upcomingAppointmentsProvider);
    ref.invalidate(pastAppointmentsProvider);
  }

  @override
  Widget build(BuildContext context) {
    ref.listen<AppointmentModel?>(appointmentsPendingDetailProvider,
        (previous, next) {
      if (next == null) return;
      final model = next;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        ref.read(appointmentsPendingDetailProvider.notifier).state = null;
        setState(() {
          _showUpcoming = model.isUpcoming;
          _selected = _Apt.fromApi(model);
        });
      });
    });

    // Автообновление при переходе на вкладку «Записи» (индекс 3)
    ref.listen<int>(dashboardTabIndexProvider, (previous, current) {
      if (current == 3 && previous != 3) {
        _refresh();
      }
    });

    final upcomingAsync = ref.watch(upcomingAppointmentsProvider);
    final pastAsync = ref.watch(pastAppointmentsProvider);

    final upcomingList =
        upcomingAsync.valueOrNull?.map(_Apt.fromApi).toList() ?? [];
    final pastList =
        pastAsync.valueOrNull?.map(_Apt.fromApi).toList() ?? [];

    final currentAsync = _showUpcoming ? upcomingAsync : pastAsync;
    final currentList = _showUpcoming ? upcomingList : pastList;

    return Scaffold(
      backgroundColor: const Color(0xFFF7F9FC),
      body: SafeArea(
        bottom: false,
        child: Stack(
          children: [
            Column(
              children: [
                // ── Header ──
                _Header(
                  upcomingCount: upcomingList.length,
                  pastCount: pastList.length,
                  showUpcoming: _showUpcoming,
                  onTabChanged: (v) => setState(() {
                    _showUpcoming = v;
                    _selected = null;
                  }),
                ),

                // ── List ──
                Expanded(
                  child: AnimatedSwitcher(
                    duration: const Duration(milliseconds: 220),
                    child: currentAsync.when(
                      loading: () => const Center(
                        child: SizedBox(
                          width: 28,
                          height: 28,
                          child: CircularProgressIndicator(
                            strokeWidth: 2.5,
                            color: AppColors.primary,
                          ),
                        ),
                      ),
                      error: (err, _) => _ErrorState(
                        onRetry: _refresh,
                      ),
                      data: (_) => _ListBody(
                        key: ValueKey(_showUpcoming),
                        list: currentList,
                        showUpcoming: _showUpcoming,
                        upcoming: upcomingList,
                        past: pastList,
                        onSelect: _openDetail,
                        onRefresh: _refresh,
                      ),
                    ),
                  ),
                ),
              ],
            ),

            // ── Detail sheet overlay ──
            if (_selected != null) ...[
              GestureDetector(
                onTap: _closeDetail,
                child: AnimatedOpacity(
                  opacity: _selected != null ? 1.0 : 0.0,
                  duration: const Duration(milliseconds: 200),
                  child: Container(color: Colors.black.withAlpha(102)),
                ),
              ),
              _DetailSheet(apt: _selected!, onClose: _closeDetail),
            ],
          ],
        ),
      ),
    );
  }
}

// ─── Error State ──────────────────────────────────────────────────────────────

class _ErrorState extends StatelessWidget {
  final VoidCallback onRetry;
  const _ErrorState({required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: const Color(0xFFFDE8E8),
                borderRadius: BorderRadius.circular(22),
              ),
              child: const Icon(Icons.cloud_off_outlined,
                  size: 32, color: Color(0xFFD94F4F)),
            ),
            const SizedBox(height: 16),
            Text(
              'Не удалось загрузить записи',
              style: GoogleFonts.inter(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                color: const Color(0xFF222222),
              ),
            ),
            const SizedBox(height: 8),
            TextButton(
              onPressed: onRetry,
              child: Text(
                'Повторить',
                style: GoogleFonts.inter(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: AppColors.primary,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Header ──────────────────────────────────────────────────────────────────

class _Header extends StatelessWidget {
  final int upcomingCount;
  final int pastCount;
  final bool showUpcoming;
  final ValueChanged<bool> onTabChanged;

  const _Header({
    required this.upcomingCount,
    required this.pastCount,
    required this.showUpcoming,
    required this.onTabChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 4),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Мои записи',
                  style: GoogleFonts.inter(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF101623),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  upcomingCount > 0
                      ? '$upcomingCount предстоящих · $pastCount завершённых'
                      : 'Нет предстоящих записей',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: const Color(0xFFA0AABF),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),
          // Tabs
          Row(
            children: [
              _Tab(
                label: 'Предстоящие',
                count: upcomingCount,
                isActive: showUpcoming,
                onTap: () => onTabChanged(true),
              ),
              _Tab(
                label: 'Прошедшие',
                count: pastCount,
                isActive: !showUpcoming,
                onTap: () => onTabChanged(false),
              ),
            ],
          ),
          Container(height: 1, color: const Color(0xFFEEF2F7)),
        ],
      ),
    );
  }
}

class _Tab extends StatelessWidget {
  final String label;
  final int count;
  final bool isActive;
  final VoidCallback onTap;

  const _Tab({
    required this.label,
    required this.count,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: onTap,
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 11),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    label,
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      fontWeight:
                          isActive ? FontWeight.w700 : FontWeight.w400,
                      color: isActive
                          ? AppColors.primary
                          : const Color(0xFFA0AABF),
                    ),
                  ),
                  const SizedBox(width: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 8, vertical: 1),
                    decoration: BoxDecoration(
                      color: isActive
                          ? const Color(0xFFE8F4FD)
                          : const Color(0xFFF4F7FB),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      '$count',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: isActive
                            ? AppColors.primary
                            : const Color(0xFFA0AABF),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Container(
              height: 2.5,
              color: isActive
                  ? AppColors.primary
                  : Colors.transparent,
            ),
          ],
        ),
      ),
    );
  }
}

// ─── List Body ────────────────────────────────────────────────────────────────

class _ListBody extends StatelessWidget {
  final List<_Apt> list;
  final bool showUpcoming;
  final List<_Apt> upcoming;
  final List<_Apt> past;
  final ValueChanged<_Apt> onSelect;
  final Future<void> Function()? onRefresh;

  const _ListBody({
    super.key,
    required this.list,
    required this.showUpcoming,
    required this.upcoming,
    required this.past,
    required this.onSelect,
    this.onRefresh,
  });

  @override
  Widget build(BuildContext context) {
    if (list.isEmpty) {
      return _EmptyRefreshable(
        isUpcoming: showUpcoming,
        onRefresh: onRefresh,
      );
    }

    final bottomPad = MediaQuery.of(context).padding.bottom + 80;
    final listView = ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: EdgeInsets.fromLTRB(16, 16, 16, bottomPad),
      children: [
        if (showUpcoming && upcoming.isNotEmpty) ...[
          _NextHighlight(apt: upcoming.first),
          const SizedBox(height: 14),
          Text(
            'Все предстоящие записи',
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF717784),
            ),
          ),
          const SizedBox(height: 10),
        ],
        if (!showUpcoming && past.isNotEmpty) ...[
          Text(
            'История посещений',
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF717784),
            ),
          ),
          const SizedBox(height: 10),
        ],
        ...list.asMap().entries.map((e) => _AptCard(
              apt: e.value,
              index: e.key,
              onTap: () => onSelect(e.value),
            )),
      ],
    );

    if (onRefresh != null) {
      return RefreshIndicator(
        color: AppColors.primary,
        onRefresh: onRefresh!,
        child: listView,
      );
    }
    return listView;
  }
}

// ─── Next Appointment Highlight ───────────────────────────────────────────────

class _NextHighlight extends StatelessWidget {
  final _Apt apt;
  const _NextHighlight({required this.apt});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: AppColors.primaryGradient,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withAlpha(77),
            blurRadius: 28,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          // Date box
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: Colors.white.withAlpha(46),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  '${apt.dateRaw.day}',
                  style: GoogleFonts.inter(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                    height: 1,
                  ),
                ),
                Text(
                  _monthShort(apt.dateRaw.month),
                  style: GoogleFonts.inter(
                    fontSize: 9,
                    color: Colors.white.withAlpha(204),
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 14),
          // Info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Ближайший приём',
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    color: Colors.white.withAlpha(191),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  apt.doctor,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                Text(
                  '${apt.time} · ${apt.service}',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: Colors.white.withAlpha(204),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    ).animate().fadeIn(duration: 300.ms).moveY(begin: 12, end: 0);
  }

  static String _monthShort(int m) {
    const months = [
      'янв', 'фев', 'мар', 'апр', 'май', 'июн',
      'июл', 'авг', 'сен', 'окт', 'ноя', 'дек',
    ];
    return months[m - 1];
  }
}

// ─── Appointment Card ─────────────────────────────────────────────────────────

class _AptCard extends StatelessWidget {
  final _Apt apt;
  final int index;
  final VoidCallback onTap;

  const _AptCard({
    required this.apt,
    required this.index,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final cfg = _cfgMap[apt.status]!;
    final isToday = apt.status == _AptStatus.upcoming &&
        apt.dateRaw == DateTime(
          DateTime.now().year,
          DateTime.now().month,
          DateTime.now().day,
        );

    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isToday
                ? const Color(0xFFC8D8E8)
                : const Color(0xFFF0F4F8),
            width: isToday ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: isToday
                  ? AppColors.primary.withAlpha(51)
                  : Colors.black.withAlpha(15),
              blurRadius: isToday ? 24 : 12,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Дата + статус
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Padding(
                                padding: EdgeInsets.only(top: 1),
                                child: Icon(
                                  Icons.calendar_today_outlined,
                                  size: 13,
                                  color: AppColors.primary,
                                ),
                              ),
                              const SizedBox(width: 5),
                              Expanded(
                                child: Text(
                                  apt.date,
                                  style: GoogleFonts.inter(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: AppColors.primary,
                                    height: 1.35,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 9, vertical: 3),
                          decoration: BoxDecoration(
                            color: cfg.bg,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                width: 5,
                                height: 5,
                                decoration: BoxDecoration(
                                  color: cfg.dot,
                                  shape: BoxShape.circle,
                                ),
                              ),
                              const SizedBox(width: 4),
                              ConstrainedBox(
                                constraints:
                                    const BoxConstraints(maxWidth: 100),
                                child: Text(
                                  cfg.label,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: GoogleFonts.inter(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w600,
                                    color: cfg.color,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Doctor row
                    Row(
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(15),
                          child: apt.avatar.isNotEmpty
                              ? Image.network(
                                  apt.avatar,
                                  width: 50,
                                  height: 50,
                                  fit: BoxFit.cover,
                                  alignment: Alignment.topCenter,
                                  errorBuilder: (_, _, _) =>
                                      _AvatarPlaceholder(size: 50),
                                )
                              : _AvatarPlaceholder(size: 50),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                apt.doctor,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: GoogleFonts.inter(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w700,
                                  color: const Color(0xFF101623),
                                ),
                              ),
                              const SizedBox(height: 3),
                              Text(
                                apt.specialty,
                                style: GoogleFonts.inter(
                                  fontSize: 12,
                                  color: const Color(0xFF717784),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const Icon(Icons.chevron_right,
                            size: 16, color: Color(0xFFC8D8E8)),
                      ],
                    ),
                    const SizedBox(height: 10),

                    // Footer
                    Container(
                      padding: const EdgeInsets.only(top: 10),
                      decoration: const BoxDecoration(
                        border: Border(
                            top: BorderSide(color: Color(0xFFF4F7FB))),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Row(
                              children: [
                                const Icon(
                                  Icons.access_time_outlined,
                                  size: 12,
                                  color: Color(0xFFA0AABF),
                                ),
                                const SizedBox(width: 3),
                                Text(
                                  apt.time,
                                  maxLines: 1,
                                  style: GoogleFonts.inter(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                    color: const Color(0xFF5A6472),
                                    height: 1.2,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          if (apt.status == _AptStatus.completed && apt.reviewed)
                            Row(
                              children: List.generate(
                                5,
                                (_) => const Icon(Icons.star,
                                    size: 10,
                                    color: AppColors.primary),
                              ),
                            ),
                          if (apt.status == _AptStatus.completed && !apt.reviewed)
                            Text(
                              'Нет отзыва',
                              style: GoogleFonts.inter(
                                  fontSize: 11,
                                  color: const Color(0xFFA0AABF)),
                            ),
                          const SizedBox(width: 8),
                          Text(
                            apt.price,
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF222222),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      )
          .animate()
          .fadeIn(delay: Duration(milliseconds: index * 60), duration: 280.ms)
          .moveY(begin: 12, end: 0),
    );
  }
}

// ─── Avatar Placeholder ───────────────────────────────────────────────────────

class _AvatarPlaceholder extends StatelessWidget {
  final double size;
  const _AvatarPlaceholder({required this.size});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      color: const Color(0xFFE8F4FD),
      child: Icon(Icons.person, color: AppColors.primary, size: size * 0.5),
    );
  }
}

// ─── Empty State (with pull-to-refresh support) ───────────────────────────────

class _EmptyRefreshable extends StatelessWidget {
  final bool isUpcoming;
  final Future<void> Function()? onRefresh;
  const _EmptyRefreshable({required this.isUpcoming, this.onRefresh});

  @override
  Widget build(BuildContext context) {
    final content = _EmptyState(isUpcoming: isUpcoming);
    if (onRefresh == null) return content;
    return RefreshIndicator(
      color: AppColors.primary,
      onRefresh: onRefresh!,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: SizedBox(
          height: MediaQuery.of(context).size.height * 0.6,
          child: content,
        ),
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  final bool isUpcoming;
  const _EmptyState({required this.isUpcoming});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: const Color(0xFFE8F4FD),
                borderRadius: BorderRadius.circular(22),
              ),
              child: const Icon(Icons.calendar_today_outlined,
                  size: 32, color: AppColors.primary),
            ),
            const SizedBox(height: 16),
            Text(
              isUpcoming
                  ? 'Нет предстоящих записей'
                  : 'Нет прошедших записей',
              style: GoogleFonts.inter(
                fontSize: 17,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF222222),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              isUpcoming
                  ? 'Запишитесь к врачу прямо сейчас'
                  : 'История посещений пуста',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                fontSize: 13,
                color: const Color(0xFFA0AABF),
                height: 1.5,
              ),
            ),
            if (isUpcoming) ...[
              const SizedBox(height: 20),
              Container(
                height: 48,
                decoration: BoxDecoration(
                  gradient: AppColors.primaryGradient,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withAlpha(90),
                      blurRadius: 18,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: TextButton(
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 28),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(24)),
                  ),
                  onPressed: () => context.push('/booking'),
                  child: Text(
                    'Записаться к врачу',
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// ─── Detail Sheet ─────────────────────────────────────────────────────────────

class _DetailSheet extends ConsumerStatefulWidget {
  final _Apt apt;
  final VoidCallback onClose;

  const _DetailSheet({required this.apt, required this.onClose});

  @override
  ConsumerState<_DetailSheet> createState() => _DetailSheetState();
}

class _DetailSheetState extends ConsumerState<_DetailSheet>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<Offset> _slide;
  bool _cancelling = false;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 320),
    );
    _slide = Tween<Offset>(
      begin: const Offset(0, 1),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _ctrl,
      curve: const Cubic(0.32, 0, 0.67, 0),
    ));
    _ctrl.forward();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  Future<void> _showCancelSheet() async {
    final result = await showModalBottomSheet<Object>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _CancelConfirmSheet(aptId: widget.apt.id),
    );
    if (result == null || !mounted) return;

    final reason = result is String ? result : null;

    setState(() => _cancelling = true);
    try {
      await ref
          .read(appointmentsRepositoryProvider)
          .cancelAppointment(widget.apt.id, reason: reason);
      ref.invalidate(upcomingAppointmentsProvider);
      ref.invalidate(pastAppointmentsProvider);
      if (mounted) widget.onClose();
    } catch (e) {
      if (!mounted) return;
      setState(() => _cancelling = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Не удалось отменить запись. Попробуйте ещё раз.',
            style: GoogleFonts.inter(fontSize: 13),
          ),
          backgroundColor: const Color(0xFFE05252),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final apt = widget.apt;
    final cfg = _cfgMap[apt.status]!;

    return Positioned(
      bottom: 0,
      left: 0,
      right: 0,
      child: SlideTransition(
        position: _slide,
        child: Container(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(context).size.height * 0.88,
          ),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            boxShadow: [
              BoxShadow(
                color: Color(0x26000000),
                blurRadius: 40,
                offset: Offset(0, -8),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Handle
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 12),
                child: Center(
                  child: Container(
                    width: 36,
                    height: 4,
                    decoration: BoxDecoration(
                      color: const Color(0xFFE0E7EF),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
              ),

              // Sheet header
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 14),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        'Детали записи',
                        style: GoogleFonts.inter(
                          fontSize: 17,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF101623),
                        ),
                      ),
                    ),
                    GestureDetector(
                      onTap: widget.onClose,
                      child: Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(
                          color: const Color(0xFFF4F8FB),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.close,
                            size: 16, color: Color(0xFF717784)),
                      ),
                    ),
                  ],
                ),
              ),

              Flexible(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Doctor card
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF7F9FC),
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(
                              color: const Color(0xFFEEF2F7)),
                        ),
                        child: Row(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(16),
                              child: apt.avatar.isNotEmpty
                                  ? Image.network(
                                      apt.avatar,
                                      width: 56,
                                      height: 56,
                                      fit: BoxFit.cover,
                                      alignment: Alignment.topCenter,
                                      errorBuilder: (_, _, _) =>
                                          _AvatarPlaceholder(size: 56),
                                    )
                                  : _AvatarPlaceholder(size: 56),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment:
                                    CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    apt.doctor,
                                    style: GoogleFonts.inter(
                                      fontSize: 15,
                                      fontWeight: FontWeight.w700,
                                      color: const Color(0xFF101623),
                                      height: 1.3,
                                    ),
                                  ),
                                  const SizedBox(height: 3),
                                  Text(
                                    apt.specialty,
                                    style: GoogleFonts.inter(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w500,
                                      color: AppColors.primary,
                                    ),
                                  ),
                                  const SizedBox(height: 6),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 10, vertical: 3),
                                    decoration: BoxDecoration(
                                      color: cfg.bg,
                                      borderRadius:
                                          BorderRadius.circular(20),
                                    ),
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(cfg.icon,
                                            size: 11, color: cfg.color),
                                        const SizedBox(width: 5),
                                        Text(
                                          cfg.label,
                                          style: GoogleFonts.inter(
                                            fontSize: 10,
                                            fontWeight: FontWeight.w600,
                                            color: cfg.color,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Details list
                      Container(
                        decoration: BoxDecoration(
                          color: const Color(0xFFF7F9FC),
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(
                              color: const Color(0xFFEEF2F7)),
                        ),
                        child: Column(
                          children: [
                            _DetailRow(
                              icon: Icons.calendar_today_outlined,
                              label: 'Дата',
                              value: apt.date,
                              isFirst: true,
                            ),
                            _DetailRow(
                              icon: Icons.access_time_outlined,
                              label: 'Время',
                              value: apt.time,
                            ),
                            _DetailRow(
                              icon: Icons.medical_services_outlined,
                              label: 'Услуга',
                              value: apt.service,
                            ),
                            _DetailRow(
                              icon: Icons.room_outlined,
                              label: 'Адрес',
                              value: apt.address,
                            ),
                            _DetailRow(
                              icon: Icons.phone_outlined,
                              label: 'Телефон',
                              value: apt.phone,
                            ),
                            _DetailRow(
                              icon: Icons.receipt_long_outlined,
                              label: 'Стоимость',
                              value: apt.price,
                            ),
                            if (apt.diagnosis != null)
                              _DetailRow(
                                icon: Icons.local_hospital_outlined,
                                label: 'Примечание',
                                value: apt.diagnosis!,
                                isLast: true,
                              ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Actions
                      if (apt.status == _AptStatus.upcoming) ...[
                        _ActionButton(
                          label: 'Перенести запись',
                          icon: Icons.event_repeat_outlined,
                          gradient: AppColors.primaryGradient,
                          textColor: Colors.white,
                          onTap: () {
                            widget.onClose();
                            context.push(
                              '/booking'
                              '?service=${widget.apt.serviceSlug}'
                              '&doctor=${widget.apt.doctorSlug}'
                              '&appointmentId=${widget.apt.id}',
                            );
                          },
                        ),
                        const SizedBox(height: 10),
                        if (apt.canCancel)
                          _ActionButton(
                            label: _cancelling ? 'Отмена...' : 'Отменить запись',
                            gradient: null,
                            textColor: const Color(0xFFE05252),
                            border: const Color(0xFFE05252),
                            onTap: _cancelling ? null : () => _showCancelSheet(),
                          ),
                      ],
                      if (apt.status == _AptStatus.completed &&
                          apt.canReview &&
                          !apt.reviewed)
                        _ActionButton(
                          label: 'Оставить отзыв',
                          icon: Icons.message_outlined,
                          gradient: AppColors.primaryGradient,
                          textColor: Colors.white,
                          onTap: () {},
                        ),
                      if (apt.status == _AptStatus.completed &&
                          apt.canReview &&
                          !apt.reviewed)
                        const SizedBox(height: 10),
                      if (apt.status == _AptStatus.completed && apt.reviewed)
                        Container(
                          height: 50,
                          decoration: BoxDecoration(
                            color: const Color(0xFFE8FDF0),
                            borderRadius: BorderRadius.circular(25),
                            border: Border.all(
                                color: const Color(0xFFC0EDD5)),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(
                                Icons.check_circle_outline,
                                size: 17,
                                color: Color(0xFF3DAA6E),
                              ),
                              const SizedBox(width: 8),
                              Text(
                                'Отзыв оставлен',
                                style: GoogleFonts.inter(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w500,
                                  color: const Color(0xFF2E7D50),
                                ),
                              ),
                            ],
                          ),
                        ),
                      if (apt.status == _AptStatus.completed && apt.reviewed)
                        const SizedBox(height: 10),
                      if (apt.status == _AptStatus.completed)
                        _ActionButton(
                          label: 'Записаться снова',
                          icon: Icons.refresh_outlined,
                          gradient: AppColors.primaryGradient,
                          textColor: Colors.white,
                          onTap: () {
                            widget.onClose();
                            context.push(_bookAgainBookingPath(apt));
                          },
                        ),
                      if (apt.status == _AptStatus.cancelled)
                        _ActionButton(
                          label: 'Записаться снова',
                          icon: Icons.refresh_outlined,
                          gradient: AppColors.primaryGradient,
                          textColor: Colors.white,
                          onTap: () {
                            widget.onClose();
                            context.push(_bookAgainBookingPath(apt));
                          },
                        ),
                    ],
                  ),
                ),
              ),
              SafeArea(
                top: false,
                child: const SizedBox(height: 8),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final bool isFirst;
  final bool isLast;

  const _DetailRow({
    required this.icon,
    required this.label,
    required this.value,
    this.isFirst = false,
    this.isLast = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        border: isLast
            ? null
            : const Border(
                bottom: BorderSide(color: Color(0xFFEEF2F7)),
              ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(
              color: const Color(0xFFE8F4FD),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, size: 15, color: AppColors.primary),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    color: const Color(0xFFA0AABF),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: const Color(0xFF222222),
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ActionButton extends StatelessWidget {
  final String label;
  final IconData? icon;
  final LinearGradient? gradient;
  final Color textColor;
  final Color? border;
  final VoidCallback? onTap;

  const _ActionButton({
    required this.label,
    this.icon,
    required this.gradient,
    required this.textColor,
    this.border,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: 50,
        decoration: BoxDecoration(
          gradient: gradient,
          borderRadius: BorderRadius.circular(25),
          border:
              border != null ? Border.all(color: border!, width: 1.5) : null,
          boxShadow: gradient != null
              ? [
                  BoxShadow(
                    color: AppColors.primary.withAlpha(90),
                    blurRadius: 18,
                    offset: const Offset(0, 6),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (icon != null) ...[
              Icon(icon, size: 17, color: textColor),
              const SizedBox(width: 8),
            ],
            Text(
              label,
              style: GoogleFonts.inter(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                color: textColor,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Cancel Confirmation Sheet ────────────────────────────────────────────────

class _CancelConfirmSheet extends StatefulWidget {
  final int aptId;
  const _CancelConfirmSheet({required this.aptId});

  @override
  State<_CancelConfirmSheet> createState() => _CancelConfirmSheetState();
}

class _CancelConfirmSheetState extends State<_CancelConfirmSheet> {
  final _reasonCtrl = TextEditingController();

  @override
  void dispose() {
    _reasonCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).viewInsets.bottom;
    return Container(
      padding: EdgeInsets.fromLTRB(20, 20, 20, 24 + bottom),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(
                color: const Color(0xFFE0E7EF),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: const Color(0xFFFDE8E8),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.cancel_outlined,
                    size: 20, color: Color(0xFFE05252)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'Отменить запись?',
                  style: GoogleFonts.inter(
                    fontSize: 17,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF101623),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            'Это действие нельзя отменить. Вы можете записаться снова в любое время.',
            style: GoogleFonts.inter(
              fontSize: 13,
              color: const Color(0xFF717784),
              height: 1.5,
            ),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _reasonCtrl,
            maxLines: 2,
            maxLength: 500,
            decoration: InputDecoration(
              hintText: 'Причина отмены (необязательно)',
              hintStyle: GoogleFonts.inter(
                fontSize: 13,
                color: const Color(0xFFA0AABF),
              ),
              filled: true,
              fillColor: const Color(0xFFF7F9FC),
              contentPadding:
                  const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Color(0xFFEEF2F7)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Color(0xFFEEF2F7)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide:
                    BorderSide(color: AppColors.primary.withAlpha(180)),
              ),
              counterStyle: GoogleFonts.inter(
                  fontSize: 11, color: const Color(0xFFA0AABF)),
            ),
            style: GoogleFonts.inter(fontSize: 13, color: const Color(0xFF222222)),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: () => Navigator.of(context).pop(false),
                  child: Container(
                    height: 50,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF4F8FB),
                      borderRadius: BorderRadius.circular(25),
                    ),
                    child: Center(
                      child: Text(
                        'Назад',
                        style: GoogleFonts.inter(
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF717784),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: GestureDetector(
                  onTap: () => Navigator.of(context)
                      .pop(_reasonCtrl.text.trim().isNotEmpty
                          ? _reasonCtrl.text.trim()
                          : true),
                  child: Container(
                    height: 50,
                    decoration: BoxDecoration(
                      color: const Color(0xFFE05252),
                      borderRadius: BorderRadius.circular(25),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFFE05252).withAlpha(80),
                          blurRadius: 14,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: Center(
                      child: Text(
                        'Подтвердить',
                        style: GoogleFonts.inter(
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
