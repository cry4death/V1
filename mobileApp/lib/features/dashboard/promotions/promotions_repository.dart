import 'package:dio/dio.dart';

import 'promotion_model.dart';

class PromotionsRepository {
  PromotionsRepository(this._dio);

  final Dio _dio;

  Future<List<PromotionModel>> fetchActive({int limit = 10}) async {
    final res = await _dio.get<Map<String, dynamic>>(
      '/promotions',
      queryParameters: {'limit': limit},
    );

    final data = res.data?['data'];
    if (data is! List) {
      throw StateError('Invalid API response: expected data array');
    }

    return data
        .map((e) =>
            PromotionModel.fromApi(Map<String, dynamic>.from(e as Map)))
        .toList();
  }

  Future<PromotionModel> fetchDetail(String slug) async {
    final res = await _dio.get<Map<String, dynamic>>('/promotions/$slug');
    final data = res.data?['data'];
    if (data is! Map) {
      throw StateError('Invalid API response: expected data object');
    }
    return PromotionModel.fromApi(Map<String, dynamic>.from(data));
  }
}
