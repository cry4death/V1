import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';
import '../widgets/doctor_grid_card.dart';
import 'services_data.dart';
import 'services_providers.dart';

/// Вкладка «Услуги»: список категорий → категория с поиском → карточка услуги.
class ServicesScreen extends ConsumerStatefulWidget {
  const ServicesScreen({super.key});

  @override
  ConsumerState<ServicesScreen> createState() => _ServicesScreenState();
}

class _ServicesScreenState extends ConsumerState<ServicesScreen> {
  final _searchCategories = TextEditingController();
  final _searchServices = TextEditingController();

  String _catQuery = '';
  String _srvQuery = '';

  @override
  void dispose() {
    _searchCategories.dispose();
    _searchServices.dispose();
    super.dispose();
  }

  List<ServiceCategory> _filterCategories(List<ServiceCategory> all) {
    final q = _catQuery.trim().toLowerCase();
    if (q.isEmpty) return all;
    return all.where((c) => c.label.toLowerCase().contains(q)).toList();
  }

  void _tryApplyPendingCategory(AsyncValue<List<ServiceCategory>> async) {
    final pending = ref.read(servicesPendingCategorySlugProvider);
    if (pending == null || pending.isEmpty) return;
    async.whenOrNull(
      data: (list) {
        ServiceCategory? found;
        for (final c in list) {
          if (c.slug == pending) {
            found = c;
            break;
          }
        }
        ref.read(servicesPendingCategorySlugProvider.notifier).state = null;
        if (found == null) return;
        final category = found;
        final nav = ref.read(servicesSubNavProvider);
        if (nav.selectedCategory?.slug == category.slug) return;
        Future.microtask(() {
          if (!mounted) return;
          ref.read(servicesSubNavProvider.notifier).openCategory(category);
          _searchServices.clear();
          setState(() => _srvQuery = '');
        });
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    ref.listen<AsyncValue<List<ServiceCategory>>>(serviceDirectionsProvider, (
      prev,
      next,
    ) {
      _tryApplyPendingCategory(next);
    });
    ref.listen<String?>(servicesPendingCategorySlugProvider, (prev, next) {
      if (next != null && next.isNotEmpty) {
        _tryApplyPendingCategory(ref.read(serviceDirectionsProvider));
      }
    });
    final subNav = ref.watch(servicesSubNavProvider);
    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 280),
      switchInCurve: Curves.easeOut,
      switchOutCurve: Curves.easeIn,
      transitionBuilder: (child, anim) {
        return SlideTransition(
          position: Tween<Offset>(
            begin: const Offset(0.06, 0),
            end: Offset.zero,
          ).animate(
              CurvedAnimation(parent: anim, curve: Curves.easeOutCubic)),
          child: FadeTransition(opacity: anim, child: child),
        );
      },
      child: subNav.selectedService != null
          ? _ServiceDetailPage(
              key: const ValueKey('service'),
              category: subNav.selectedService!.cat,
              offer: subNav.selectedService!.offer,
              onBack: () =>
                  ref.read(servicesSubNavProvider.notifier).popOneStep(),
            )
          : subNav.selectedCategory != null
              ? _CategoryServicesPage(
                  key: const ValueKey('category'),
                  category: subNav.selectedCategory!,
                  searchCtrl: _searchServices,
                  query: _srvQuery,
                  onQueryChanged: (v) => setState(() => _srvQuery = v),
                  onBack: () {
                    ref.read(servicesSubNavProvider.notifier).popOneStep();
                    _searchServices.clear();
                    setState(() => _srvQuery = '');
                  },
                  onServiceTap: (offer) {
                    ref
                        .read(servicesSubNavProvider.notifier)
                        .openService(offer);
                  },
                )
              : Consumer(
                  key: const ValueKey('list'),
                  builder: (context, ref, _) {
                    final async = ref.watch(serviceDirectionsProvider);
                    return async.when(
                      data: (all) => _CategoriesListPage(
                        searchCtrl: _searchCategories,
                        query: _catQuery,
                        onQueryChanged: (v) => setState(() => _catQuery = v),
                        filtered: _filterCategories(all),
                        onSelect: (c) {
                          ref
                              .read(servicesSubNavProvider.notifier)
                              .openCategory(c);
                        },
                      ),
                      loading: () => const _ServiceCategoriesLoadingPage(),
                      error: (err, st) => _ServiceCategoriesErrorPage(
                        onRetry: () {
                          ref.invalidate(serviceDirectionsProvider);
                        },
                      ),
                    );
                  },
                ),
    );
  }
}

// ─── Categories list ──────────────────────────────────────────────────────────

class _CategoriesListPage extends StatelessWidget {
  final TextEditingController searchCtrl;
  final String query;
  final ValueChanged<String> onQueryChanged;
  final List<ServiceCategory> filtered;
  final ValueChanged<ServiceCategory> onSelect;

