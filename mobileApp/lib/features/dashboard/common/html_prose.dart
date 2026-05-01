import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:html/dom.dart' as html_dom;
import 'package:html/parser.dart' as html_parser;

import '../../../core/constants/app_colors.dart';

const Color articleBodyColor = Color(0xFF3D4F63);
const Color articleLeadColor = Color(0xFF475569);
const Color articleListSurface = Color(0xFFF4F8FC);

void _markLeadParagraph(html_dom.DocumentFragment frag) {
  final first = frag.querySelector('p');
  if (first != null && !first.classes.contains('article-lead')) {
    first.classes.add('article-lead');
  }
}

Map<String, Style> articleProseStyles({required String? fontFamily}) {
  final base = Style(
    fontFamily: fontFamily,
    color: articleBodyColor,
  );

  return {
    'body': base.merge(Style(
      margin: Margins.zero,
      padding: HtmlPaddings.zero,
      fontSize: FontSize(15),
      lineHeight: const LineHeight(1.68),
      textAlign: TextAlign.start,
    )),
    'p': Style(
      margin: Margins.only(bottom: 14),
      textAlign: TextAlign.start,
      fontSize: FontSize(15),
      lineHeight: const LineHeight(1.68),
      color: articleBodyColor,
      fontFamily: fontFamily,
    ),
    'p.article-lead': Style(
      fontSize: FontSize(16.5),
      lineHeight: const LineHeight(1.58),
      fontWeight: FontWeight.w500,
      color: articleLeadColor,
      margin: Margins.only(bottom: 18),
      fontFamily: fontFamily,
    ),
    'div': Style(
      textAlign: TextAlign.start,
      fontFamily: fontFamily,
    ),
    'h1': Style(
      fontSize: FontSize(22),
      fontWeight: FontWeight.w800,
      color: AppColors.textPrimary,
      margin: Margins.only(top: 8, bottom: 12),
      textAlign: TextAlign.start,
      letterSpacing: -0.4,
      fontFamily: fontFamily,
    ),
    'h2': Style(
      fontSize: FontSize(19),
      fontWeight: FontWeight.w800,
      color: AppColors.primaryDark,
      margin: Margins.only(top: 26, bottom: 12),
      padding: HtmlPaddings.only(left: 14),
      textAlign: TextAlign.start,
      letterSpacing: -0.35,
      lineHeight: const LineHeight(1.3),
      border: Border(
        left: BorderSide(
          color: AppColors.primary.withValues(alpha: 0.85),
          width: 4,
        ),
      ),
      fontFamily: fontFamily,
    ),
    'h3': Style(
      fontSize: FontSize(17),
      fontWeight: FontWeight.w700,
      color: AppColors.textPrimary,
      margin: Margins.only(top: 20, bottom: 10),
      textAlign: TextAlign.start,
      letterSpacing: -0.25,
      lineHeight: const LineHeight(1.35),
      fontFamily: fontFamily,
    ),
    'h4': Style(
      fontSize: FontSize(16),
      fontWeight: FontWeight.w700,
      color: AppColors.textPrimary,
      margin: Margins.only(top: 16, bottom: 8),
      fontFamily: fontFamily,
    ),
    'a': Style(
      color: AppColors.primary,
      fontWeight: FontWeight.w600,
      textDecoration: TextDecoration.underline,
      textDecorationColor: AppColors.primary.withValues(alpha: 0.35),
      fontFamily: fontFamily,
    ),
    'strong': Style(
      fontWeight: FontWeight.w700,
      color: AppColors.textPrimary,
      fontFamily: fontFamily,
    ),
    'b': Style(
      fontWeight: FontWeight.w700,
      color: AppColors.textPrimary,
      fontFamily: fontFamily,
    ),
    'em': Style(
      fontStyle: FontStyle.italic,
      color: articleLeadColor,
      fontFamily: fontFamily,
    ),
    'i': Style(
      fontStyle: FontStyle.italic,
      fontFamily: fontFamily,
    ),
    'ul': Style(
      margin: Margins.only(bottom: 16, top: 4),
      padding: HtmlPaddings.symmetric(vertical: 14, horizontal: 16),
      backgroundColor: articleListSurface,
      listStyleType: ListStyleType.disc,
      listStylePosition: ListStylePosition.outside,
      fontFamily: fontFamily,
    ),
    'ol': Style(
      margin: Margins.only(bottom: 16, top: 4),
      padding: HtmlPaddings.symmetric(vertical: 14, horizontal: 16),
      backgroundColor: articleListSurface,
      listStyleType: ListStyleType.decimal,
      listStylePosition: ListStylePosition.outside,
      fontFamily: fontFamily,
    ),
    'li': Style(
      margin: Margins.only(bottom: 10),
      lineHeight: const LineHeight(1.6),
      textAlign: TextAlign.start,
      color: articleBodyColor,
      fontFamily: fontFamily,
    ),
    'blockquote': Style(
      textAlign: TextAlign.start,
      border: Border(
        left: BorderSide(
          color: AppColors.primary.withValues(alpha: 0.45),
          width: 4,
        ),
      ),
      padding: HtmlPaddings.only(left: 16, top: 14, bottom: 14, right: 12),
      margin: Margins.symmetric(vertical: 18),
      backgroundColor: const Color(0xFFF8FAFC),
      fontStyle: FontStyle.italic,
      color: articleLeadColor,
      fontSize: FontSize(15),
      lineHeight: const LineHeight(1.65),
      fontFamily: fontFamily,
    ),
    'hr': Style(
      margin: Margins.symmetric(vertical: 22),
      border: const Border(
        top: BorderSide(color: Color(0xFFE2E8F0), width: 1),
      ),
    ),
    'code': Style(
      backgroundColor: const Color(0xFFEEF2F7),
      fontFamily: fontFamily,
      fontSize: FontSize(13.5),
      color: AppColors.primaryDark,
    ),
    'pre': Style(
      backgroundColor: const Color(0xFFF1F5F9),
      padding: HtmlPaddings.all(14),
      margin: Margins.symmetric(vertical: 14),
      fontFamily: fontFamily,
      fontSize: FontSize(13),
      lineHeight: const LineHeight(1.5),
      color: const Color(0xFF334155),
    ),
    'figure': Style(
      margin: Margins.symmetric(vertical: 12),
    ),
    'figcaption': Style(
      fontSize: FontSize(12.5),
      color: const Color(0xFF64748B),
      fontStyle: FontStyle.italic,
      textAlign: TextAlign.center,
      margin: Margins.only(top: 8),
      fontFamily: fontFamily,
    ),
  };
}

