import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../storage/secure_storage.dart';
import '../storage/providers.dart';
import 'api_exception.dart';

/// Базовый URL Laravel API (префикс `/api/v1` на сервере).
///
/// Запуск бэкенда: `php artisan serve --host=0.0.0.0 --port=8000` (0.0.0.0 — иначе с
/// эмулятора/телефона до `127.0.0.1` на ПК не достучаться).
///
/// - Эмулятор Android → `10.0.2.2` = localhost машины, где крутится `artisan serve`.
/// - Телефон в Wi‑Fi → IP ПК в той же сети (`ipconfig`, IPv4 адаптера).
/// - USB: `adb reverse tcp:8000 tcp:8000` и тогда `http://127.0.0.1:8000/api/v1`.
///
/// Переопределение: `flutter run --dart-define=API_BASE_URL=http://.../api/v1`
const String kApiBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'http://10.0.2.2:8000/api/v1',
);

final dioProvider = Provider<Dio>((ref) {
  final secure = ref.watch(secureStorageProvider);

  final dio = Dio(
    BaseOptions(
      baseUrl: kApiBaseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 20),
      sendTimeout: const Duration(seconds: 20),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ),
  );

  dio.interceptors.add(_AuthInterceptor(secure));
  dio.interceptors.add(_ErrorInterceptor());

  return dio;
});

class _AuthInterceptor extends Interceptor {
  final SecureStorage _storage;

  _AuthInterceptor(this._storage);

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _storage.readAccessToken();
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }
}

class _ErrorInterceptor extends Interceptor {
  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    final status = err.response?.statusCode;

    if (status == 401) {
      handler.reject(DioException(
        requestOptions: err.requestOptions,
        response: err.response,
        error: const UnauthorizedException(),
        type: err.type,
      ));
      return;
    }

    final message = _extractMessage(err);

    handler.reject(DioException(
      requestOptions: err.requestOptions,
      response: err.response,
      error: ApiException(message, statusCode: status, raw: err),
      type: err.type,
    ));
  }

  String _extractMessage(DioException err) {
    final data = err.response?.data;
    if (data is Map && data['message'] is String) {
      return data['message'] as String;
    }
    return err.message ?? 'Network error';
  }
}