  const _CategoriesListPage({
    required this.searchCtrl,
    required this.query,
    required this.onQueryChanged,
    required this.filtered,
    required this.onSelect,
  });

  @override
  Widget build(BuildContext context) {
    final bottomPad = MediaQuery.of(context).padding.bottom + 80;

    return Scaffold(
      backgroundColor: const Color(0xFFF7F9FC),
      body: SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              color: Colors.white,
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Категории услуг',
                    style: GoogleFonts.inter(
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF101623),
                    ),
                  ),
                  const SizedBox(height: 12),
                  _SearchBar(
                    controller: searchCtrl,
                    query: query,
                    hint: 'Найти категорию...',
                    onChanged: onQueryChanged,
                  ),
                  const SizedBox(height: 10),
                  Text(
                    '${filtered.length} ${categoryWord(filtered.length)}',
                    style: GoogleFonts.inter(
                      fontSize: 12,
                      color: const Color(0xFFA0AABF),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: filtered.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text('🔍', style: GoogleFonts.inter(fontSize: 40)),
                          const SizedBox(height: 12),
                          Text(
                            'Ничего не найдено',
                            style: GoogleFonts.inter(
                              fontSize: 15,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF222222),
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Попробуйте другой запрос',
                            style: GoogleFonts.inter(
                              fontSize: 13,
                              color: const Color(0xFFA0AABF),
                            ),
                          ),
                        ],
                      ),
                    )
                  : GridView.builder(
                      padding: EdgeInsets.fromLTRB(12, 12, 12, bottomPad),
                      gridDelegate:
                          const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        mainAxisSpacing: 12,
                        crossAxisSpacing: 12,
                        childAspectRatio: 2.1,
                      ),
                      itemCount: filtered.length,
                      itemBuilder: (_, i) {
                        final cat = filtered[i];
                        return _CategoryTile(
                          category: cat,
                          onTap: () => onSelect(cat),
                        ).animate(delay: (i * 25).ms).fadeIn().slideY(
                            begin: 0.06, curve: Curves.easeOut);
                      },
                    ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ServiceCategoriesLoadingPage extends StatelessWidget {
  const _ServiceCategoriesLoadingPage();

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Color(0xFFF7F9FC),
      body: SafeArea(
        child: Center(
          child: CircularProgressIndicator(
            color: Color(0xFF4682B4),
          ),
        ),
      ),
    );
  }
}

class _ServiceCategoriesErrorPage extends StatelessWidget {
  const _ServiceCategoriesErrorPage({required this.onRetry});

  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF7F9FC),
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'Не удалось загрузить категории',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF222222),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  'Проверьте соединение с интернетом и адрес API в настройках.',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    color: const Color(0xFFA0AABF),
                  ),
                ),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: onRetry,
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF4682B4),
                  ),
                  child: Text(
                    'Повторить',
                    style: GoogleFonts.inter(fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _CategoryTile extends StatelessWidget {
  final ServiceCategory category;
  final VoidCallback onTap;

  const _CategoryTile({required this.category, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Ink(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: const Color(0xFFF0F4F8)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withAlpha(13),
                blurRadius: 12,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          padding: const EdgeInsets.fromLTRB(12, 8, 12, 8),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Text(
                  category.label,
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF222222),
                    height: 1.25,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(height: 4),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '${category.count} усл.',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      color: const Color(0xFFA0AABF),
                    ),
                  ),
                  Container(
                    width: 22,
                    height: 22,
                    decoration: BoxDecoration(
                      color: const Color(0xFFE8F4FD),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.chevron_right,
                        size: 14, color: Color(0xFF4682B4)),
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

// ─── Category services ────────────────────────────────────────────────────────

class _CategoryServicesPage extends ConsumerWidget {
  final ServiceCategory category;
  final TextEditingController searchCtrl;
  final String query;
  final ValueChanged<String> onQueryChanged;
  final VoidCallback onBack;
  final ValueChanged<ServiceOffer> onServiceTap;

  const _CategoryServicesPage({
    super.key,
    required this.category,
    required this.searchCtrl,
    required this.query,
    required this.onQueryChanged,
    required this.onBack,
    required this.onServiceTap,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(servicesInDirectionProvider(category.slug));
    final bottomPad = MediaQuery.of(context).padding.bottom + 80;

    final body = async.when(
      data: (all) {
        final q = query.trim().toLowerCase();
        final list = q.isEmpty
            ? all
            : all
                .where((s) =>
                    s.name.toLowerCase().contains(q) ||
                    s.desc.toLowerCase().contains(q))
                .toList();
        if (list.isEmpty) {
          return Center(
            child: Text(
              'Ничего не найдено',
              style: GoogleFonts.inter(
                fontSize: 14,
                color: const Color(0xFFA0AABF),
              ),
            ),
          );
        }
        return ListView.builder(
          padding: EdgeInsets.fromLTRB(12, 12, 12, bottomPad),
          itemCount: list.length,
          physics: const ClampingScrollPhysics(),
          itemBuilder: (_, i) {
            final s = list[i];
            return Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: _ServiceListCard(
                offer: s,
                onTap: () => onServiceTap(s),
              ).animate(delay: (i * 40).ms).fadeIn().slideX(
                  begin: 0.04, curve: Curves.easeOut),
            );
          },
        );
      },
      loading: () => const Center(
        child: CircularProgressIndicator(color: Color(0xFF4682B4)),
      ),
      error: (err, st) => Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'Не удалось загрузить услуги',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                  fontSize: 15,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF222222),
                ),
              ),
              const SizedBox(height: 12),
              TextButton(
                onPressed: () {
                  ref.invalidate(servicesInDirectionProvider(category.slug));
                },
                child: Text(
                  'Повторить',
                  style: GoogleFonts.inter(
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF4682B4),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );

    return Scaffold(
      backgroundColor: const Color(0xFFF7F9FC),
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF4682B4), Color(0xFF1E5A99)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  GestureDetector(
                    onTap: onBack,
                    child: Container(
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: Colors.white.withAlpha(51),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.arrow_back_ios_new,
                          size: 16, color: Colors.white),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    category.label,
                    style: GoogleFonts.inter(
                      fontSize: 20,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    async.maybeWhen(
                      data: (all) =>
                          '${all.length} ${serviceWord(all.length)}',
                      orElse: () =>
                          '${category.count} ${serviceWord(category.count)}',
                    ),
                    style: GoogleFonts.inter(
                      fontSize: 13,
                      color: Colors.white.withAlpha(191),
                    ),
                  ),
                  const SizedBox(height: 14),
                  _SearchBar(
                    controller: searchCtrl,
                    query: query,
                    hint: 'Поиск услуги...',
                    onChanged: onQueryChanged,
                    light: true,
                  ),
                ],
              ),
            ),
            Expanded(child: body),
          ],
        ),
      ),
    );
  }
}

