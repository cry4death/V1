import 'package:dio/dio.dart';

import 'services_data.dart';

class ServicesRepository {
  ServicesRepository(this._dio);

  final Dio _dio;

  Future<List<ServiceCategory>> fetchDirections() async {
    final res = await _dio.get<Map<String, dynamic>>('/service-directions');
    final data = res.data?['data'];
    if (data is! List) {
      throw StateError('Invalid API response: expected data array');
    }
    return data
        .map((e) => ServiceCategory.fromJson(Map<String, dynamic>.from(e as Map)))
        .toList();
  }

  Future<List<ServiceOffer>> fetchServicesInDirection(String slug) async {
    if (slug.isEmpty) {
      return const [];
    }
    final res = await _dio
        .get<Map<String, dynamic>>('/service-directions/$slug/services');
    final data = res.data?['data'];
    if (data is! List) {
      throw StateError('Invalid API response: expected data array');
    }
    return data
        .map((e) => ServiceOffer.fromJson(Map<String, dynamic>.from(e as Map)))
        .toList();
  }
}
