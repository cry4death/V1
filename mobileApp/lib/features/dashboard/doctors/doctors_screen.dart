import 'dart:math' show min;

import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/constants/app_colors.dart';
import 'doctor_models.dart';
import 'doctors_providers.dart';

// ─── Main Screen ──────────────────────────────────────────────────────────────

class DoctorsScreen extends ConsumerStatefulWidget {
  const DoctorsScreen({super.key});

  @override
  ConsumerState<DoctorsScreen> createState() => _DoctorsScreenState();
}

class _DoctorsScreenState extends ConsumerState<DoctorsScreen> {
  final _searchCtrl = TextEditingController();
  String _query = '';
  String _specialty = 'Все специализации';
  String _grade = 'Все категории';
  String _ageGroup = 'Все возрасты';

  List<DoctorModel> _filterDoctors(List<DoctorModel> doctors) {
    final q = _query.toLowerCase();
    return doctors.where((d) {
      if (q.isNotEmpty && !d.fullName.toLowerCase().contains(q)) return false;
      if (_specialty != 'Все специализации' && d.specialty != _specialty) {
        return false;
      }
      if (_grade != 'Все категории' && d.gradeName != _grade) return false;
      if (_ageGroup != 'Все возрасты') {
        if (_ageGroup == 'Взрослые' &&
            d.ageGroup != DoctorAgeGroup.adults &&
            d.ageGroup != DoctorAgeGroup.all) {
          return false;
        }
        if (_ageGroup == 'Дети' &&
            d.ageGroup != DoctorAgeGroup.children &&
            d.ageGroup != DoctorAgeGroup.all) {
          return false;
        }
      }
      return true;
    }).toList();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    ref.listen<String?>(doctorsPendingSlugProvider, (prev, next) {
      if (next == null || next.isEmpty) return;
      if (!mounted) return;
      ref.read(doctorsSubNavProvider.notifier).openDoctor(next);
      ref.read(doctorsPendingSlugProvider.notifier).state = null;
    });

    final selectedSlug = ref.watch(doctorsSubNavProvider);
    final async = ref.watch(doctorsListProvider);

    return async.when(
      loading: () => const Scaffold(
        backgroundColor: Color(0xFFF7F9FC),
        body: Center(child: CircularProgressIndicator()),
      ),
      error: (e, _) => Scaffold(
        backgroundColor: const Color(0xFFF7F9FC),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'Не удалось загрузить врачей',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF222222),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  '$e',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    color: const Color(0xFF717784),
                  ),
                ),
                const SizedBox(height: 16),
                TextButton(
                  onPressed: () => ref.invalidate(doctorsListProvider),
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
        ),
      ),
      data: (doctors) {
        final specialties = [
          'Все специализации',
          ...doctors.map((d) => d.specialty).toSet(),
        ];
        final filtered = _filterDoctors(doctors);

        return AnimatedSwitcher(
          duration: const Duration(milliseconds: 280),
          switchInCurve: Curves.easeOut,
          switchOutCurve: Curves.easeIn,
          transitionBuilder: (child, anim) {
            final isDetail = child.key == const ValueKey('detail');
            return SlideTransition(
              position: Tween<Offset>(
                begin: isDetail ? const Offset(1, 0) : const Offset(-1, 0),
                end: Offset.zero,
              ).animate(anim),
              child: child,
            );
          },
          child: selectedSlug == null
              ? _ListScreen(
                  key: const ValueKey('list'),
                  searchCtrl: _searchCtrl,
                  query: _query,
                  onQueryChanged: (v) => setState(() => _query = v),
                  specialty: _specialty,
                  specialties: specialties,
                  onSpecialtyChanged: (v) => setState(() => _specialty = v),
                  grade: _grade,
                  onGradeChanged: (v) => setState(() => _grade = v),
                  ageGroup: _ageGroup,
                  onAgeGroupChanged: (v) => setState(() => _ageGroup = v),
                  filtered: filtered,
                  onRefresh: () async {
                    ref.invalidate(doctorsListProvider);
                    await ref.read(doctorsListProvider.future);
                  },
                  onSelect: (d) =>
                      ref.read(doctorsSubNavProvider.notifier).openDoctor(d.slug),
                )
              : _DetailScreen(
                  key: const ValueKey('detail'),
                  slug: selectedSlug,
                  onBack: () => ref.read(doctorsSubNavProvider.notifier).close(),
                ),
        );
      },
    );
  }
}

// ─── List Screen ──────────────────────────────────────────────────────────────

class _ListScreen extends StatelessWidget {
  final TextEditingController searchCtrl;
  final String query;
  final ValueChanged<String> onQueryChanged;
  final String specialty;
  final List<String> specialties;
  final ValueChanged<String> onSpecialtyChanged;
  final String grade;
  final ValueChanged<String> onGradeChanged;
  final String ageGroup;
  final ValueChanged<String> onAgeGroupChanged;
  final List<DoctorModel> filtered;
  final ValueChanged<DoctorModel> onSelect;
  final Future<void> Function() onRefresh;

  static const _grades = [
    'Все категории',
    'Высшая категория',
    'Первая категория',
    'Вторая категория',
  ];

  static const _ageGroups = ['Все возрасты', 'Взрослые', 'Дети'];