/// Подготовка HTML: абсолютные URL у `img`, по желанию — стиль первого абзаца как лид.
String preprocessHtmlForFlutter(
  String raw,
  String siteOrigin, {
  bool markLeadParagraph = false,
}) {
  final trimmed = raw.trim();
  if (trimmed.isEmpty) return trimmed;

  final frag = html_parser.parseFragment(trimmed);
  for (final el in frag.querySelectorAll('img')) {
    final src = el.attributes['src'];
    if (src == null) continue;
    final s = src.trim();
    if (s.isEmpty) continue;
    if (s.startsWith('http://') ||
        s.startsWith('https://') ||
        s.startsWith('data:')) {
      continue;
    }
    if (s.startsWith('//')) {
      el.attributes['src'] = 'https:$s';
      continue;
    }
    el.attributes['src'] =
        s.startsWith('/') ? '$siteOrigin$s' : '$siteOrigin/$s';
  }
  if (markLeadParagraph) {
    _markLeadParagraph(frag);
  }
  return frag.outerHtml;
}

String stripJustifyFromHtml(String html) {
  return html.replaceAllMapped(
    RegExp(r'text-align\s*:\s*justify\b', caseSensitive: false),
    (_) => 'text-align:left',
  );
}

Widget articleInlineImageFromSrc(String src, double maxWidth) {
  if (src.isEmpty) return const SizedBox.shrink();

  Widget core;
  if (src.startsWith('data:')) {
    try {
      final uri = Uri.parse(src);
      final data = uri.data;
      if (data != null) {
        core = Image.memory(
          data.contentAsBytes(),
          width: maxWidth,
          fit: BoxFit.fitWidth,
          alignment: Alignment.centerLeft,
          errorBuilder: (_, _, _) => const SizedBox.shrink(),
        );
      } else {
        core = const SizedBox.shrink();
      }
    } catch (_) {
      core = const SizedBox.shrink();
    }
  } else {
    core = Image.network(
      src,
      width: maxWidth,
      fit: BoxFit.fitWidth,
      alignment: Alignment.centerLeft,
      errorBuilder: (_, _, _) => const Icon(
        Icons.broken_image_outlined,
        color: Color(0xFF9FB4CC),
        size: 40,
      ),
    );
  }

  return Padding(
    padding: const EdgeInsets.symmetric(vertical: 12),
    child: Align(
      alignment: Alignment.centerLeft,
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: maxWidth),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(14),
          child: DecoratedBox(
            decoration: BoxDecoration(
              border: Border.all(color: const Color(0xFFE2E8F0)),
              borderRadius: BorderRadius.circular(14),
              color: const Color(0xFFF8FAFC),
            ),
            child: core,
          ),
        ),
      ),
    ),
  );
}
