import '../../../core/network/dio_client.dart';

class PromotionModel {
  final int id;
  final String slug;
  final String title;
  final String? shortDescription;
  final String imageUrl;
  final String? category;
  final String? categorySlug;
  final DateTime? startDate;
  final DateTime? endDate;
  final List<String> items;
  final String? fullDescription;

  const PromotionModel({
    required this.id,
    required this.slug,
    required this.title,
    this.shortDescription,
    required this.imageUrl,
    this.category,
    this.categorySlug,
    this.startDate,
    this.endDate,
    this.items = const [],
    this.fullDescription,
  });

  factory PromotionModel.fromApi(Map<String, dynamic> json) {
    DateTime? parseDate(dynamic raw) {
      if (raw is String && raw.isNotEmpty) {
        return DateTime.tryParse(raw);
      }
      return null;
    }

    List<String> items = const [];
    final rawItems = json['items'];
    if (rawItems is List) {
      items = rawItems.map((e) => e.toString()).toList();
    }

    return PromotionModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      slug: json['slug']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      shortDescription: json['short_description']?.toString(),
      imageUrl: json['image_url']?.toString() ?? '',
      category: json['category']?.toString(),
      categorySlug: json['category_slug']?.toString(),
      startDate: parseDate(json['start_date']),
      endDate: parseDate(json['end_date']),
      items: items,
      fullDescription: json['full_description']?.toString(),
    );
  }
}

extension PromotionModelDisplayX on PromotionModel {
  /// Абсолютный URL обложки для [Image.network].
  String get displayImageUrl {
    final u = imageUrl.trim();
    if (u.isEmpty) return '';
    if (u.startsWith('http://') || u.startsWith('https://')) return u;
    final origin = Uri.parse(resolvedApiBaseUrl).origin;
    if (u.startsWith('/')) return '$origin$u';
    return '$origin/$u';
  }
}
