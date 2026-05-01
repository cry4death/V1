import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/constants/app_colors.dart';
import 'article_model.dart';
import 'blog_providers.dart';

class BlogScreen extends ConsumerStatefulWidget {
  const BlogScreen({super.key});

  @override
  ConsumerState<BlogScreen> createState() => _BlogScreenState();
}

class _BlogScreenState extends ConsumerState<BlogScreen> {
  String? _categorySlug;
  String _search = '';
  final TextEditingController _searchCtrl = TextEditingController();
  Timer? _debounce;

  @override
  void dispose() {
    _debounce?.cancel();
    _searchCtrl.dispose();
    super.dispose();
  }

  void _onSearchChanged(String value) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 350), () {
      if (!mounted) return;
      setState(() => _search = value);
    });
  }

  Future<void> _onRefresh() async {
    final query = BlogQuery(
      categorySlug: _categorySlug,
      search: _search,
    );
    await Future.wait([
      ref.refresh(articleCategoriesProvider.future),
      ref.refresh(blogFilteredProvider(query).future),
    ]);
  }

  @override
  Widget build(BuildContext context) {
    final query = BlogQuery(categorySlug: _categorySlug, search: _search);
    final listAsync = ref.watch(blogFilteredProvider(query));
    final categoriesAsync = ref.watch(articleCategoriesProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF7F9FC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF222222)),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/dashboard');
            }
          },
        ),
        title: Text(
          'Блог о здоровье',
          style: GoogleFonts.inter(
            fontSize: 17,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF222222),
          ),
        ),
        centerTitle: true,
      ),
      body: RefreshIndicator(
        onRefresh: _onRefresh,
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(
            parent: BouncingScrollPhysics(),
          ),
          slivers: [
            SliverToBoxAdapter(
              child: ColoredBox(
                color: Colors.white,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const SizedBox(height: 4),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 0),
                      child: _SearchField(
                        controller: _searchCtrl,
                        onChanged: _onSearchChanged,
                        onClear: () {
                          _searchCtrl.clear();
                          _debounce?.cancel();
                          setState(() => _search = '');
                        },
                      ),
                    ),
                    const SizedBox(height: 10),
                    SizedBox(
                      height: 44,
                      child: categoriesAsync.when(
                        loading: () => const SizedBox.shrink(),
                        error: (_, _) => const SizedBox.shrink(),
                        data: (categories) => _CategoriesTabs(
                          categories: categories,
                          selectedSlug: _categorySlug,
                          onSelected: (slug) =>
                              setState(() => _categorySlug = slug),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],
                ),
              ),
            ),
            listAsync.when(
              loading: () => const SliverFillRemaining(
                hasScrollBody: false,
                child: Padding(
                  padding: EdgeInsets.only(top: 48),
                  child: Center(child: CircularProgressIndicator()),
                ),
              ),
              error: (err, _) => SliverFillRemaining(
                hasScrollBody: false,
                child: _ErrorView(
                  message: err.toString(),
                  onRetry: () => ref.invalidate(blogFilteredProvider(query)),
                ),
              ),
              data: (articles) {
                if (articles.isEmpty) {
                  return SliverFillRemaining(
                    hasScrollBody: false,
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(32, 32, 32, 48),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.search_off,
                              size: 48, color: Color(0xFFA0AABF)),
                          const SizedBox(height: 10),
                          Text(
                            _search.isNotEmpty || _categorySlug != null
                                ? 'Ничего не найдено'
                                : 'Пока нет статей',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.inter(
                                fontSize: 14, color: const Color(0xFF7A8599)),
                          ),
                        ],
                      ),
                    ),
                  );
                }
                return SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        if (index.isOdd) {
                          return const SizedBox(height: 14);
                        }
                        final i = index ~/ 2;
                        return _BlogCard(article: articles[i]);
                      },
                      childCount: articles.isEmpty
                          ? 0
                          : 2 * articles.length - 1,
                    ),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _SearchField extends StatelessWidget {
  final TextEditingController controller;
  final ValueChanged<String> onChanged;
  final VoidCallback onClear;
  const _SearchField({
    required this.controller,
    required this.onChanged,
    required this.onClear,
  });

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      onChanged: onChanged,
      textInputAction: TextInputAction.search,
      style: GoogleFonts.inter(fontSize: 14, color: const Color(0xFF222222)),
      decoration: InputDecoration(
        hintText: 'Поиск статей…',
        hintStyle: GoogleFonts.inter(
            fontSize: 14, color: const Color(0xFFA0AABF)),
        prefixIcon: const Icon(Icons.search, color: Color(0xFF7A8599)),
        suffixIcon: ValueListenableBuilder<TextEditingValue>(
          valueListenable: controller,
          builder: (_, value, _) => value.text.isEmpty
              ? const SizedBox.shrink()
              : IconButton(
                  icon: const Icon(Icons.close, color: Color(0xFF7A8599)),
                  onPressed: onClear,
                ),
        ),
        filled: true,
        fillColor: const Color(0xFFF1F4F9),
        isDense: true,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: AppColors.primary, width: 1.2),
        ),
      ),
    );
  }
}

