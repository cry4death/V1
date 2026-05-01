import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:smooth_page_indicator/smooth_page_indicator.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/network/dio_client.dart';
import '../../auth/presentation/controllers/auth_controller.dart';
import '../blog/article_model.dart';
import '../blog/blog_providers.dart';
import '../dashboard_tab_provider.dart';
import '../doctors/doctor_models.dart';
import '../doctors/doctors_providers.dart';
import '../promotions/promotion_model.dart';
import '../promotions/promotions_providers.dart';
import '../services/services_data.dart';
import '../services/services_providers.dart';

const _promoGradientPalettes = [
  [Color(0xE01E5A99), Color(0xD04682B4)],
  [Color(0xE01D7882), Color(0xD03AADB4)],
  [Color(0xE04A2FB4), Color(0xD07B52E0)],
];

List<Color> _promoGradientAt(int index) =>
    _promoGradientPalettes[index % _promoGradientPalettes.length];

String? _promoAbsoluteImageUrl(String raw) {
  final t = raw.trim();
  if (t.isEmpty) return null;
  if (t.startsWith('http://') || t.startsWith('https://')) return t;
  final origin = Uri.parse(kApiBaseUrl).origin;
  if (t.startsWith('/')) return '$origin$t';
  return '$origin/$t';
}

// ─── HomeScreen ───────────────────────────────────────────────────────────────

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  final PageController _promoController = PageController();

  String _greet() {
    final h = DateTime.now().hour;
    if (h < 12) return 'Доброе утро';
    if (h < 18) return 'Добрый день';
    return 'Добрый вечер';
  }

  @override
  void dispose() {
    _promoController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final firstName = ref.watch(authControllerProvider.select(
          (s) => s.firstName.isEmpty ? 'Пользователь' : s.firstName,
        ));
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        bottom: false,
        child: CustomScrollView(
          slivers: [
              // Header
              SliverToBoxAdapter(child: _Header(firstName: firstName, greet: _greet())),

              // Promo slider
              SliverToBoxAdapter(
                child: _PromoSlider(controller: _promoController)
                    .animate()
                    .fadeIn(duration: 400.ms),
              ),

              // Appointments
              SliverToBoxAdapter(
                child: _SectionHeader(
                  title: 'Ближайшие записи',
                  onMore: () => ref
                      .read(dashboardTabIndexProvider.notifier)
                      .state = 3,
                ),
              ),
              SliverToBoxAdapter(
                child: const _UpcomingAppointmentsEmptyState()
                    .animate()
                    .fadeIn(delay: 100.ms),
              ),

              // Categories
              SliverToBoxAdapter(
                child: _SectionHeader(
                  title: 'Категории услуг',
                  onMore: () => ref
                      .read(dashboardTabIndexProvider.notifier)
                      .state = 2,
                ),
              ),
              SliverToBoxAdapter(
                child: _HomeServiceCategories(
                  onCategoryTap: (cat) {
                    ref
                        .read(servicesPendingCategorySlugProvider.notifier)
                        .state = cat.slug;
                    ref.read(dashboardTabIndexProvider.notifier).state = 2;
                  },
                ).animate().fadeIn(delay: 150.ms),
              ),

              // Doctors
              SliverToBoxAdapter(
                child: _SectionHeader(
                  title: 'Наши врачи',
                  onMore: () => ref
                      .read(dashboardTabIndexProvider.notifier)
                      .state = 1,
                ),
              ),
              SliverToBoxAdapter(
                child: const _HomeDoctorsSection()
                    .animate()
                    .fadeIn(delay: 200.ms),
              ),

              // Blog
              SliverToBoxAdapter(
                child: _SectionHeader(
                  title: 'Блог о здоровье',
                  onMore: () => context.push('/blog'),
                ),
              ),
              const SliverToBoxAdapter(child: _BlogList()),

              // Contact banner
              SliverToBoxAdapter(
                child: _ContactBanner().animate().fadeIn(delay: 300.ms),
              ),

              const SliverToBoxAdapter(child: SizedBox(height: 16)),
            ],
          ),
        ),
    );
  }
}


