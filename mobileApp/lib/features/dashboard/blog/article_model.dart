class ArticleModel {
  final int id;
  final String slug;
  final String title;
  final String? category;
  final String? categorySlug;
  final String? author;
  final DateTime? publishedAt;
  final int readingTime;
  final String? metaDescription;
  final String coverUrl;
  final String? content;

  const ArticleModel({
    required this.id,
    required this.slug,
    required this.title,
    required this.coverUrl,
    required this.readingTime,
    this.category,
    this.categorySlug,
    this.author,
    this.publishedAt,
    this.metaDescription,
    this.content,
  });

  factory ArticleModel.fromApi(Map<String, dynamic> json) {
    DateTime? parsedDate;
    final raw = json['published_at'];
    if (raw is String && raw.isNotEmpty) {
      parsedDate = DateTime.tryParse(raw);
    }

    return ArticleModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      slug: json['slug']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      coverUrl: json['cover_url']?.toString() ?? '',
      readingTime: (json['reading_time'] as num?)?.toInt() ?? 0,
      category: json['category']?.toString(),
      categorySlug: json['category_slug']?.toString(),
      author: json['author']?.toString(),
      publishedAt: parsedDate,
      metaDescription: json['meta_description']?.toString(),
      content: json['content']?.toString(),
    );
  }

  static const _months = [
    'янв', 'фев', 'мар', 'апр', 'мая', 'июн',
    'июл', 'авг', 'сен', 'окт', 'ноя', 'дек',
  ];

  String get formattedDate {
    final d = publishedAt;
    if (d == null) return '';
    return '${d.day} ${_months[d.month - 1]} ${d.year}';
  }

  String get readTimeLabel => readingTime > 0 ? '$readingTime мин' : '—';
}
