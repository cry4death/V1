import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';

// ─── Types ────────────────────────────────────────────────────────────────────

enum _AptStatus { upcoming, completed, cancelled }

class _Apt {
  final int id;
  final String doctor;
  final String specialty;
  final String date;
  final DateTime dateRaw;
  final String time;
  final String room;
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
    required this.specialty,
    required this.date,
    required this.dateRaw,
    required this.time,
    required this.room,
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
}

// ─── Mock Data ────────────────────────────────────────────────────────────────

const _p1 = 'https://images.unsplash.com/photo-1673865641073-4479f93a7776?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400';
const _p2 = 'https://images.unsplash.com/photo-1645066928295-2506defde470?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400';
const _p3 = 'https://images.unsplash.com/photo-1612531386530-97286d97c2d2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400';
const _p4 = 'https://images.unsplash.com/photo-1659353887804-fc7f9313021a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400';
const _p5 = 'https://images.unsplash.com/photo-1758691463582-11aea602cd4a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400';

final _all = [
  _Apt(
    id: 1,
    doctor: 'Денисевич Юлия Александровна',
    specialty: 'Акушер-гинеколог',
    date: 'Сегодня, 3 апреля 2026',
    dateRaw: DateTime(2026, 4, 3),
    time: '14:30',
    room: 'Кабинет 205',
    address: 'г. Минск, ул. К. Туровского, 14',
    phone: '+375 (17) 215 02 89',
    avatar: _p1,
    status: _AptStatus.upcoming,
    price: '65,00 BYN',
    canCancel: true,
  ),
  _Apt(
    id: 2,
    doctor: 'Сидоров Михаил Андреевич',
    specialty: 'Кардиолог',
    date: '10 апреля 2026',
    dateRaw: DateTime(2026, 4, 10),
    time: '10:00',
    room: 'Кабинет 318',
    address: 'г. Минск, ул. К. Туровского, 14',
    phone: '+375 (17) 215 02 89',
    avatar: _p2,
    status: _AptStatus.upcoming,
    price: '80,00 BYN',
    canCancel: true,
  ),
  _Apt(
    id: 3,
    doctor: 'Новикова Елена Дмитриевна',
    specialty: 'Невролог',
    date: '18 апреля 2026',
    dateRaw: DateTime(2026, 4, 18),
    time: '09:30',
    room: 'Кабинет 112',
    address: 'г. Минск, ул. К. Туровского, 14',
    phone: '+375 (17) 215 02 89',
    avatar: _p5,
    status: _AptStatus.upcoming,
    price: '70,00 BYN',
    canCancel: true,
  ),
  _Apt(
    id: 4,
    doctor: 'Иванов Пётр Сергеевич',
    specialty: 'Терапевт',
    date: '20 марта 2026',
    dateRaw: DateTime(2026, 3, 20),
    time: '11:00',
    room: 'Кабинет 101',
    address: 'г. Минск, ул. К. Туровского, 14',
    phone: '+375 (17) 215 02 89',
    avatar: _p2,
    status: _AptStatus.completed,
    diagnosis: 'ОРВИ, назначено лечение',
    price: '55,00 BYN',
    canReview: true,
    reviewed: false,
  ),
  _Apt(
    id: 5,
    doctor: 'Морозов Дмитрий Игоревич',
    specialty: 'Офтальмолог',
    date: '5 марта 2026',
    dateRaw: DateTime(2026, 3, 5),
    time: '15:45',
    room: 'Кабинет 209',
    address: 'г. Минск, ул. К. Туровского, 14',
    phone: '+375 (17) 215 02 89',
    avatar: _p4,
    status: _AptStatus.completed,
    diagnosis: 'Миопия слабой степени, подбор очков',
    price: '60,00 BYN',
    canReview: true,
    reviewed: true,
  ),
  _Apt(
    id: 6,
    doctor: 'Козлова Анна Викторовна',
    specialty: 'Педиатр',
    date: '12 февраля 2026',
    dateRaw: DateTime(2026, 2, 12),
    time: '10:30',
    room: 'Кабинет 305',
    address: 'г. Минск, ул. К. Туровского, 14',
    phone: '+375 (17) 215 02 89',
    avatar: _p3,
    status: _AptStatus.completed,
    diagnosis: 'Профилактический осмотр, здоров',
    price: '45,00 BYN',
    canReview: true,
    reviewed: true,
  ),
  _Apt(
    id: 7,
    doctor: 'Волкова Ольга Николаевна',
    specialty: 'Дерматолог',
    date: '28 января 2026',
    dateRaw: DateTime(2026, 1, 28),
    time: '13:00',
    room: 'Кабинет 214',
    address: 'г. Минск, ул. К. Туровского, 14',
    phone: '+375 (17) 215 02 89',
    avatar: _p5,
    status: _AptStatus.cancelled,
    price: '70,00 BYN',
  ),
];

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

