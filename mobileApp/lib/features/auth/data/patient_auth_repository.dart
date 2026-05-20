import 'package:dio/dio.dart';

/// Регистрация и вход пациента по OTP (API сайта, префикс `/api/v1`).
class PatientAuthRepository {
  PatientAuthRepository(this._dio);

  final Dio _dio;

  Future<void> registerRequestOtp({
    required String lastName,
    required String firstName,
    String? middleName,
    required String birthDate,
    required String gender,
    /// E.164 без «+», например `375291234567`.
    required String phone,
  }) async {
    await _dio.post<Map<String, dynamic>>(
      '/auth/register/request-otp',
      data: {
        'last_name': lastName,
        'first_name': firstName,
        if (middleName != null && middleName.isNotEmpty) 'middle_name': middleName,
        'birth_date': birthDate,
        'gender': gender,
        'phone': phone,
      },
    );
  }

  Future<({String token, Map<String, dynamic> patient})> registerVerify({
    /// Должен совпадать с номером в [registerRequestOtp] (E.164 без «+»).
    required String phone,
    required String otp,
  }) async {
    final res = await _dio.post<Map<String, dynamic>>(
      '/auth/register/verify',
      data: {
        'phone': phone,
        'otp': otp,
      },
    );
    return _parseTokenAndPatient(res);
  }

  Future<void> requestLoginOtp({
    /// E.164 без «+», например `375291234567` (как в регистрации).
    required String phone,
  }) async {
    await _dio.post<Map<String, dynamic>>(
      '/auth/login/request-otp',
      data: {'phone': phone},
    );
  }

  Future<({String token, Map<String, dynamic> patient})> verifyLoginOtp({
    required String phone,
    required String otp,
  }) async {
    final res = await _dio.post<Map<String, dynamic>>(
      '/auth/login/verify',
      data: {
        'phone': phone,
        'otp': otp,
      },
    );
    return _parseTokenAndPatient(res);
  }

  /// Текущий пациент (`GET /me`, Bearer прикладывает Dio).
  Future<Map<String, dynamic>> me() async {
    final res = await _dio.get<Map<String, dynamic>>('/me');
    final data = res.data?['data'];
    if (data is! Map<String, dynamic>) {
      throw StateError('Invalid API response: missing data');
    }
    return data;
  }

  /// Обновление профиля (`PATCH /me`).
  ///
  /// Все поля опциональны — передаются только те, что нужно изменить.
  /// Возвращает обновлённые данные пациента.
  Future<Map<String, dynamic>> updateMe({
    String? lastName,
    String? firstName,
    String? middleName,
    String? birthDate,
    String? gender,
    String? email,
  }) async {
    final body = <String, dynamic>{
      'last_name': lastName,
      'first_name': firstName,
      'middle_name': middleName,
      'birth_date': birthDate,
      'gender': gender,
      'email': email,
    }..removeWhere((_, v) => v == null);
    final res = await _dio.patch<Map<String, dynamic>>('/me', data: body);
    final data = res.data?['data'];
    if (data is! Map<String, dynamic>) {
      throw StateError('Invalid API response: missing data');
    }
    return data;
  }

  /// Отзыв текущего Sanctum-токена (`POST /me/logout`).
  Future<void> logout() async {
    await _dio.post<Map<String, dynamic>>('/me/logout');
  }

  static ({String token, Map<String, dynamic> patient}) _parseTokenAndPatient(
    Response<Map<String, dynamic>> res,
  ) {
    final data = res.data?['data'];
    if (data is! Map<String, dynamic>) {
      throw StateError('Invalid API response: missing data');
    }
    final token = data['token'];
    final patient = data['patient'];
    if (token is! String || patient is! Map<String, dynamic>) {
      throw StateError('Invalid API response: token or patient');
    }
    return (token: token, patient: patient);
  }
}