class _ServiceListCard extends StatelessWidget {
  final ServiceOffer offer;
  final VoidCallback onTap;

  const _ServiceListCard({required this.offer, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Container(
          padding: const EdgeInsets.fromLTRB(16, 14, 16, 14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: const Color(0xFFF0F4F8)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withAlpha(15),
                blurRadius: 12,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                offer.name,
                style: GoogleFonts.inter(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF222222),
                  height: 1.35,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                offer.desc,
                style: GoogleFonts.inter(
                  fontSize: 12,
                  color: const Color(0xFF717784),
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    offer.price,
                    style: GoogleFonts.inter(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF4682B4),
                    ),
                  ),
                  DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: AppColors.primaryGradient,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.primary.withAlpha(77),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        onTap: onTap,
                        borderRadius: BorderRadius.circular(20),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 8),
                          child: Text(
                            'Записаться',
                            style: GoogleFonts.inter(
                              fontSize: 12,
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
        ),
      ),
    );
  }
}

// ─── Service detail (всё в одном скролле, без sticky header) ───────────────────

class _ServiceDetailPage extends StatelessWidget {
  final ServiceCategory category;
  final ServiceOffer offer;
  final VoidCallback onBack;

  const _ServiceDetailPage({
    super.key,
    required this.category,
    required this.offer,
    required this.onBack,
  });

  static const _kDefaultIndications =
      'Услуга может быть рекомендована после очной консультации врача с учётом вашего анамнеза и результатов обследований.';
  static const _kDefaultPreparation =
      'Перед процедурой уточните у администратора или лечащего врача индивидуальные рекомендации по подготовке и перечень необходимых анализов.';

  @override
  Widget build(BuildContext context) {
    final about = offer.longAbout ?? offer.desc;
    final indications = offer.indications ?? _kDefaultIndications;
    final preparation = offer.preparation ?? _kDefaultPreparation;
    final bottomPad = MediaQuery.of(context).padding.bottom + 80;

    return Scaffold(
      backgroundColor: const Color(0xFFF7F9FC),
      body: SafeArea(
        bottom: false,
        child: SingleChildScrollView(
          physics: const ClampingScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Шапка с градиентом — уходит при скролле (не sticky)
              Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF4682B4), Color(0xFF1E5A99)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    GestureDetector(
                      onTap: onBack,
                      child: Container(
                        width: 36,
                        height: 36,
                        decoration: BoxDecoration(
                          color: Colors.white.withAlpha(51),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.arrow_back_ios_new,
                            size: 16, color: Colors.white),
                      ),
                    ),
                    const SizedBox(height: 14),
                    Text(
                      offer.name,
                      style: GoogleFonts.inter(
                        fontSize: 20,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                        height: 1.3,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      category.label,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        color: Colors.white.withAlpha(191),
                      ),
                    ),
                  ],
                ),
              ),

