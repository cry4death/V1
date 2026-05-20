import 'package:dio/dio.dart';

import 'appointment_model.dart';

class AppointmentsRepository {
  AppointmentsRepository(this._dio);

  final Dio _dio;

  Future<List<AppointmentModel>> fetchUpcoming() => _fetch('upcoming');

  Future<List<AppointmentModel>> fetchPast() => _fetch('past');

  Future<List<AppointmentModel>> _fetch(String status) async {
    final res = await _dio.get<Map<String, dynamic>>(
      '/appointments',
      queryParameters: {'status': status},
    );

    final data = res.data?['data'];
    if (data is! List) {
      throw StateError('Invalid API response: expected data array');
    }

    return data
        .map((e) => AppointmentModel.fromJson(Map<String, dynamic>.from(e as Map)))
        .toList();
  }

  Future<void> cancelAppointment(int id, {String? reason}) async {
    await _dio.post<void>(
      '/appointments/$id/cancel',
      data: reason != null && reason.isNotEmpty ? {'reason': reason} : null,
    );
  }

  Future<AppointmentModel> rescheduleAppointment(
    int id,
    DateTime startAt,
  ) async {
    final res = await _dio.post<Map<String, dynamic>>(
      '/appointments/$id/reschedule',
      data: {'start_at': startAt.toUtc().toIso8601String()},
    );
    final data = res.data?['data'];
    if (data is Map<String, dynamic>) {
      return AppointmentModel.fromJson(data);
    }
    throw StateError('Invalid reschedule response');
  }
}
