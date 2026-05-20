import 'package:dio/dio.dart';

class BookingRepository {
  BookingRepository(this._dio);

  final Dio _dio;

  /// Creates a new appointment. Returns the created appointment id.
  Future<int> createAppointment({
    required int serviceId,
    required int doctorId,
    required DateTime startAt,
    String? note,
  }) async {
    final body = <String, dynamic>{
      'service_id': serviceId,
      'doctor_id': doctorId,
      'start_at': startAt.toUtc().toIso8601String(),
      if (note != null && note.trim().isNotEmpty) 'note': note.trim(),
    };

    final res = await _dio.post<Map<String, dynamic>>(
      '/appointments',
      data: body,
    );
    final data = res.data?['data'];
    if (data is Map) {
      return (data['id'] as num?)?.toInt() ?? 0;
    }
    return 0;
  }
}
