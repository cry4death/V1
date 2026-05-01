import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import 'article_model.dart';
import 'blog_repository.dart';

export 'blog_repository.dart' show ArticleCategoryModel;

final blogRepositoryProvider = Provider<BlogRepository>((ref) {
  return BlogRepository(ref.watch(dioProvider));
});

/// Короткий список для главной (превью).
final blogPreviewProvider = FutureProvider<List<ArticleModel>>((ref) async {
  return ref.watch(blogRepositoryProvider).fetchList(limit: 6);
});

/// Полный список для отдельной страницы блога.
final blogListProvider = FutureProvider<List<ArticleModel>>((ref) async {
  return ref.watch(blogRepositoryProvider).fetchList();
});

/// Параметры фильтра списка блога (категория + поиск).
class BlogQuery {
  final String? categorySlug;
  final String search;
  const BlogQuery({this.categorySlug, this.search = ''});

  @override
  bool operator ==(Object other) =>
      other is BlogQuery &&
      other.categorySlug == categorySlug &&
      other.search == search;

  @override
  int get hashCode => Object.hash(categorySlug, search);
}

final blogFilteredProvider =
    FutureProvider.family<List<ArticleModel>, BlogQuery>((ref, query) async {
  return ref.watch(blogRepositoryProvider).fetchList(
        categorySlug: query.categorySlug,
        search: query.search.trim().isEmpty ? null : query.search.trim(),
      );
});

final articleCategoriesProvider =
    FutureProvider<List<ArticleCategoryModel>>((ref) async {
  return ref.watch(blogRepositoryProvider).fetchCategories();
});

final articleDetailProvider =
    FutureProvider.family<ArticleModel, String>((ref, slug) async {
  return ref.watch(blogRepositoryProvider).fetchDetail(slug);
});