// ─── Header ──────────────────────────────────────────────────────────────────

class _Header extends StatelessWidget {
  final String firstName;
  final String greet;
  const _Header({required this.firstName, required this.greet});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 14),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '$greet 👋',
                    style: GoogleFonts.inter(
                        fontSize: 12, color: const Color(0xFFA0AABF)),
                  ),
                  Text(
                    '$firstName!',
                    style: GoogleFonts.inter(
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF101623),
                    ),
                  ),
                ],
              ),
              Stack(
                children: [
                  Container(
                    width: 42,
                    height: 42,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF4F8FB),
                      borderRadius: BorderRadius.circular(13),
                    ),
                    child: const Icon(Icons.notifications_none_outlined,
                        size: 19, color: AppColors.primary),
                  ),
                  Positioned(
                    top: 9,
                    right: 10,
                    child: Container(
                      width: 7,
                      height: 7,
                      decoration: BoxDecoration(
                        color: const Color(0xFFE05252),
                        shape: BoxShape.circle,
                        border: Border.all(
                            color: const Color(0xFFF4F8FB), width: 1.5),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 14),
          Container(
            height: 46,
            decoration: BoxDecoration(
              color: const Color(0xFFF4F8FB),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFEEF2F7)),
            ),
            child: Row(
              children: [
                const SizedBox(width: 16),
                const Icon(Icons.search, size: 17, color: Color(0xFFA0AABF)),
                const SizedBox(width: 10),
                Text(
                  'Поиск врачей и услуг...',
                  style: GoogleFonts.inter(
                      fontSize: 14, color: const Color(0xFFA0AABF)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Promo Slider ─────────────────────────────────────────────────────────────

class _PromoSlider extends ConsumerStatefulWidget {
  final PageController controller;
  const _PromoSlider({required this.controller});

  @override
  ConsumerState<_PromoSlider> createState() => _PromoSliderState();
}

class _PromoSliderState extends ConsumerState<_PromoSlider> {
  @override
  void initState() {
    super.initState();
    widget.controller.addListener(_onPage);
  }

  @override
  void dispose() {
    widget.controller.removeListener(_onPage);
    super.dispose();
  }

  void _onPage() {
    if (widget.controller.page != null) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final async = ref.watch(homePromotionsProvider);

    return async.when(
      data: (list) {
        if (list.isEmpty) return const SizedBox.shrink();
        return Padding(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
          child: Column(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(22),
                child: SizedBox(
                  height: 160,
                  child: PageView.builder(
                    controller: widget.controller,
                    itemCount: list.length,
                    itemBuilder: (context, index) {
                      final p = list[index];
                      return _PromoCard(
                        promo: p,
                        gradientColors: _promoGradientAt(index),
                      );
                    },
                  ),
                ),
              ),
              const SizedBox(height: 10),
              SmoothPageIndicator(
                controller: widget.controller,
                count: list.length,
                effect: const ExpandingDotsEffect(
                  activeDotColor: AppColors.primary,
                  dotColor: Color(0xFFC8D8E8),
                  dotHeight: 6,
                  dotWidth: 6,
                  expansionFactor: 3.5,
                  spacing: 6,
                ),
              ),
            ],
          ),
        );
      },
      loading: () => Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(22),
          child: SizedBox(
            height: 160,
            child: ColoredBox(
              color: const Color(0xFFEEF2F7),
              child: Center(
                child: SizedBox(
                  width: 28,
                  height: 28,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.5,
                    color: AppColors.primary.withValues(alpha: 0.85),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      error: (e, _) => const SizedBox.shrink(),
    );
  }
}

class _PromoCard extends StatelessWidget {
  final PromotionModel promo;
  final List<Color> gradientColors;
  const _PromoCard({
    required this.promo,
    required this.gradientColors,
  });

  @override
  Widget build(BuildContext context) {
    final categoryLabel = (promo.category?.trim().isNotEmpty ?? false)
        ? promo.category!.trim()
        : 'Акция';
    final endLabel = promo.endDate != null
        ? 'до ${DateFormat('dd.MM.yyyy').format(promo.endDate!)}'
        : null;
    final desc = promo.shortDescription?.trim() ?? '';
    final img = _promoAbsoluteImageUrl(promo.imageUrl);

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () {
          if (promo.slug.isEmpty) return;
          context.push('/promotion/${promo.slug}');
        },
        child: Stack(
          fit: StackFit.expand,
          children: [
            if (img != null)
              Image.network(
                img,
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) => ColoredBox(
                  color: gradientColors.first.withValues(alpha: 0.35),
                ),
              )
            else
              ColoredBox(color: gradientColors.first.withValues(alpha: 0.4)),
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: gradientColors,
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Flexible(
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.white.withAlpha(64),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            categoryLabel,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                      if (endLabel != null) ...[
                        const SizedBox(width: 8),
                        Flexible(
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 10, vertical: 3),
                            decoration: BoxDecoration(
                              color: Colors.white.withAlpha(46),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              endLabel,
                              overflow: TextOverflow.ellipsis,
                              textAlign: TextAlign.end,
                              style: GoogleFonts.inter(
                                fontSize: 11,
                                color: Colors.white.withAlpha(230),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                  Flexible(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          promo.title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.inter(
                            fontSize: 17,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                            height: 1.15,
                          ),
                        ),
                        if (desc.isNotEmpty) ...[
                          const SizedBox(height: 2),
                          Text(
                            desc,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              color: Colors.white.withAlpha(217),
                              height: 1.3,
                            ),
                          ),
                        ],
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.white.withAlpha(56),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                              color: Colors.white.withAlpha(89),
                              width: 1,
                            ),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                'Подробнее',
                                style: GoogleFonts.inter(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                  color: Colors.white,
                                ),
                              ),
                              const SizedBox(width: 6),
                              const Icon(
                                Icons.arrow_forward,
                                size: 13,
                                color: Colors.white,
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
          ],
        ),
      ),
    );
  }
}

// ─── Upcoming appointments (empty; real data later) ──────────────────────────

class _UpcomingAppointmentsEmptyState extends StatelessWidget {
  const _UpcomingAppointmentsEmptyState();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 4),
      child: Text(
        'У вас нет записей',
        style: GoogleFonts.inter(
          fontSize: 13.5,
          fontWeight: FontWeight.w500,
          height: 1.35,
          color: AppColors.textSecondary,
        ),
      ),
    );
  }
}

// ─── Section Header ───────────────────────────────────────────────────────────

class _SectionHeader extends StatelessWidget {
  final String title;
  final VoidCallback? onMore;
  const _SectionHeader({required this.title, this.onMore});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            title,
            style: GoogleFonts.inter(
              fontSize: 17,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF101623),
            ),
          ),
          if (onMore != null)
            GestureDetector(
              onTap: onMore,
              child: Row(
                children: [
                  Text(
                    'Все',
                    style: GoogleFonts.inter(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: AppColors.primary),
                  ),
                  const Icon(Icons.chevron_right,
                      size: 14, color: AppColors.primary),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

// ─── Categories (API) — главная лента ─────────────────────────────────────────

class _HomeServiceCategories extends ConsumerWidget {
  final void Function(ServiceCategory cat) onCategoryTap;

  const _HomeServiceCategories({required this.onCategoryTap});

  static const _icons = <IconData>[
    Icons.medical_services_outlined,
    Icons.favorite_outline,
    Icons.psychology_outlined,
    Icons.content_cut,
    Icons.child_care_outlined,
    Icons.spa_outlined,
    Icons.remove_red_eye_outlined,
    Icons.mood_outlined,
    Icons.science_outlined,
    Icons.vaccines_outlined,
  ];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(serviceDirectionsProvider);

    return async.when(
      loading: () => const SizedBox(
        height: 112,
        child: Center(
          child: SizedBox(
            width: 24,
            height: 24,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: AppColors.primary,
            ),
          ),
        ),
      ),
      error: (err, st) => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20),
        child: SizedBox(
          height: 64,
          child: Row(
            children: [
              Expanded(
                child: Text(
                  'Категории не загрузились',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    color: const Color(0xFF7A8599),
                  ),
                ),
              ),
              TextButton(
                onPressed: () => ref.invalidate(serviceDirectionsProvider),
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
      data: (categories) {
        if (categories.isEmpty) {
          return SizedBox(
            height: 64,
            child: Center(
              child: Text(
                'Пока нет категорий',
                style: GoogleFonts.inter(
                  fontSize: 13,
                  color: const Color(0xFF7A8599),
                ),
              ),
            ),
          );
        }
        return SizedBox(
          height: 112,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            itemCount: categories.length,
            itemBuilder: (context, index) {
              final cat = categories[index];
              final icon = _icons[index % _icons.length];
              return Padding(
                padding: const EdgeInsets.only(right: 12),
                child: Material(
                  color: Colors.transparent,
                  child: InkWell(
                    onTap: () => onCategoryTap(cat),
                    borderRadius: BorderRadius.circular(18),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 54,
                          height: 54,
                          decoration: BoxDecoration(
                            color: const Color(0xFFE8F4FD),
                            borderRadius: BorderRadius.circular(18),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withAlpha(18),
                                blurRadius: 12,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          child: Icon(
                            icon,
                            size: 22,
                            color: const Color(0xFF4682B4),
                          ),
                        ),
                        const SizedBox(height: 4),
                        SizedBox(
                          width: 64,
                          child: Text(
                            cat.label,
                            textAlign: TextAlign.center,
                            style: GoogleFonts.inter(
                              fontSize: 10,
                              fontWeight: FontWeight.w500,
                              height: 1.12,
                              color: const Color(0xFF444444),
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        );
      },
    );
  }
}

// ─── Home doctors (API) ───────────────────────────────────────────────────────

class _HomeDoctorsSection extends ConsumerWidget {
  const _HomeDoctorsSection();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(doctorsListProvider);
    return async.when(
      loading: () => const SizedBox(
        height: 288,
        child: Center(
          child: SizedBox(
            width: 22,
            height: 22,
            child: CircularProgressIndicator(strokeWidth: 2),
          ),
        ),
      ),
      error: (err, _) => SizedBox(
        height: 120,
        child: Center(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  'Не удалось загрузить врачей',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    color: const Color(0xFF7A8599),
                  ),
                ),
                const SizedBox(height: 6),
                TextButton(
                  onPressed: () => ref.invalidate(doctorsListProvider),
                  child: const Text('Повторить'),
                ),
              ],
            ),
          ),
        ),
      ),
      data: (all) {
        if (all.isEmpty) {
          return SizedBox(
            height: 100,
            child: Center(
              child: Text(
                'Пока нет врачей',
                style: GoogleFonts.inter(
                  fontSize: 13,
                  color: const Color(0xFF7A8599),
                ),
              ),
            ),
          );
        }
        return _DoctorsList(
          doctors: all.take(3).toList(),
          onBook: (doc) {
            ref.read(doctorsPendingSlugProvider.notifier).state = doc.slug;
            ref.read(dashboardTabIndexProvider.notifier).state = 1;
          },
        );
      },
    );
  }
}

// ─── Doctors List ─────────────────────────────────────────────────────────────

class _DoctorsList extends StatelessWidget {
  final List<DoctorModel> doctors;
  final void Function(DoctorModel) onBook;

  const _DoctorsList({required this.doctors, required this.onBook});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 288,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 20),
        itemCount: doctors.length,
        itemBuilder: (context, index) {
          final doc = doctors[index];
          final photo = doc.photo.trim();
          final showRating = doc.rating > 0.05;
          final lastName = doc.lastName.trim();
          final initials = doc.nameInitialsLine;
          final expText = doc.homeCardExperienceText;
          const kCardH = 288.0;
          const kPhotoH = 118.0;
          return SizedBox(
            width: 186,
            height: kCardH,
            child: Container(
              margin: const EdgeInsets.only(right: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFFF0F4F8)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withAlpha(16),
                    blurRadius: 16,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              padding: const EdgeInsets.fromLTRB(12, 12, 12, 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  SizedBox(
                    height: kPhotoH,
                    child: Stack(
                      clipBehavior: Clip.none,
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(14),
                          child: photo.isEmpty
                              ? const ColoredBox(
                                  color: Color(0xFFE8F4FD),
                                  child: Center(
                                    child: Icon(
                                      Icons.person,
                                      size: 40,
                                      color: AppColors.primary,
                                    ),
                                  ),
                                )
                              : Image.network(
                                  photo,
                                  width: double.infinity,
                                  height: kPhotoH,
                                  fit: BoxFit.cover,
                                  // Верх портрета (голова) не обрезаем — кадрируем от верха, не от центра.
                                  alignment: Alignment.topCenter,
                                  filterQuality: FilterQuality.medium,
                                  errorBuilder: (context, error, stack) =>
                                      const ColoredBox(
                                    color: Color(0xFFE8F4FD),
                                    child: Center(
                                      child: Icon(
                                        Icons.person,
                                        size: 40,
                                        color: AppColors.primary,
                                      ),
                                    ),
                                  ),
                                ),
                        ),
                        if (showRating)
                          Positioned(
                            top: 6,
                            right: 6,
                            child: DecoratedBox(
                              decoration: BoxDecoration(
                                color: Colors.black.withAlpha(128),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Padding(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 7, vertical: 4),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(
                                      Icons.star_rounded,
                                      size: 13,
                                      color: Color(0xFFFFD166),
                                    ),
                                    const SizedBox(width: 3),
                                    Text(
                                      doc.rating.toStringAsFixed(1),
                                      style: GoogleFonts.inter(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w700,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Expanded(
                    child: SingleChildScrollView(
                      physics: const ClampingScrollPhysics(),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            lastName.isNotEmpty ? lastName : doc.compactName,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.inter(
                              fontSize: 14,
                              fontWeight: FontWeight.w700,
                              height: 1.2,
                              color: const Color(0xFF101623),
                            ),
                          ),
                          if (initials.isNotEmpty) ...[
                            const SizedBox(height: 2),
                            Text(
                              initials,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.inter(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w600,
                                height: 1.2,
                                color: const Color(0xFF3D4555),
                              ),
                            ),
                          ],
                          const SizedBox(height: 4),
                          Text(
                            doc.specialty,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              height: 1.25,
                              color: const Color(0xFF5C6570),
                            ),
                          ),
                          if (expText.isNotEmpty) ...[
                            const SizedBox(height: 2),
                            Text(
                              'Стаж: $expText',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.inter(
                                fontSize: 11,
                                height: 1.25,
                                color: const Color(0xFF8A93A0),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Material(
                    color: Colors.transparent,
                    child: InkWell(
                      onTap: () => onBook(doc),
                      borderRadius: BorderRadius.circular(12),
                      child: Ink(
                        height: 40,
                        decoration: BoxDecoration(
                          gradient: AppColors.primaryGradient,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Center(
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
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}

// ─── Blog List ────────────────────────────────────────────────────────────────

class _BlogList extends ConsumerWidget {
  const _BlogList();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(blogPreviewProvider);

    return async.when(
      loading: () => const SizedBox(
        height: 220,
        child: Center(
          child: SizedBox(
            width: 22,
            height: 22,
            child: CircularProgressIndicator(strokeWidth: 2),
          ),
        ),
      ),
      error: (err, _) => SizedBox(
        height: 140,
        child: Center(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  'Не удалось загрузить блог',
                  style: GoogleFonts.inter(
                      fontSize: 13, color: const Color(0xFF7A8599)),
                ),
                const SizedBox(height: 6),
                TextButton(
                  onPressed: () => ref.invalidate(blogPreviewProvider),
                  child: const Text('Повторить'),
                ),
              ],
            ),
          ),
        ),
      ),
      data: (articles) {
        if (articles.isEmpty) {
          return SizedBox(
            height: 120,
            child: Center(
              child: Text(
                'Пока нет статей',
                style: GoogleFonts.inter(
                    fontSize: 13, color: const Color(0xFF7A8599)),
              ),
            ),
          );
        }
        return SizedBox(
          height: 220,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            itemCount: articles.length,
            itemBuilder: (context, index) {
              final art = articles[index];
              return _BlogPreviewCard(article: art);
            },
          ),
        ).animate().fadeIn(delay: 250.ms);
      },
    );
  }
}

class _BlogPreviewCard extends StatelessWidget {
  final ArticleModel article;
  const _BlogPreviewCard({required this.article});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 200,
      margin: const EdgeInsets.only(right: 12),
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
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(20),
          onTap: () => context.push('/blog/${article.slug}'),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ClipRRect(
                borderRadius:
                    const BorderRadius.vertical(top: Radius.circular(20)),
                child: Image.network(
                  article.coverUrl,
                  width: double.infinity,
                  height: 92,
                  fit: BoxFit.cover,
                  errorBuilder: (_, _, _) => Container(
                    height: 92,
                    color: const Color(0xFFE8F4FD),
                  ),
                ),
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if ((article.category ?? '').isNotEmpty)
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFFE8F4FD),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            article.category!.toUpperCase(),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.inter(
                                fontSize: 9,
                                fontWeight: FontWeight.w700,
                                color: AppColors.primary,
                                letterSpacing: 0.5),
                          ),
                        ),
                      const SizedBox(height: 4),
                      Expanded(
                        child: Text(
                          article.title,
                          style: GoogleFonts.inter(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF222222),
                              height: 1.28),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.menu_book_outlined,
                              size: 11, color: Color(0xFFA0AABF)),
                          const SizedBox(width: 5),
                          Expanded(
                            child: Text(
                              '${article.readTimeLabel} · ${article.formattedDate}',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.inter(
                                  fontSize: 11,
                                  color: const Color(0xFFA0AABF)),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Contact Banner ───────────────────────────────────────────────────────────

class _ContactBanner extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 8),
      child: Container(
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [Color(0xFF1E5A99), Color(0xFF4682B4)],
          ),
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: AppColors.primary.withAlpha(71),
              blurRadius: 24,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        padding: const EdgeInsets.all(20),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Есть вопросы?',
                    style: GoogleFonts.inter(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: Colors.white),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Мы на связи с 8:00 до 20:00',
                    style: GoogleFonts.inter(
                        fontSize: 12,
                        color: Colors.white.withAlpha(204)),
                  ),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 14, vertical: 7),
                    decoration: BoxDecoration(
                      color: Colors.white.withAlpha(51),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                          color: Colors.white.withAlpha(77), width: 1),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.phone_outlined,
                            size: 13, color: Colors.white),
                        const SizedBox(width: 6),
                        Flexible(
                          child: Text(
                            '+375 (17) 215 02 89',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.inter(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: Colors.white),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 12),
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: Colors.white.withAlpha(38),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Icon(Icons.phone, size: 28,
                  color: Colors.white.withAlpha(230)),
            ),
          ],
        ),
      ),
    );
  }
}