  const _ListScreen({
    super.key,
    required this.searchCtrl,
    required this.query,
    required this.onQueryChanged,
    required this.specialty,
    required this.specialties,
    required this.onSpecialtyChanged,
    required this.grade,
    required this.onGradeChanged,
    required this.ageGroup,
    required this.onAgeGroupChanged,
    required this.filtered,
    required this.onSelect,
    required this.onRefresh,
  });

  @override
  Widget build(BuildContext context) {
    final header = ColoredBox(
      color: Colors.white,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Наши врачи',
              style: GoogleFonts.inter(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF101623),
              ),
            ),
            const SizedBox(height: 2),
            Text(
              'Квалифицированные специалисты с многолетним опытом',
              style: GoogleFonts.inter(
                fontSize: 12,
                color: const Color(0xFF717784),
                height: 1.45,
              ),
            ),
            const SizedBox(height: 12),
            Container(
              height: 44,
              decoration: BoxDecoration(
                color: const Color(0xFFF4F8FB),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFEEF2F7)),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 12),
                    child: Icon(
                      Icons.search,
                      size: 18,
                      color: Color(0xFFA0AABF),
                    ),
                  ),
                  Expanded(
                    child: TextField(
                      controller: searchCtrl,
                      onChanged: onQueryChanged,
                      decoration: InputDecoration(
                        hintText: 'Поиск по имени...',
                        hintStyle: GoogleFonts.inter(
                          fontSize: 14,
                          color: const Color(0xFFA0AABF),
                        ),
                        border: InputBorder.none,
                        isDense: true,
                        contentPadding: EdgeInsets.zero,
                      ),
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        color: const Color(0xFF222222),
                      ),
                    ),
                  ),
                  if (query.isNotEmpty)
                    GestureDetector(
                      onTap: () {
                        searchCtrl.clear();
                        onQueryChanged('');
                      },
                      child: const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 12),
                        child: Icon(
                          Icons.close,
                          size: 16,
                          color: Color(0xFFA0AABF),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 8),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _FilterDropdown(
                    value: specialty,
                    options: specialties,
                    onChanged: onSpecialtyChanged,
                  ),
                  const SizedBox(width: 8),
                  _FilterDropdown(
                    value: grade,
                    options: _grades,
                    onChanged: onGradeChanged,
                  ),
                  const SizedBox(width: 8),
                  _FilterDropdown(
                    value: ageGroup,
                    options: _ageGroups,
                    onChanged: onAgeGroupChanged,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Найдено: ${filtered.length} ${_docCount(filtered.length)}',
              style: GoogleFonts.inter(
                fontSize: 11,
                color: const Color(0xFFA0AABF),
              ),
            ),
            const SizedBox(height: 10),
          ],
        ),
      ),
    );

    return Scaffold(
      backgroundColor: const Color(0xFFF7F9FC),
      resizeToAvoidBottomInset: true,
      body: SafeArea(
        bottom: false,
        child: RefreshIndicator(
          onRefresh: onRefresh,
          child: CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(
              parent: BouncingScrollPhysics(),
            ),
            slivers: [
              SliverToBoxAdapter(child: header),
              if (filtered.isEmpty)
                SliverFillRemaining(
                  hasScrollBody: false,
                  child: _EmptyState(),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(12, 12, 12, 100),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, rowIndex) {
                        final i = rowIndex * 2;
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Expanded(
                                child: _DoctorCard(
                                  doctor: filtered[i],
                                  index: i,
                                  onTap: () => onSelect(filtered[i]),
                                ),
                              ),
                              const SizedBox(width: 12),
                              if (i + 1 < filtered.length)
                                Expanded(
                                  child: _DoctorCard(
                                    doctor: filtered[i + 1],
                                    index: i + 1,
                                    onTap: () => onSelect(filtered[i + 1]),
                                  ),
                                )
                              else
                                const Expanded(child: SizedBox()),
                            ],
                          ),
                        );
                      },
                      childCount: (filtered.length + 1) >> 1,
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  static String _docCount(int n) {
    if (n == 1) return 'врач';
    if (n >= 2 && n <= 4) return 'врача';
    return 'врачей';
  }
}

// ─── Filter Dropdown ──────────────────────────────────────────────────────────

class _FilterDropdown extends StatelessWidget {
  final String value;
  final List<String> options;
  final ValueChanged<String> onChanged;

  const _FilterDropdown({
    required this.value,
    required this.options,
    required this.onChanged,
  });