String? _daysUntil(DateTime d) {
  final now = DateTime.now();
  final today = DateTime(now.year, now.month, now.day);
  final diff = d.difference(today).inDays;
  if (diff == 0) return 'Сегодня';
  if (diff == 1) return 'Завтра';
  if (diff > 1) return 'Через $diff дн.';
  return null;
}

// ─── Main Screen ─────────────────────────────────────────────────────────────

class AppointmentsScreen extends StatefulWidget {
  const AppointmentsScreen({super.key});

  @override
  State<AppointmentsScreen> createState() => _AppointmentsScreenState();
}

class _AppointmentsScreenState extends State<AppointmentsScreen> {
  bool _showUpcoming = true;
  _Apt? _selected;

  List<_Apt> get _upcoming => _all.where((a) => a.status == _AptStatus.upcoming).toList();
  List<_Apt> get _past => _all.where((a) => a.status != _AptStatus.upcoming).toList();
  List<_Apt> get _list => _showUpcoming ? _upcoming : _past;

  void _openDetail(_Apt apt) => setState(() => _selected = apt);
  void _closeDetail() => setState(() => _selected = null);

  @override
  Widget build(BuildContext context) {
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
                  upcomingCount: _upcoming.length,
                  pastCount: _past.length,
                  showUpcoming: _showUpcoming,
                  onTabChanged: (v) => setState(() => _showUpcoming = v),
                ),