class _CategoriesTabs extends StatelessWidget {
  final List<ArticleCategoryModel> categories;
  final String? selectedSlug;
  final ValueChanged<String?> onSelected;

  const _CategoriesTabs({
    required this.categories,
    required this.selectedSlug,
    required this.onSelected,
  });

  @override
  Widget build(BuildContext context) {
    final items = <_TabItem>[
      const _TabItem(slug: null, label: 'Все'),
      for (final c in categories)
        _TabItem(slug: c.slug, label: c.name, count: c.articlesCount),
    ];

    return ListView.separated(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: items.length,
      separatorBuilder: (_, _) => const SizedBox(width: 8),
      itemBuilder: (context, index) {
        final item = items[index];
        final selected = item.slug == selectedSlug;
        return _CategoryChip(
          label: item.label,
          count: item.count,
          selected: selected,
          onTap: () => onSelected(item.slug),
        );
      },
    );
  }
}

class _TabItem {
  final String? slug;
  final String label;
  final int? count;
  const _TabItem({required this.slug, required this.label, this.count});
}

class _CategoryChip extends StatelessWidget {
  final String label;
  final int? count;
  final bool selected;
  final VoidCallback onTap;

  const _CategoryChip({
    required this.label,
    required this.count,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(22),
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: BoxDecoration(
            color: selected ? AppColors.primary : Colors.white,
            borderRadius: BorderRadius.circular(22),
            border: Border.all(
              color: selected ? AppColors.primary : const Color(0xFFE3E8EF),
            ),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                label,
                style: GoogleFonts.inter(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color:
                      selected ? Colors.white : const Color(0xFF3B5066),
                ),
              ),
              if (count != null && count! > 0) ...[
                const SizedBox(width: 6),
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 6, vertical: 1),
                  decoration: BoxDecoration(
                    color: selected
                        ? Colors.white.withAlpha(60)
                        : const Color(0xFFEEF2F7),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '$count',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: selected
                          ? Colors.white
                          : const Color(0xFF6A7A8F),
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _BlogCard extends StatelessWidget {
  final ArticleModel article;
  const _BlogCard({required this.article});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(20),
      onTap: () => context.push('/blog/${article.slug}'),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xFFF0F4F8)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withAlpha(14),
              blurRadius: 14,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius:
                  const BorderRadius.vertical(top: Radius.circular(20)),
              child: Image.network(
                article.coverUrl,
                width: double.infinity,
                height: 160,
                fit: BoxFit.cover,
                errorBuilder: (_, _, _) => Container(
                  height: 160,
                  color: const Color(0xFFE8F4FD),
                  child: const Icon(Icons.image_not_supported_outlined,
                      color: Color(0xFF9FB4CC), size: 36),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if ((article.category ?? '').isNotEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE8F4FD),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        article.category!.toUpperCase(),
                        style: GoogleFonts.inter(
                          fontSize: 10,
                          fontWeight: FontWeight.w700,
                          color: AppColors.primary,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                  const SizedBox(height: 10),
                  Text(
                    article.title,
                    style: GoogleFonts.inter(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF222222),
                      height: 1.3,
                    ),
                  ),
                  if ((article.metaDescription ?? '').isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      article.metaDescription!,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        color: const Color(0xFF6A7A8F),
                        height: 1.35,
                      ),
                    ),
                  ],
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      const Icon(Icons.schedule,
                          size: 13, color: Color(0xFFA0AABF)),
                      const SizedBox(width: 4),
                      Text(
                        article.readTimeLabel,
                        style: GoogleFonts.inter(
                            fontSize: 12, color: const Color(0xFFA0AABF)),
                      ),
                      const SizedBox(width: 10),
                      const Icon(Icons.calendar_today_outlined,
                          size: 12, color: Color(0xFFA0AABF)),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          article.formattedDate,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.inter(
                              fontSize: 12, color: const Color(0xFFA0AABF)),
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
    );
  }
}

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.wifi_off, size: 48, color: Color(0xFFA0AABF)),
            const SizedBox(height: 12),
            Text(
              'Не удалось загрузить статьи',
              style: GoogleFonts.inter(
                  fontSize: 15, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 6),
            Text(
              message,
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                  fontSize: 12, color: const Color(0xFF7A8599)),
            ),
            const SizedBox(height: 14),
            ElevatedButton(
              onPressed: onRetry,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
              ),
              child: const Text('Повторить'),
            ),
          ],
        ),
      ),
    );
  }
}