  bool get _isAll => value.startsWith('Все');

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => _showOptions(context),
      child: Container(
        height: 36,
        padding: const EdgeInsets.symmetric(horizontal: 12),
        decoration: BoxDecoration(
          color: const Color(0xFFF4F8FB),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: _isAll
                ? const Color(0xFFEEF2F7)
                : AppColors.primary.withAlpha(77),
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Text(
              value,
              style: GoogleFonts.inter(
                fontSize: 12,
                fontWeight: _isAll ? FontWeight.w400 : FontWeight.w600,
                color: _isAll
                    ? const Color(0xFFA0AABF)
                    : const Color(0xFF222222),
              ),
            ),
            const SizedBox(width: 4),
            Icon(
              Icons.keyboard_arrow_down_rounded,
              size: 14,
              color: _isAll ? const Color(0xFFA0AABF) : AppColors.primary,
            ),
          ],
        ),
      ),
    );
  }

  void _showOptions(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      isScrollControlled: true,
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.55,
      ),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 8),
            Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(
                color: const Color(0xFFE0E7EF),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 8),
            Flexible(
              child: ListView(
                shrinkWrap: true,
                children: options
                    .map(
                      (opt) => GestureDetector(
                        onTap: () {
                          onChanged(opt);
                          Navigator.pop(context);
                        },
                        child: Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 20,
                            vertical: 14,
                          ),
                          decoration: BoxDecoration(
                            color: opt == value
                                ? const Color(0xFFE8F4FD)
                                : Colors.transparent,
                          ),
                          child: Text(
                            opt,
                            style: GoogleFonts.inter(
                              fontSize: 14,
                              fontWeight: opt == value
                                  ? FontWeight.w600
                                  : FontWeight.w400,
                              color: opt == value
                                  ? AppColors.primary
                                  : const Color(0xFF222222),
                            ),
                          ),
                        ),
                      ),
                    )
                    .toList(),
              ),
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }
}

// ─── Doctor Card ──────────────────────────────────────────────────────────────

class _DoctorCard extends StatelessWidget {
  final DoctorModel doctor;
  final int index;
  final VoidCallback onTap;