                // ── List ──
                Expanded(
                  child: AnimatedSwitcher(
                    duration: const Duration(milliseconds: 220),
                    child: _ListBody(
                      key: ValueKey(_showUpcoming),
                      list: _list,
                      showUpcoming: _showUpcoming,
                      upcoming: _upcoming,
                      past: _past,
                      onSelect: _openDetail,
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
            child: Row(
              children: [
                Expanded(
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
                _BookButton(),
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

class _BookButton extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      height: 36,
      decoration: BoxDecoration(
        gradient: AppColors.primaryGradient,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withAlpha(77),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: TextButton.icon(
        style: TextButton.styleFrom(
          padding: const EdgeInsets.symmetric(horizontal: 14),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(18)),
        ),
        icon: const Icon(Icons.calendar_today_outlined,
            size: 14, color: Colors.white),
        label: Text(
          'Записаться',
          style: GoogleFonts.inter(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        onPressed: () {},
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

  const _ListBody({
    super.key,
    required this.list,
    required this.showUpcoming,
    required this.upcoming,
    required this.past,
    required this.onSelect,
  });

  @override
  Widget build(BuildContext context) {
    if (list.isEmpty) {
      return _EmptyState(isUpcoming: showUpcoming);
    }

    final bottomPad = MediaQuery.of(context).padding.bottom + 80;
    return ListView(
      physics: const ClampingScrollPhysics(),
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
  }
}

// ─── Next Appointment Highlight ───────────────────────────────────────────────

class _NextHighlight extends StatelessWidget {
  final _Apt apt;
  const _NextHighlight({required this.apt});

  @override
  Widget build(BuildContext context) {
    final badge = _daysUntil(apt.dateRaw);
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
                  '${apt.time} · ${apt.room}',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: Colors.white.withAlpha(204),
                  ),
                ),
              ],
            ),
          ),
          if (badge != null)
            Container(
              decoration: BoxDecoration(
                color: Colors.white.withAlpha(38),
                borderRadius: BorderRadius.circular(12),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              child: Column(
                children: [
                  const Icon(Icons.access_time_outlined,
                      size: 14, color: Colors.white),
                  const SizedBox(height: 3),
                  Text(
                    badge,
                    style: GoogleFonts.inter(
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
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
    final badge = apt.status == _AptStatus.upcoming
        ? _daysUntil(apt.dateRaw)
        : null;
    final isToday = badge == 'Сегодня';

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
        child:           ClipRRect(
          borderRadius: BorderRadius.circular(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Дата (полностью, с переносом) + статус
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
                                constraints: const BoxConstraints(maxWidth: 100),
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
                          child: Image.network(
                            apt.avatar,
                            width: 50,
                            height: 50,
                            fit: BoxFit.cover,
                            alignment: Alignment.topCenter,
                            errorBuilder: (_, _, _) => Container(
                              width: 50,
                              height: 50,
                              color: const Color(0xFFE8F4FD),
                              child: const Icon(Icons.person,
                                  color: AppColors.primary),
                            ),
                          ),
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
                                  Icons.room_outlined,
                                  size: 12,
                                  color: Color(0xFFA0AABF),
                                ),
                                const SizedBox(width: 4),
                                Flexible(
                                  child: Text(
                                    apt.room,
                                    maxLines: 1,
                                    softWrap: false,
                                    overflow: TextOverflow.ellipsis,
                                    style: GoogleFonts.inter(
                                      fontSize: 11,
                                      color: const Color(0xFFA0AABF),
                                      height: 1.2,
                                    ),
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 5),
                                  child: Text(
                                    '·',
                                    style: GoogleFonts.inter(
                                      fontSize: 11,
                                      color: const Color(0xFFC8D0DA),
                                    ),
                                  ),
                                ),
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
                          if (badge != null)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 9, vertical: 3),
                              decoration: BoxDecoration(
                                color: badge == 'Сегодня'
                                    ? const Color(0xFFFDE8E8)
                                    : badge == 'Завтра'
                                        ? const Color(0xFFFDF5E8)
                                        : const Color(0xFFF4F7FB),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                badge,
                                style: GoogleFonts.inter(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: badge == 'Сегодня'
                                      ? const Color(0xFFD94F4F)
                                      : badge == 'Завтра'
                                          ? const Color(0xFFD98C3A)
                                          : const Color(0xFF717784),
                                ),
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
                          if (apt.status == _AptStatus.cancelled)
                            Text(
                              'Отменено пациентом',
                              style: GoogleFonts.inter(
                                  fontSize: 11,
                                  color: const Color(0xFFD94F4F)),
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

// ─── Empty State ─────────────────────────────────────────────────────────────

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
                  onPressed: () {},
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

class _DetailSheet extends StatefulWidget {
  final _Apt apt;
  final VoidCallback onClose;

  const _DetailSheet({required this.apt, required this.onClose});

  @override
  State<_DetailSheet> createState() => _DetailSheetState();
}

class _DetailSheetState extends State<_DetailSheet>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<Offset> _slide;

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

  @override
  Widget build(BuildContext context) {
    final apt = widget.apt;
    final cfg = _cfgMap[apt.status]!;
    final badge = apt.status == _AptStatus.upcoming
        ? _daysUntil(apt.dateRaw)
        : null;

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
                padding:
                    const EdgeInsets.fromLTRB(20, 0, 20, 14),
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
                  padding:
                      const EdgeInsets.fromLTRB(20, 0, 20, 24),
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
                              child: Image.network(
                                apt.avatar,
                                width: 56,
                                height: 56,
                                fit: BoxFit.cover,
                                alignment: Alignment.topCenter,
                                errorBuilder: (_, _, _) =>
                                    Container(
                                  width: 56,
                                  height: 56,
                                  color: const Color(0xFFE8F4FD),
                                  child: const Icon(Icons.person,
                                      color: AppColors.primary),
                                ),
                              ),
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
                                    padding:
                                        const EdgeInsets.symmetric(
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
                              icon: Icons.room_outlined,
                              label: 'Кабинет',
                              value: '${apt.room}, ${apt.address}',
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
                                label: 'Диагноз',
                                value: apt.diagnosis!,
                                isLast: true,
                              ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Badge for upcoming
                      if (apt.status == _AptStatus.upcoming && badge != null)
                        Container(
                          margin: const EdgeInsets.only(bottom: 14),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 10),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [
                                Color(0xFFE8F4FD),
                                Color(0xFFD0E8F8)
                              ],
                            ),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(
                                color: const Color(0xFFC8D8E8)),
                          ),
                          child: Row(
                            children: [
                              const Icon(
                                Icons.access_time_outlined,
                                size: 16,
                                color: AppColors.primary,
                              ),
                              const SizedBox(width: 10),
                              Text(
                                badge,
                                style: GoogleFonts.inter(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.primaryDark,
                                ),
                              ),
                            ],
                          ),
                        ),

                      // Actions
                      if (apt.status == _AptStatus.upcoming) ...[
                        _ActionButton(
                          label: 'Перенести запись',
                          gradient: AppColors.primaryGradient,
                          textColor: Colors.white,
                          onTap: () {},
                        ),
                        const SizedBox(height: 10),
                        if (apt.canCancel)
                          _ActionButton(
                            label: 'Отменить запись',
                            gradient: null,
                            textColor: const Color(0xFFE05252),
                            border: const Color(0xFFE05252),
                            onTap: () {},
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
                            mainAxisAlignment:
                                MainAxisAlignment.center,
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
                      if (apt.status == _AptStatus.cancelled)
                        _ActionButton(
                          label: 'Записаться снова',
                          icon: Icons.refresh_outlined,
                          gradient: AppColors.primaryGradient,
                          textColor: Colors.white,
                          onTap: () {},
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
  final VoidCallback onTap;

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
          border: border != null ? Border.all(color: border!, width: 1.5) : null,
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