              // Цена и CTA
              Container(
                color: Colors.white,
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Стоимость:',
                      style: GoogleFonts.inter(
                        fontSize: 12,
                        color: const Color(0xFF717784),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      offer.price,
                      style: GoogleFonts.inter(
                        fontSize: 24,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF4682B4),
                      ),
                    ),
                    const SizedBox(height: 14),
                    SizedBox(
                      width: double.infinity,
                      child: DecoratedBox(
                        decoration: BoxDecoration(
                          gradient: AppColors.primaryGradient,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: TextButton(
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16)),
                          ),
                          onPressed: () {},
                          child: Text(
                            'Записаться на приём',
                            style: GoogleFonts.inter(
                              fontSize: 15,
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

              const SizedBox(height: 8),

              Container(
                color: Colors.white,
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Об услуге',
                      style: GoogleFonts.inter(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF222222),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      about,
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        color: const Color(0xFF444444),
                        height: 1.6,
                      ),
                    ),
                    const SizedBox(height: 18),
                    Text(
                      'Показания',
                      style: GoogleFonts.inter(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF222222),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      indications,
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        color: const Color(0xFF444444),
                        height: 1.6,
                      ),
                    ),
                    const SizedBox(height: 18),
                    Text(
                      'Подготовка',
                      style: GoogleFonts.inter(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF222222),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      preparation,
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        color: const Color(0xFF444444),
                        height: 1.6,
                      ),
                    ),
                    const SizedBox(height: 18),
                    Text(
                      'Как проходит приём',
                      style: GoogleFonts.inter(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF222222),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'Специалист проводит осмотр, при необходимости назначает дополнительные исследования и составляет план лечения или наблюдения.',
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        color: const Color(0xFF444444),
                        height: 1.6,
                      ),
                    ),
                  ],
                ),
              ),

              Padding(
                padding: const EdgeInsets.fromLTRB(12, 16, 12, 0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Врачи',
                      style: GoogleFonts.inter(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF222222),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Специалисты, оказывающие услуги в клинике',
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        color: const Color(0xFF717784),
                      ),
                    ),
                    const SizedBox(height: 12),
                    GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      padding: EdgeInsets.zero,
                      gridDelegate:
                          const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        mainAxisSpacing: 12,
                        crossAxisSpacing: 12,
                        childAspectRatio: 0.56,
                      ),
                      itemCount: serviceDoctorsSnapshots.length,
                      itemBuilder: (_, i) {
                        final d = serviceDoctorsSnapshots[i];
                        return DoctorGridCard(
                          lastName: d.lastName,
                          firstName: d.firstName,
                          patronymic: d.patronymic,
                          specialty: d.specialty,
                          experienceYears: d.experienceYears,
                          gradeName: d.gradeName,
                          rating: d.rating,
                          photoUrl: d.photoUrl,
                          onTap: () {},
                          onBook: () {},
                        );
                      },
                    ),
                  ],
                ),
              ),

              SizedBox(height: bottomPad),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Search bar ───────────────────────────────────────────────────────────────

class _SearchBar extends StatelessWidget {
  final TextEditingController controller;
  final String query;
  final String hint;
  final ValueChanged<String> onChanged;
  final bool light;

  const _SearchBar({
    required this.controller,
    required this.query,
    required this.hint,
    required this.onChanged,
    this.light = false,
  });

  @override
  Widget build(BuildContext context) {
    final bg = light ? Colors.white.withAlpha(46) : const Color(0xFFF4F8FB);
    final border = light ? Colors.white.withAlpha(77) : const Color(0xFFEEF2F7);
    final icon = light ? Colors.white70 : const Color(0xFFA0AABF);
    final hintStyle = light
        ? GoogleFonts.inter(fontSize: 14, color: Colors.white70)
        : GoogleFonts.inter(fontSize: 14, color: const Color(0xFFA0AABF));

    return Container(
      height: 44,
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: border),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14),
      child: Row(
        children: [
          Icon(Icons.search, size: 18, color: icon),
          const SizedBox(width: 10),
          Expanded(
            child: TextField(
              controller: controller,
              onChanged: onChanged,
              style: GoogleFonts.inter(
                fontSize: 14,
                color: light ? Colors.white : const Color(0xFF222222),
              ),
              decoration: InputDecoration(
                hintText: hint,
                hintStyle: hintStyle,
                border: InputBorder.none,
                isDense: true,
                contentPadding: EdgeInsets.zero,
              ),
            ),
          ),
          if (query.isNotEmpty)
            GestureDetector(
              onTap: () {
                controller.clear();
                onChanged('');
              },
              child: Icon(Icons.close, size: 16, color: icon),
            ),
        ],
      ),
    );
  }
}
