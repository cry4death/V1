import 'package:dio/dio.dart';

import 'article_model.dart';

class ArticleCategoryModel {
  final int id;
  final String slug;
  final String name;
  final int articlesCount;

  const ArticleCategoryModel({
    required this.id,
    required this.slug,
    required this.name,
    required this.articlesCount,
  });

  factory ArticleCategoryModel.fromApi(Map<String, dynamic> json) {
    return ArticleCategoryModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      slug: json['slug']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      articlesCount: (json['articles_count'] as num?)?.toInt() ?? 0,
    );
  }
}

class BlogRepository {
  BlogRepository(this._dio);

  final Dio _dio;

  Future<List<ArticleModel>> fetchList({
    String? categorySlug,
    String? search,
    int? limit,
  }) async {
    final q = <String, dynamic>{};
    if (categorySlug != null && categorySlug.isNotEmpty) {
      q['category'] = categorySlug;
    }
    if (search != null && search.isNotEmpty) {
      q['q'] = search;
    }
    if (limit != null && limit > 0) {
      q['limit'] = limit;
    }

    final res = await _dio.get<Map<String, dynamic>>(
      '/articles',
      queryParameters: q.isEmpty ? null : q,
    );

    final data = res.data?['data'];
    if (data is! List) {
      throw StateError('Invalid API response: expected data array');
    }

    return data
        .map((e) => ArticleModel.fromApi(Map<String, dynamic>.from(e as Map)))
        .toList();
  }

  Future<List<ArticleCategoryModel>> fetchCategories() async {
    final res = await _dio.get<Map<String, dynamic>>('/article-categories');
    final data = res.data?['data'];
    if (data is! List) {
      throw StateError('Invalid API response: expected data array');
    }
    return data
        .map((e) => ArticleCategoryModel.fromApi(
            Map<String, dynamic>.from(e as Map)))
        .toList();
  }

  Future<ArticleModel> fetchDetail(String slug) async {
    final res = await _dio.get<Map<String, dynamic>>('/articles/$slug');
    final data = res.data?['data'];
    if (data is! Map) {
      throw StateError('Invalid API response: expected data object');
    }
    return ArticleModel.fromApi(Map<String, dynamic>.from(data));
  }
}
