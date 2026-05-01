import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import 'promotion_model.dart';
import 'promotions_repository.dart';

final promotionsRepositoryProvider = Provider<PromotionsRepository>((ref) {
  return PromotionsRepository(ref.watch(dioProvider));
});

/// Активные акции и чек-апы для главной.
final homePromotionsProvider =
    FutureProvider<List<PromotionModel>>((ref) async {
  return ref.watch(promotionsRepositoryProvider).fetchActive(limit: 10);
});

final promotionDetailProvider =
    FutureProvider.family<PromotionModel, String>((ref, slug) async {
  return ref.watch(promotionsRepositoryProvider).fetchDetail(slug);
});