  const _DoctorCard({
    required this.doctor,
    required this.index,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child:
          Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFFF0F4F8)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withAlpha(18),
                      blurRadius: 14,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Photo
                      Stack(
                        children: [
                          Image.network(
                            doctor.photo,
                            width: double.infinity,
                            height: 160,
                            fit: BoxFit.cover,
                            alignment: Alignment.topCenter,
                            errorBuilder: (_, _, _) => Container(
                              height: 160,
                              color: const Color(0xFFE8F4FD),
                              child: const Center(
                                child: Icon(
                                  Icons.person,
                                  size: 48,
                                  color: AppColors.primary,
                                ),
                              ),
                            ),
                          ),
                          // Rating badge
                          Positioned(
                            top: 10,
                            right: 10,
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.black.withAlpha(128),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.anchor,
                                    size: 10,
                                    color: Color(0xFF6FB8FF),
                                  ),
                                  const SizedBox(width: 3),
                                  Text(
                                    '${doctor.rating}',
                                    style: GoogleFonts.inter(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: Colors.white,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),

                      Padding(
                        padding: const EdgeInsets.fromLTRB(10, 8, 10, 8),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Text(
                              '${doctor.firstName} ${doctor.patronymic}',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.inter(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF222222),
                                height: 1.25,
                              ),
                            ),
                            Text(
                              doctor.lastName,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.inter(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF222222),
                                height: 1.25,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Text(
                              doctor.specialty,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.inter(
                                fontSize: 11,
                                fontWeight: FontWeight.w500,
                                color: AppColors.primary,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                const Icon(
                                  Icons.access_time_outlined,
                                  size: 12,
                                  color: AppColors.primary,
                                ),
                                const SizedBox(width: 4),
                                Expanded(
                                  child: Text(
                                    'Стаж: ${DoctorModel.experienceYearsLabel(doctor.experience)}',
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: GoogleFonts.inter(
                                      fontSize: 10,
                                      color: const Color(0xFF717784),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 7,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: const Color(0xFFE8F4FD),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.workspace_premium_outlined,
                                    size: 10,
                                    color: AppColors.primary,
                                  ),
                                  const SizedBox(width: 3),
                                  Flexible(
                                    child: Text(
                                      doctor.gradeName,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: GoogleFonts.inter(
                                        fontSize: 9,
                                        fontWeight: FontWeight.w600,
                                        color: AppColors.primary,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 8),
                            SizedBox(
                              width: double.infinity,
                              height: 32,
                              child: DecoratedBox(
                                decoration: BoxDecoration(
                                  gradient: AppColors.primaryGradient,
                                  borderRadius: BorderRadius.circular(16),
                                  boxShadow: [
                                    BoxShadow(
                                      color: AppColors.primary.withAlpha(77),
                                      blurRadius: 10,
                                      offset: const Offset(0, 3),
                                    ),
                                  ],
                                ),
                                child: TextButton(
                                  style: TextButton.styleFrom(
                                    padding: EdgeInsets.zero,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(16),
                                    ),
                                  ),
                                  onPressed: () => context.push(
                                    '/booking?doctor=${doctor.slug}',
                                  ),
                                  child: Text(
                                    'Записаться',
                                    style: GoogleFonts.inter(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: Colors.white,
                                    ),
                                  ),
                                ),
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
              .fadeIn(
                delay: Duration(milliseconds: index * 50),
                duration: 280.ms,
              )
              .moveY(begin: 14, end: 0),
    );
  }
}

// ─── Empty State ──────────────────────────────────────────────────────────────

class _EmptyState extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text('🔍', style: TextStyle(fontSize: 40)),
          const SizedBox(height: 12),
          Text(
            'Врачи не найдены',
            style: GoogleFonts.inter(
              fontSize: 15,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF222222),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Попробуйте изменить фильтры',
            style: GoogleFonts.inter(
              fontSize: 13,
              color: const Color(0xFFA0AABF),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Detail Screen ────────────────────────────────────────────────────────────

class _DetailScreen extends ConsumerStatefulWidget {
  final String slug;
  final VoidCallback onBack;

  const _DetailScreen({super.key, required this.slug, required this.onBack});

  @override
  ConsumerState<_DetailScreen> createState() => _DetailScreenState();
}

class _DetailScreenState extends ConsumerState<_DetailScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final async = ref.watch(doctorDetailProvider(widget.slug));
    return async.when(
      loading: () => Scaffold(
        backgroundColor: const Color(0xFFF7F9FC),
        body: SafeArea(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              IconButton(
                onPressed: widget.onBack,
                icon: const Icon(
                  Icons.arrow_back_ios_new,
                  size: 18,
                  color: Color(0xFF222222),
                ),
              ),
              const Expanded(child: Center(child: CircularProgressIndicator())),
            ],
          ),
        ),
      ),
      error: (e, _) => Scaffold(
        backgroundColor: const Color(0xFFF7F9FC),
        body: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                IconButton(
                  onPressed: widget.onBack,
                  icon: const Icon(
                    Icons.arrow_back_ios_new,
                    size: 18,
                    color: Color(0xFF222222),
                  ),
                ),
                Expanded(
                  child: Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'Не удалось загрузить данные',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.inter(
                            fontWeight: FontWeight.w600,
                            fontSize: 16,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          '$e',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            color: const Color(0xFF717784),
                          ),
                        ),
                        TextButton(
                          onPressed: () =>
                              ref.invalidate(doctorDetailProvider(widget.slug)),
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
                ),
              ],
            ),
          ),
        ),
      ),
      data: (DoctorModel doc) {
        return Scaffold(
          backgroundColor: const Color(0xFFF7F9FC),
          body: SafeArea(
            bottom: false,
            child: NestedScrollView(
              physics: const ClampingScrollPhysics(),
              headerSliverBuilder: (context2, _) => [
                SliverToBoxAdapter(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ── Photo header ──
                      Stack(
                        children: [
                          Image.network(
                            doc.photo,
                            width: double.infinity,
                            height: 320,
                            fit: BoxFit.cover,
                            alignment: Alignment.topCenter,
                            errorBuilder: (_, _, _) => Container(
                              height: 320,
                              color: const Color(0xFFF0F4F8),
                              child: const Center(
                                child: Icon(
                                  Icons.person,
                                  size: 80,
                                  color: Color(0xFF9FB4CC),
                                ),
                              ),
                            ),
                          ),
                          // Gradient overlay
                          Positioned.fill(
                            child: DecoratedBox(
                              decoration: const BoxDecoration(
                                gradient: LinearGradient(
                                  begin: Alignment.topCenter,
                                  end: Alignment.bottomCenter,
                                  colors: [
                                    Color(0x59000000),
                                    Color(0x00000000),
                                    Color(0xB3000000),
                                  ],
                                  stops: [0.0, 0.45, 1.0],
                                ),
                              ),
                            ),
                          ),
                          // Back button
                          Positioned(
                            top: 16,
                            left: 16,
                            child: GestureDetector(
                              onTap: widget.onBack,
                              child: Container(
                                width: 36,
                                height: 36,
                                decoration: BoxDecoration(
                                  color: Colors.white.withAlpha(56),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: const Icon(
                                  Icons.arrow_back_ios_new,
                                  size: 16,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                          ),
                          // Name overlay
                          Positioned(
                            bottom: 14,
                            left: 16,
                            right: 16,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'ВРАЧ ${doc.specialty.toUpperCase()}',
                                  style: GoogleFonts.inter(
                                    fontSize: 10,
                                    color: Colors.white.withAlpha(191),
                                    fontWeight: FontWeight.w600,
                                    letterSpacing: 0.8,
                                  ),
                                ),
                                Text(
                                  doc.lastName,
                                  style: GoogleFonts.inter(
                                    fontSize: 20,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.white,
                                    height: 1.2,
                                  ),
                                ),
                                Text(
                                  '${doc.firstName} ${doc.patronymic}',
                                  style: GoogleFonts.inter(
                                    fontSize: 15,
                                    fontWeight: FontWeight.w500,
                                    color: Colors.white.withAlpha(230),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),

                      // ── Info card ──
                      Container(
                        color: Colors.white,
                        padding: const EdgeInsets.fromLTRB(16, 14, 16, 14),
                        child: Column(
                          children: [
                            _InfoRow(
                              iconBg: const Color(0xFFE8F4FD),
                              icon: Icons.calendar_today_outlined,
                              iconColor: AppColors.primary,
                              text: doc.experienceSummary,
                            ),
                            const SizedBox(height: 8),
                            _InfoRow(
                              iconBg: const Color(0xFFE8F4FD),
                              icon: Icons.workspace_premium_outlined,
                              iconColor: AppColors.primary,
                              text: doc.gradeName,
                            ),
                            if (doc.academicDegree != null &&
                                doc.academicDegree!.isNotEmpty) ...[
                              const SizedBox(height: 8),
                              _InfoRow(
                                iconBg: const Color(0xFFE8F4FD),
                                icon: Icons.school_outlined,
                                iconColor: AppColors.primary,
                                text: doc.academicDegree!,
                              ),
                            ],
                            const SizedBox(height: 8),
                            _InfoRow(
                              iconBg: const Color(0xFFE8F4FD),
                              icon: Icons.room_outlined,
                              iconColor: AppColors.primary,
                              text: doc.address,
                            ),
                            const SizedBox(height: 8),
                            _InfoRow(
                              iconBg: const Color(0xFFE8F4FD),
                              icon: Icons.anchor,
                              iconColor: AppColors.primary,
                              text: 'Рейтинг: ',
                              boldText: doc.rating.toStringAsFixed(2),
                              trailing: _AnchorRow(rating: doc.rating),
                            ),
                            const SizedBox(height: 8),
                            _InfoRow(
                              iconBg: const Color(0xFFE8F4FD),
                              icon: Icons.phone_outlined,
                              iconColor: AppColors.primary,
                              text: doc.phone,
                              textColor: AppColors.primary,
                            ),
                            const SizedBox(height: 14),

                            // CTA buttons
                            Row(
                              children: [
                                Expanded(
                                  child: SizedBox(
                                    height: 44,
                                    child: DecoratedBox(
                                      decoration: BoxDecoration(
                                        gradient: AppColors.primaryGradient,
                                        borderRadius: BorderRadius.circular(22),
                                        boxShadow: [
                                          BoxShadow(
                                            color: AppColors.primary.withAlpha(
                                              90,
                                            ),
                                            blurRadius: 14,
                                            offset: const Offset(0, 4),
                                          ),
                                        ],
                                      ),
                                      child: TextButton(
                                        style: TextButton.styleFrom(
                                          padding: EdgeInsets.zero,
                                          shape: RoundedRectangleBorder(
                                            borderRadius: BorderRadius.circular(
                                              22,
                                            ),
                                          ),
                                        ),
                                        onPressed: () => context.push(
                                          '/booking?doctor=${widget.slug}',
                                        ),
                                        child: Text(
                                          'Записаться',
                                          style: GoogleFonts.inter(
                                            fontSize: 13,
                                            fontWeight: FontWeight.w600,
                                            color: Colors.white,
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: SizedBox(
                                    height: 44,
                                    child: OutlinedButton.icon(
                                      style: OutlinedButton.styleFrom(
                                        side: const BorderSide(
                                          color: AppColors.primary,
                                          width: 1.5,
                                        ),
                                        shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(
                                            22,
                                          ),
                                        ),
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 12,
                                        ),
                                      ),
                                      onPressed: () =>
                                          _showReviewDialog(context, doc),
                                      icon: const Icon(
                                        Icons.message_outlined,
                                        size: 14,
                                        color: AppColors.primary,
                                      ),
                                      label: Text(
                                        'Оставить отзыв',
                                        style: GoogleFonts.inter(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w500,
                                          color: AppColors.primary,
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                // Tab bar
                SliverPersistentHeader(
                  pinned: true,
                  delegate: _TabBarDelegate(
                    TabBar(
                      controller: _tabCtrl,
                      labelColor: AppColors.primary,
                      unselectedLabelColor: const Color(0xFFA0AABF),
                      indicatorColor: AppColors.primary,
                      indicatorWeight: 2,
                      labelStyle: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                      unselectedLabelStyle: GoogleFonts.inter(fontSize: 11),
                      tabs: const [
                        Tab(
                          icon: Icon(Icons.person_outline, size: 16),
                          text: 'О враче',
                        ),
                        Tab(
                          icon: Icon(Icons.medical_services_outlined, size: 16),
                          text: 'Услуги',
                        ),
                        Tab(
                          icon: Icon(Icons.school_outlined, size: 16),
                          text: 'Опыт',
                        ),
                        Tab(
                          icon: Icon(Icons.chat_bubble_outline, size: 16),
                          text: 'Отзывы',
                        ),
                      ],
                    ),
                  ),
                ),
              ],
              body: MediaQuery.removePadding(
                context: context,
                removeTop: true,
                child: TabBarView(
                  controller: _tabCtrl,
                  children: [
                    _AboutTab(doctor: doc),
                    _ServicesTab(doctor: doc),
                    _ExperienceTab(doctor: doc),
                    _ReviewsTab(doctor: doc),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}

// ─── Tab Bar Delegate ─────────────────────────────────────────────────────────

class _TabBarDelegate extends SliverPersistentHeaderDelegate {
  final TabBar tabBar;
  const _TabBarDelegate(this.tabBar);

  @override
  Widget build(BuildContext ctx, double shrinkOffset, bool overlapsContent) =>
      Container(color: Colors.white, child: tabBar);

  @override
  double get maxExtent => tabBar.preferredSize.height;

  @override
  double get minExtent => tabBar.preferredSize.height;

  @override
  bool shouldRebuild(_) => false;
}

// ─── Info Row ─────────────────────────────────────────────────────────────────

class _InfoRow extends StatelessWidget {
  final Color iconBg;
  final IconData icon;
  final Color iconColor;
  final String text;
  final String? boldText;
  final Color? boldColor;
  final Color? textColor;
  final Widget? trailing;

  const _InfoRow({
    required this.iconBg,
    required this.icon,
    required this.iconColor,
    required this.text,
    this.boldText,
    this.boldColor,
    this.textColor,
    this.trailing,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 28,
          height: 28,
          decoration: BoxDecoration(
            color: iconBg,
            borderRadius: BorderRadius.circular(9),
          ),
          child: Icon(icon, size: 14, color: iconColor),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Text.rich(
            TextSpan(
              text: text,
              style: GoogleFonts.inter(
                fontSize: 13,
                color: textColor ?? const Color(0xFF444444),
              ),
              children: boldText != null
                  ? [
                      TextSpan(
                        text: boldText,
                        style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: boldColor ?? const Color(0xFF222222),
                        ),
                      ),
                    ]
                  : [],
            ),
          ),
        ),
        ?trailing,
      ],
    );
  }
}

// ─── Anchor Row ───────────────────────────────────────────────────────────────

class _AnchorRow extends StatelessWidget {
  final double rating;
  const _AnchorRow({required this.rating});

  @override
  Widget build(BuildContext context) {
    final full = rating.floor();
    final empty = 5 - full;
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        ...List.generate(
          full,
          (_) => const Icon(Icons.anchor, size: 12, color: AppColors.primary),
        ),
        ...List.generate(
          empty,
          (_) => const Icon(Icons.anchor, size: 12, color: Color(0xFFC8D8E8)),
        ),
      ],
    );
  }
}

// ─── About Tab ────────────────────────────────────────────────────────────────

class _AboutTab extends StatelessWidget {
  final DoctorModel doctor;
  const _AboutTab({required this.doctor});

  @override
  Widget build(BuildContext context) {
    final bottomPad = MediaQuery.of(context).padding.bottom + 16;
    return ListView(
      physics: const ClampingScrollPhysics(),
      padding: EdgeInsets.fromLTRB(16, 16, 16, bottomPad),
      children: [
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: const Color(0xFFF0F4F8)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withAlpha(13),
                blurRadius: 10,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Text(
            doctor.about,
            style: GoogleFonts.inter(
              fontSize: 13,
              color: const Color(0xFF444444),
              height: 1.65,
            ),
          ),
        ),
      ],
    );
  }
}

// ─── Services Tab ─────────────────────────────────────────────────────────────

class _ServicesTab extends StatelessWidget {
  final DoctorModel doctor;
  const _ServicesTab({required this.doctor});

  @override
  Widget build(BuildContext context) {
    final bottomPad = MediaQuery.of(context).padding.bottom + 16;
    return ListView(
      physics: const ClampingScrollPhysics(),
      padding: EdgeInsets.fromLTRB(16, 16, 16, bottomPad),
      children: [
        Text(
          'Услуги',
          style: GoogleFonts.inter(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF222222),
          ),
        ),
        const SizedBox(height: 12),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: const Color(0xFFF0F4F8)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withAlpha(13),
                blurRadius: 10,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            children: doctor.services.asMap().entries.map((e) {
              final isLast = e.key == doctor.services.length - 1;
              return Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 13,
                ),
                decoration: BoxDecoration(
                  border: isLast
                      ? null
                      : const Border(
                          bottom: BorderSide(color: Color(0xFFF4F7FB)),
                        ),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 32,
                      height: 32,
                      decoration: BoxDecoration(
                        color: const Color(0xFFE8F4FD),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(
                        Icons.medical_services_outlined,
                        size: 15,
                        color: AppColors.primary,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        e.value,
                        style: GoogleFonts.inter(
                          fontSize: 13,
                          color: const Color(0xFF222222),
                          height: 1.35,
                        ),
                      ),
                    ),
                    const Icon(
                      Icons.chevron_right,
                      size: 16,
                      color: Color(0xFFC8D8E8),
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }
}

// ─── Experience Tab ───────────────────────────────────────────────────────────

class _ExperienceTab extends StatelessWidget {
  final DoctorModel doctor;
  const _ExperienceTab({required this.doctor});

  @override
  Widget build(BuildContext context) {
    final bottomPad = MediaQuery.of(context).padding.bottom + 16;
    return ListView(
      physics: const ClampingScrollPhysics(),
      padding: EdgeInsets.fromLTRB(16, 16, 16, bottomPad),
      children: [
        Text(
          'Стаж работы',
          style: GoogleFonts.inter(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF222222),
          ),
        ),
        const SizedBox(height: 12),

        // Work history timeline
        ...doctor.workHistory.asMap().entries.map((e) {
          final w = e.value;
          final isLast = e.key == doctor.workHistory.length - 1;
          return IntrinsicHeight(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: 20,
                  child: Column(
                    children: [
                      Container(
                        width: 12,
                        height: 12,
                        margin: const EdgeInsets.only(top: 5),
                        decoration: BoxDecoration(
                          color: AppColors.primary,
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 2),
                          boxShadow: [
                            BoxShadow(
                              color: AppColors.primary.withAlpha(77),
                              blurRadius: 4,
                            ),
                          ],
                        ),
                      ),
                      if (!isLast)
                        Expanded(
                          child: Center(
                            child: Container(
                              width: 2,
                              color: const Color(0xFF9FC8E8),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Padding(
                    padding: EdgeInsets.only(bottom: isLast ? 0 : 14),
                    child: Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFF0F4F8)),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withAlpha(13),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            w.period,
                            style: GoogleFonts.inter(
                              fontSize: 11,
                              color: AppColors.primary,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            w.role,
                            style: GoogleFonts.inter(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF222222),
                            ),
                          ),
                          Text(
                            w.org,
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              color: const Color(0xFF717784),
                              height: 1.35,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          );
        }),

        const SizedBox(height: 20),
        Text(
          'Повышения квалификации',
          style: GoogleFonts.inter(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF222222),
          ),
        ),
        const SizedBox(height: 12),

        // Education timeline
        ...doctor.education.asMap().entries.map((e) {
          final edu = e.value;
          final isLast = e.key == doctor.education.length - 1;
          return IntrinsicHeight(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: 20,
                  child: Column(
                    children: [
                      Container(
                        width: 12,
                        height: 12,
                        margin: const EdgeInsets.only(top: 5),
                        decoration: BoxDecoration(
                          color: AppColors.primary,
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 2),
                          boxShadow: [
                            BoxShadow(
                              color: AppColors.primary.withAlpha(77),
                              blurRadius: 4,
                            ),
                          ],
                        ),
                      ),
                      if (!isLast)
                        Expanded(
                          child: Center(
                            child: Container(
                              width: 2,
                              color: const Color(0xFF9FC8E8),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Padding(
                    padding: EdgeInsets.only(bottom: isLast ? 0 : 14),
                    child: Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFF0F4F8)),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withAlpha(13),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            edu.year,
                            style: GoogleFonts.inter(
                              fontSize: 11,
                              color: AppColors.primary,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            edu.title,
                            style: GoogleFonts.inter(
                              fontSize: 13,
                              color: const Color(0xFF222222),
                              height: 1.35,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          );
        }),
      ],
    );
  }
}

// ─── Reviews Tab ──────────────────────────────────────────────────────────────

class _ReviewsTab extends StatelessWidget {
  final DoctorModel doctor;
  const _ReviewsTab({required this.doctor});

  @override
  Widget build(BuildContext context) {
    final bottomPad = MediaQuery.of(context).padding.bottom + 16;
    return ListView(
      physics: const ClampingScrollPhysics(),
      padding: EdgeInsets.fromLTRB(16, 16, 16, bottomPad),
      children: [
        // Rating summary
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: const Color(0xFFF0F4F8)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withAlpha(13),
                blurRadius: 10,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Row(
            children: [
              Column(
                children: [
                  Text(
                    doctor.rating.toStringAsFixed(1),
                    style: GoogleFonts.inter(
                      fontSize: 36,
                      fontWeight: FontWeight.w800,
                      color: const Color(0xFF222222),
                      height: 1,
                    ),
                  ),
                  _AnchorRow(rating: doctor.rating),
                  const SizedBox(height: 4),
                  Text(
                    '${doctor.reviews.length} ${_revLabel(doctor.reviews.length)}',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      color: const Color(0xFFA0AABF),
                    ),
                  ),
                ],
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  children: [5, 4, 3, 2, 1].map((star) {
                    final pct = star >= doctor.rating.floor()
                        ? (star == 5 ? 0.85 : 0.15)
                        : 0.0;
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 4),
                      child: Row(
                        children: [
                          Text(
                            '$star',
                            style: GoogleFonts.inter(
                              fontSize: 10,
                              color: const Color(0xFF717784),
                            ),
                          ),
                          const SizedBox(width: 4),
                          const Icon(
                            Icons.anchor,
                            size: 9,
                            color: AppColors.primary,
                          ),
                          const SizedBox(width: 4),
                          Expanded(
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(3),
                              child: LinearProgressIndicator(
                                value: pct,
                                minHeight: 5,
                                backgroundColor: const Color(0xFFF0F4F8),
                                valueColor: const AlwaysStoppedAnimation(
                                  AppColors.primary,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),

        // Reviews list
        ...doctor.reviews.map(
          (rev) => Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: const Color(0xFFF0F4F8)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withAlpha(13),
                  blurRadius: 10,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 36,
                      height: 36,
                      decoration: const BoxDecoration(
                        gradient: AppColors.primaryGradient,
                        shape: BoxShape.circle,
                      ),
                      child: Center(
                        child: Text(
                          rev.initials,
                          style: GoogleFonts.inter(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            rev.age != null
                                ? '${rev.name}, ${rev.age} лет'
                                : rev.name,
                            style: GoogleFonts.inter(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF222222),
                            ),
                          ),
                          Text(
                            rev.date,
                            style: GoogleFonts.inter(
                              fontSize: 11,
                              color: const Color(0xFFA0AABF),
                            ),
                          ),
                        ],
                      ),
                    ),
                    _AnchorRow(rating: 5),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  rev.text,
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    color: const Color(0xFF444444),
                    height: 1.6,
                  ),
                ),
              ],
            ),
          ),
        ),

        // Leave review button
        Builder(
          builder: (ctx) => SizedBox(
            height: 50,
            child: OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: AppColors.primary, width: 1.5),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(25),
                ),
              ),
              onPressed: () => _showReviewDialog(ctx, doctor),
              icon: const Icon(
                Icons.message_outlined,
                size: 16,
                color: AppColors.primary,
              ),
              label: Text(
                'Оставить отзыв',
                style: GoogleFonts.inter(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: AppColors.primary,
                ),
              ),
            ),
          ),
        ),
        const SizedBox(height: 24),
      ],
    );
  }

  static String _revLabel(int n) {
    if (n == 1) return 'отзыв';
    if (n >= 2 && n <= 4) return 'отзыва';
    return 'отзывов';
  }
}

// ─── Review Dialog ────────────────────────────────────────────────────────────

void _showReviewDialog(BuildContext context, DoctorModel doctor) {
  showDialog<void>(
    context: context,
    barrierColor: Colors.black.withAlpha(120),
    useSafeArea: true,
    builder: (_) => _ReviewDialog(doctor: doctor),
  );
}

class _ReviewDialog extends StatefulWidget {
  final DoctorModel doctor;
  const _ReviewDialog({required this.doctor});

  @override
  State<_ReviewDialog> createState() => _ReviewDialogState();
}

class _ReviewDialogState extends State<_ReviewDialog> {
  final _nameCtrl = TextEditingController();
  final _textCtrl = TextEditingController();
  int _rating = 0;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _textCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final mq = MediaQuery.of(context);
    final h = mq.size.height;
    final kb = mq.viewInsets.bottom;
    final pad = mq.padding;
    // Только зона выше клавиатуры (и минус safe area)
    final available = h - kb - pad.vertical - 12;
    final maxH = (available - 4).clamp(200.0, 900.0);
    final maxW = min(400.0, mq.size.width - 40);
    return Dialog(
      alignment: kb > 0 ? Alignment.topCenter : Alignment.center,
      backgroundColor: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      insetPadding: EdgeInsets.fromLTRB(
        20,
        kb > 0 ? pad.top + 8 : 24,
        20,
        12,
      ),
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: maxW, maxHeight: maxH),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
            // ── Title row ──
            Row(
              children: [
                Expanded(
                  child: Text(
                    'Ваш отзыв',
                    style: GoogleFonts.inter(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF101623),
                    ),
                  ),
                ),
                GestureDetector(
                  onTap: () => Navigator.pop(context),
                  child: Container(
                    width: 30,
                    height: 30,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF4F8FB),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.close,
                      size: 16,
                      color: Color(0xFF717784),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),

            // ── Name field ──
            Text(
              'Ваше имя *',
              style: GoogleFonts.inter(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: const Color(0xFF444444),
              ),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _nameCtrl,
              decoration: InputDecoration(
                hintText: 'Ваше имя',
                hintStyle: GoogleFonts.inter(
                  fontSize: 14,
                  color: const Color(0xFFA0AABF),
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 13,
                ),
                filled: true,
                fillColor: const Color(0xFFF8FBFF),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: Color(0xFFD4E4F4)),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(
                    color: AppColors.primary,
                    width: 1.5,
                  ),
                ),
              ),
              style: GoogleFonts.inter(
                fontSize: 14,
                color: const Color(0xFF222222),
              ),
            ),
            const SizedBox(height: 16),

            // ── Rating ──
            Text(
              'Оценка *',
              style: GoogleFonts.inter(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: const Color(0xFF444444),
              ),
            ),
            const SizedBox(height: 8),
            Row(
              children: List.generate(5, (i) {
                final filled = i < _rating;
                return GestureDetector(
                  onTap: () => setState(() => _rating = i + 1),
                  child: Padding(
                    padding: const EdgeInsets.only(right: 10),
                    child: Icon(
                      Icons.anchor,
                      size: 28,
                      color: filled
                          ? AppColors.primary
                          : const Color(0xFFD4E4F4),
                    ),
                  ),
                );
              }),
            ),
            const SizedBox(height: 16),

            // ── Review text ──
            Text(
              'Ваш отзыв *',
              style: GoogleFonts.inter(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: const Color(0xFF444444),
              ),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _textCtrl,
              maxLines: 4,
              decoration: InputDecoration(
                hintText: 'Поделитесь впечатлениями о приёме...',
                hintStyle: GoogleFonts.inter(
                  fontSize: 13,
                  color: const Color(0xFFA0AABF),
                ),
                contentPadding: const EdgeInsets.all(14),
                filled: true,
                fillColor: const Color(0xFFF8FBFF),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: Color(0xFFD4E4F4)),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(
                    color: AppColors.primary,
                    width: 1.5,
                  ),
                ),
              ),
              style: GoogleFonts.inter(
                fontSize: 13,
                color: const Color(0xFF222222),
              ),
            ),
            const SizedBox(height: 20),

            // ── Кнопки: колонка на всю ширину — длинный текст «Отправить отзыв» не ломается
            Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                SizedBox(
                  height: 48,
                  child: OutlinedButton(
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(
                        color: Color(0xFFD4E4F4),
                        width: 1.5,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                    onPressed: () => Navigator.pop(context),
                    child: Text(
                      'Отмена',
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        fontWeight: FontWeight.w500,
                        color: AppColors.primary,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                DecoratedBox(
                  decoration: BoxDecoration(
                    gradient: AppColors.primaryGradient,
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.primary.withAlpha(80),
                        blurRadius: 12,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: TextButton(
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 14),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                    onPressed: () {
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(
                            'Отзыв отправлен! Спасибо.',
                            style: GoogleFonts.inter(fontSize: 13),
                          ),
                          backgroundColor: AppColors.primary,
                          behavior: SnackBarBehavior.floating,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      );
                    },
                    child: Text(
                      'Отправить отзыв',
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                        height: 1.2,
                      ),
                    ),
                  ),
                ),
              ],
            ),
            ],
          ),
        ),
      ),
    );
  }
}
