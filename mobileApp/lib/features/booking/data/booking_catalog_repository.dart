import 'package:dio/dio.dart';

import 'booking_models.dart';

class BookingCatalogRepository {
  BookingCatalogRepository(this._dio);

  final Dio _dio;

  Future<List<BookingServiceItem>> fetchServices({String? doctorSlug}) async {
    final q = <String, dynamic>{};
    if (doctorSlug != null && doctorSlug.isNotEmpty) {
      q['doctor'] = doctorSlug;
    }
    final res = await _dio.get<Map<String, dynamic>>(
      '/booking/services',
      queryParameters: q.isEmpty ? null : q,
    );
    final data = res.data?['data'];
    if (data is! List) return [];
    return data
        .map((e) => BookingServiceItem.fromJson(Map<String, dynamic>.from(e as Map)))
        .toList();
  }

  Future<List<BookingDoctorItem>> fetchDoctors(String serviceSlug) async {
    final res = await _dio.get<Map<String, dynamic>>(
      '/booking/doctors',
      queryParameters: {'service': serviceSlug},
    );
    final data = res.data?['data'];
    if (data is! List) return [];
    return data
        .map((e) => BookingDoctorItem.fromJson(Map<String, dynamic>.from(e as Map)))
        .toList();
  }

  /// Returns list of date strings "yyyy-MM-dd".
  Future<List<String>> fetchDates({
    required String serviceSlug,
    required String doctorSlug,
    String? from,
    String? to,
  }) async {
    final q = <String, dynamic>{
      'service': serviceSlug,
      'doctor': doctorSlug,
    };
    if (from != null) q['from'] = from;
    if (to != null) q['to'] = to;

    final res = await _dio.get<Map<String, dynamic>>(
      '/booking/dates',
      queryParameters: q,
    );
    final data = res.data?['data'];
    if (data is! List) return [];
    return data.map((e) => e.toString()).toList();
  }

  /// Returns list of slot DateTimes (local).
  Future<List<DateTime>> fetchSlots({
    required String serviceSlug,
    required String doctorSlug,
    required String date,
  }) async {
    final res = await _dio.get<Map<String, dynamic>>(
      '/booking/slots',
      queryParameters: {
        'service': serviceSlug,
        'doctor': doctorSlug,
        'date': date,
      },
    );
    final data = res.data?['data'];
    if (data is! List) return [];

    return data
        .map((e) {
          final raw = e.toString();
          // Server returns slot times in its timezone (app.timezone=UTC) without
          // any suffix. Append 'Z' so Dart always parses as UTC, then convert
          // to device-local time for display.
          return DateTime.tryParse('${raw}Z');
        })
        .whereType<DateTime>()
        .map((dt) => dt.toLocal())
        .toList();
  }
}
