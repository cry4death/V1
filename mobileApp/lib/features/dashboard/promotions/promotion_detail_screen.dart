import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import '../../../core/constants/app_colors.dart';
import '../../../core/network/dio_client.dart';
import '../common/html_prose.dart';
import 'promotion_model.dart';
import 'promotions_providers.dart';

class PromotionDetailScreen extends ConsumerWidget {
  final String slug;
  const PromotionDetailScreen({super.key, required this.slug});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(promotionDetailProvider(slug));

    return Scaffold(
      backgroundColor: AppColors.background,
      body: async.when(
        loading: () => const Center(
          child: CircularProgressIndicator(color: AppColors.primary),
        ),
        error: (err, _) => _PromotionErrorBody(
          onRetry: () => ref.invalidate(promotionDetailProvider(slug)),
        ),
        data: (promo) => _PromotionDetailBody(promotion: promo),
      ),
    );
  }
}

class _PromotionErrorBody extends StatelessWidget {
  final VoidCallback onRetry;
  const _PromotionErrorBody({required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 48, color: Color(0xFFA0AABF)),
            const SizedBox(height: 12),
            Text(
              'Не удалось загрузить акцию',
              style: GoogleFonts.inter(fontSize: 15, fontWeight: FontWeight.w600),
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

class _PromotionDetailBody extends StatelessWidget {
  final PromotionModel promotion;
  const _PromotionDetailBody({required this.promotion});

  String _periodLabel() {
    final fmt = DateFormat('dd.MM.yyyy');
    final s = promotion.startDate;
    final e = promotion.endDate;
    if (s == null && e == null) return '';
    final buf = StringBuffer('Действует');
    if (s != null) {
      buf.write(' с ${fmt.format(s)}');
    }
    if (e != null) {
      buf.write(' по ${fmt.format(e)}');
    }
    return buf.toString();
  }

  @override
  Widget build(BuildContext context) {
    final cover = promotion.displayImageUrl;
    final period = _periodLabel();

    return CustomScrollView(
      slivers: [
        SliverAppBar(
          expandedHeight: 220,
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
              child: const Icon(Icons.arrow_back, size: 20, color: Color(0xFF222222)),
            ),
            onPressed: () {
              if (context.canPop()) {
                context.pop();
              } else {
                context.go('/dashboard');
              }
            },
          ),
          flexibleSpace: FlexibleSpaceBar(
            background: cover.isEmpty
                ? ColoredBox(
                    color: const Color(0xFFE8F4FD),
                    child: Icon(
                      Icons.local_offer_outlined,
                      size: 56,
                      color: AppColors.primary.withValues(alpha: 0.45),
                    ),
                  )
                : Image.network(
                    cover,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) => ColoredBox(
                      color: const Color(0xFFE8F4FD),
                      child: Icon(
                        Icons.image_not_supported_outlined,
                        color: AppColors.primary.withValues(alpha: 0.45),
                        size: 48,
                      ),
                    ),
                  ),
          ),
        ),
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 28),
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
                    if ((promotion.category ?? '').isNotEmpty)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE8F4FD),
                          borderRadius: BorderRadius.circular(24),
                          border: Border.all(
                            color: AppColors.primary.withValues(alpha: 0.22),
                          ),
                        ),
                        child: Text(
                          promotion.category!.toUpperCase(),
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            color: AppColors.primary,
                            letterSpacing: 0.65,
                          ),
                        ),
                      ),
                    if ((promotion.category ?? '').isNotEmpty) const SizedBox(height: 14),
                    Text(
                      promotion.title,
                      style: GoogleFonts.inter(
                        fontSize: 24,
                        fontWeight: FontWeight.w800,
                        color: AppColors.textPrimary,
                        height: 1.2,
                        letterSpacing: -0.6,
                      ),
                    ),
                    if (period.isNotEmpty) ...[
                      const SizedBox(height: 14),
                      Row(
                        children: [
                          Icon(
                            Icons.event_rounded,
                            size: 15,
                            color: AppColors.textSecondary.withValues(alpha: 0.9),
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              period,
                              style: GoogleFonts.inter(
                                fontSize: 12.5,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                    if ((promotion.shortDescription ?? '').trim().isNotEmpty) ...[
                      const SizedBox(height: 18),
                      Text(
                        promotion.shortDescription!.trim(),
                        style: GoogleFonts.inter(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w500,
                          color: const Color(0xFF3D5A73),
                          height: 1.5,
                        ),
                      ),
                    ],
                    if (promotion.items.isNotEmpty) ...[
                      const SizedBox(height: 22),
                      Text(
                        'Что входит в программу',
                        style: GoogleFonts.inter(
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                          color: AppColors.primaryDark,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                        decoration: BoxDecoration(
                          color: articleListSurface,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            for (var i = 0; i < promotion.items.length; i++) ...[
                              Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Padding(
                                    padding: const EdgeInsets.only(top: 4),
                                    child: Icon(
                                      Icons.chevron_right_rounded,
                                      size: 18,
                                      color: AppColors.primary.withValues(alpha: 0.85),
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  Expanded(
                                    child: Text(
                                      promotion.items[i],
                                      style: GoogleFonts.inter(
                                        fontSize: 14,
                                        height: 1.55,
                                        color: articleBodyColor,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              if (i < promotion.items.length - 1) const SizedBox(height: 10),
                            ],
                          ],
                        ),
                      ),
                    ],
                    Padding(
                      padding: const EdgeInsets.only(top: 22, bottom: 6),
                      child: Divider(
                        height: 1,
                        thickness: 1,
                        color: AppColors.border.withValues(alpha: 0.65),
                      ),
                    ),
                    Builder(
                      builder: (context) {
                        final raw = (promotion.fullDescription ?? '').trim();
                        if (raw.isEmpty) {
                          return Padding(
                            padding: const EdgeInsets.only(top: 8),
                            child: Text(
                              'Подробное описание скоро появится.',
                              style: GoogleFonts.inter(
                                fontSize: 15,
                                color: articleBodyColor,
                                height: 1.6,
                              ),
                            ),
                          );
                        }
                        final origin = Uri.parse(kApiBaseUrl).origin;
                        final htmlData = stripJustifyFromHtml(
                          preprocessHtmlForFlutter(
                            raw,
                            origin,
                            markLeadParagraph: false,
                          ),
                        );
                        final maxContentW = MediaQuery.sizeOf(context).width - 24 - 40;
                        final inter = GoogleFonts.inter().fontFamily;
                        return Html(
                          data: htmlData,
                          shrinkWrap: true,
                          extensions: [
                            ImageExtension(
                              builder: (ctx) {
                                final s = ctx.attributes['src'] ?? '';
                                return articleInlineImageFromSrc(s, maxContentW);
                              },
                            ),
                          ],
                          style: articleProseStyles(fontFamily: inter),
                        );
                      },
                    ),
                    const SizedBox(height: 20),
                    Text(
                      '* Скидка предоставляется на тарифную часть без учёта стоимости '
                      'лекарственных средств, расходных материалов, изделий медицинского '
                      'назначения и медицинской техники.',
                      style: GoogleFonts.inter(
                        fontSize: 11.5,
                        height: 1.45,
                        color: AppColors.textSecondary.withValues(alpha: 0.92),
                        fontStyle: FontStyle.italic,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}
