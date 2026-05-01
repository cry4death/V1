import 'package:dio/dio.dart';

import 'doctor_models.dart';

class DoctorsRepository {
  DoctorsRepository(this._dio);

  final Dio _dio;

  Future<List<DoctorModel>> fetchList({
    String? specializationSlug,
    String? patientAge,
  }) async {
    final q = <String, dynamic>{};
    if (specializationSlug != null && specializationSlug.isNotEmpty) {
      q['specialization'] = specializationSlug;
    }
    if (patientAge != null && patientAge.isNotEmpty) {
      q['patient_age'] = patientAge;
    }

    final res = await _dio.get<Map<String, dynamic>>(
      '/doctors',
      queryParameters: q.isEmpty ? null : q,
    );

    final data = res.data?['data'];
    if (data is! List) {
      throw StateError('Invalid API response: expected data array');
    }

    return data
        .map((e) => DoctorModel.fromApiList(Map<String, dynamic>.from(e as Map)))
        .toList();
  }

  Future<DoctorModel> fetchDetail(String slug) async {
    final res = await _dio.get<Map<String, dynamic>>('/doctors/$slug');
    final data = res.data?['data'];
    if (data is! Map) {
      throw StateError('Invalid API response: expected data object');
    }
    return DoctorModel.fromApiDetail(Map<String, dynamic>.from(data));
  }
}
