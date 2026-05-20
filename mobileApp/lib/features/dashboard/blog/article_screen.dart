import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/constants/app_colors.dart';
import '../../../core/network/dio_client.dart';
import '../common/html_prose.dart';
import 'blog_providers.dart';

class ArticleScreen extends ConsumerWidget {
  final String slug;
  const ArticleScreen({super.key, required this.slug});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(articleDetailProvider(slug));

    return Scaffold(
      backgroundColor: AppColors.background,
      body: async.when(
        loading: () =>
            const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (err, _) => _ErrorBody(
          message: err.toString(),
          onRetry: () => ref.invalidate(articleDetailProvider(slug)),
        ),
        data: (article) {
          return CustomScrollView(
            slivers: [
              SliverAppBar(
                expandedHeight: 240,
                pinned: true,
                backgroundColor: Colors.white,
                foregroundColor: const Color(0xFF222222),
                elevation: 0,
                leading: IconButton(
                  icon: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: Colors.white.withAlpha(220),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.arrow_back,
                        size: 20, color: Color(0xFF222222)),
                  ),
                  onPressed: () {
                    if (context.canPop()) {
                      context.pop();
                    } else {
                      context.go('/blog');
                    }
                  },
                ),
                flexibleSpace: FlexibleSpaceBar(
                  background: Image.network(
                    article.coverUrl,
                    fit: BoxFit.cover,
                    errorBuilder: (_, _, _) => Container(
                      color: const Color(0xFFE8F4FD),
                      child: const Icon(Icons.image_not_supported_outlined,
                          color: Color(0xFF9FB4CC), size: 48),
                    ),
                  ),
                ),
              ),
              SliverToBoxAdapter(
                child: Padding(
                  padding:
                      const EdgeInsets.fromLTRB(12, 10, 12, 28),
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(22),
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.cardShadow.withAlpha(50),
                          blurRadius: 28,
                          offset: const Offset(0, 8),
                        ),
                      ],
                      border: Border.all(color: AppColors.borderLight),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(20, 22, 20, 28),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if ((article.category ?? '').isNotEmpty)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 6),
                              decoration: BoxDecoration(
                                color: const Color(0xFFE8F4FD),
                                borderRadius: BorderRadius.circular(24),
                                border: Border.all(
                                  color:
                                      AppColors.primary.withValues(alpha: 0.22),
                                ),
                              ),
                              child: Text(
                                article.category!.toUpperCase(),
                                style: GoogleFonts.inter(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.primary,
                                  letterSpacing: 0.65,
                                ),
                              ),
                            ),
                          if ((article.category ?? '').isNotEmpty)
                            const SizedBox(height: 14),
                          Text(
                            article.title,
                            style: GoogleFonts.inter(
                              fontSize: 24,
                              fontWeight: FontWeight.w800,
                              color: AppColors.textPrimary,
                              height: 1.2,
                              letterSpacing: -0.6,
                            ),
                          ),
                          const SizedBox(height: 14),
                          Row(
                            children: [
                              if ((article.author ?? '').isNotEmpty) ...[
                                Icon(Icons.person_outline_rounded,
                                    size: 15,
                                    color: AppColors.textSecondary
                                        .withValues(alpha: 0.9)),
                                const SizedBox(width: 5),
                                Flexible(
                                  child: Text(
                                    article.author!,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: GoogleFonts.inter(
                                      fontSize: 12.5,
                                      fontWeight: FontWeight.w500,
                                      color: AppColors.textSecondary,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 14),
                              ],
                              Icon(Icons.schedule_rounded,
                                  size: 14,
                                  color: AppColors.textSecondary
                                      .withValues(alpha: 0.9)),
                              const SizedBox(width: 5),
                              Text(
                                article.readTimeLabel,
                                style: GoogleFonts.inter(
                                  fontSize: 12.5,
                                  color: AppColors.textSecondary,
                                ),
                              ),
                              const SizedBox(width: 14),
                              Icon(Icons.event_rounded,
                                  size: 14,
                                  color: AppColors.textSecondary
                                      .withValues(alpha: 0.9)),
                              const SizedBox(width: 5),
                              Expanded(
                                child: Text(
                                  article.formattedDate,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: GoogleFonts.inter(
                                    fontSize: 12.5,
                                    color: AppColors.textSecondary,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          if ((article.metaDescription ?? '').isNotEmpty) ...[
                            const SizedBox(height: 20),
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                gradient: LinearGradient(
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                  colors: [
                                    const Color(0xFFF3F8FD),
                                    const Color(0xFFE8F4FD)
                                        .withValues(alpha: 0.45),
                                  ],
                                ),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(
                                    color: const Color(0xFFD4E4F4)),
                              ),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Icon(
                                    Icons.format_quote_rounded,
                                    size: 22,
                                    color: AppColors.primary.withValues(alpha: 0.55),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Text(
                                      article.metaDescription!,
                                      style: GoogleFonts.inter(
                                        fontSize: 14,
                                        fontWeight: FontWeight.w500,
                                        color: const Color(0xFF3D5A73),
                                        height: 1.5,
                                        fontStyle: FontStyle.italic,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                          Padding(
                            padding:
                                const EdgeInsets.only(top: 22, bottom: 6),
                            child: Divider(
                              height: 1,
                              thickness: 1,
                              color: AppColors.border.withValues(alpha: 0.65),
                            ),
                          ),
                          Builder(
                            builder: (context) {
                              final raw =
                                  (article.content ?? '').trim();
                              if (raw.isEmpty) {
                                return Padding(
                                  padding:
                                      const EdgeInsets.only(top: 8),
                                  child: Text(
                                    'Содержимое статьи недоступно.',
                                    style: GoogleFonts.inter(
                                      fontSize: 15,
                                      color: articleBodyColor,
                                      height: 1.6,
                                    ),
                                  ),
                                );
                              }
                              final origin =
                                  Uri.parse(resolvedApiBaseUrl).origin;
                              final htmlData = stripJustifyFromHtml(
                                preprocessHtmlForFlutter(
                                  raw,
                                  origin,
                                  markLeadParagraph: true,
                                ),
                              );
                              final maxContentW =
                                  MediaQuery.sizeOf(context).width -
                                      24 -
                                      40;
                              final inter =
                                  GoogleFonts.inter().fontFamily;
                              return Html(
                                data: htmlData,
                                shrinkWrap: true,
                                extensions: [
                                  ImageExtension(
                                    builder: (ctx) {
                                      final s = ctx.attributes['src'] ??
                                          '';
                                      return articleInlineImageFromSrc(
                                        s,
                                        maxContentW,
                                      );
                                    },
                                  ),
                                ],
                                style: articleProseStyles(
                                  fontFamily: inter,
                                ),
                              );
                            },
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}

class _ErrorBody extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorBody({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline,
                size: 48, color: Color(0xFFA0AABF)),
            const SizedBox(height: 12),
            Text('Не удалось загрузить статью',
                style: GoogleFonts.inter(
                    fontSize: 15, fontWeight: FontWeight.w600)),
            const SizedBox(height: 6),
            Text(message,
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                    fontSize: 12, color: const Color(0xFF7A8599))),
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
