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
/// Переопределение: `--dart-define=API_BASE=...` или `API_BASE_URL=...`.
/// Значение должно включать суффикс `/api/v1` (как на сервере Laravel).
const String kApiBaseFromDefine = String.fromEnvironment('API_BASE');
const String kApiBaseUrlFromEnv = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'http://10.0.2.2:8000/api/v1',
);

/// Итоговая база: приоритет `API_BASE`, иначе `API_BASE_URL`/дефолт; без хвостового `/`.
String get resolvedApiBaseUrl {
  final raw = kApiBaseFromDefine.isNotEmpty ? kApiBaseFromDefine : kApiBaseUrlFromEnv;
  return raw.endsWith('/') ? raw.substring(0, raw.length - 1) : raw;
}

final authSessionExpiredSignalProvider = StateProvider<int>((ref) => 0);

final dioProvider = Provider<Dio>((ref) {
  final secure = ref.watch(secureStorageProvider);
  Future<String?>? refreshFuture;

  final dio = Dio(
    BaseOptions(
      baseUrl: resolvedApiBaseUrl,
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
  dio.interceptors.add(_ErrorInterceptor(
    dio,
    secure,
    onRefreshStarted: () {
      refreshFuture = _refreshAccessToken(dio, secure);
      refreshFuture!.whenComplete(() {
        refreshFuture = null;
      });
      return refreshFuture!;
    },
    readRefreshFuture: () => refreshFuture,
    onSessionExpired: () {
      ref.read(authSessionExpiredSignalProvider.notifier).state++;
    },
  ));

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
    if (options.extra['skipAuthRefresh'] == true) {
      options.headers.remove('Authorization');
      handler.next(options);
      return;
    }

    final token = await _storage.readAccessToken();
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }
}

class _ErrorInterceptor extends Interceptor {
  final Dio _dio;
  final SecureStorage _storage;
  final Future<String?> Function() _onRefreshStarted;
  final Future<String?>? Function() _readRefreshFuture;
  final void Function() _onSessionExpired;

  _ErrorInterceptor(
    this._dio,
    this._storage, {
    required Future<String?> Function() onRefreshStarted,
    required Future<String?>? Function() readRefreshFuture,
    required void Function() onSessionExpired,
  })  : _onRefreshStarted = onRefreshStarted,
        _readRefreshFuture = readRefreshFuture,
        _onSessionExpired = onSessionExpired;

  @override
  Future<void> onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    final status = err.response?.statusCode;

    if (status == 401 && !_isRefreshRequest(err.requestOptions)) {
      if (err.requestOptions.extra['authRetry'] == true) {
        await _storage.clear();
        _onSessionExpired();
        handler.reject(DioException(
          requestOptions: err.requestOptions,
          response: err.response,
          error: const UnauthorizedException(),
          type: err.type,
          stackTrace: err.stackTrace,
        ));
        return;
      }

      final refreshedToken = await _refreshOrWait();
      if (refreshedToken != null && refreshedToken.isNotEmpty) {
        try {
          final response = await _retry(err.requestOptions, refreshedToken);
          handler.resolve(response);
          return;
        } on DioException catch (retryError) {
          handler.reject(_wrapError(retryError));
          return;
        }
      }

      await _storage.clear();
      _onSessionExpired();
      handler.reject(DioException(
        requestOptions: err.requestOptions,
        response: err.response,
        error: const UnauthorizedException(),
        type: err.type,
        stackTrace: err.stackTrace,
      ));
      return;
    }

    final message = _extractMessage(err);

    handler.reject(DioException(
      requestOptions: err.requestOptions,
      response: err.response,
      error: ApiException(message, statusCode: status, raw: err),
      type: err.type,
      stackTrace: err.stackTrace,
    ));
  }

  Future<String?> _refreshOrWait() async {
    final activeRefresh = _readRefreshFuture();
    if (activeRefresh != null) {
      return activeRefresh;
    }

    return _onRefreshStarted();
  }

  bool _isRefreshRequest(RequestOptions options) {
    return options.path.endsWith('/auth/refresh') ||
        options.extra['skipAuthRefresh'] == true;
  }

  Future<Response<dynamic>> _retry(
    RequestOptions requestOptions,
    String token,
  ) {
    final headers = Map<String, dynamic>.from(requestOptions.headers);
    headers['Authorization'] = 'Bearer $token';
    final options = Options(
      method: requestOptions.method,
      sendTimeout: requestOptions.sendTimeout,
      receiveTimeout: requestOptions.receiveTimeout,
      extra: {
        ...requestOptions.extra,
        'authRetry': true,
      },
      headers: headers,
      responseType: requestOptions.responseType,
      contentType: requestOptions.contentType,
      validateStatus: requestOptions.validateStatus,
      receiveDataWhenStatusError: requestOptions.receiveDataWhenStatusError,
      followRedirects: requestOptions.followRedirects,
      maxRedirects: requestOptions.maxRedirects,
      requestEncoder: requestOptions.requestEncoder,
      responseDecoder: requestOptions.responseDecoder,
    );

    return _dio.request<dynamic>(
      requestOptions.path,
      data: requestOptions.data,
      queryParameters: requestOptions.queryParameters,
      options: options,
      cancelToken: requestOptions.cancelToken,
      onSendProgress: requestOptions.onSendProgress,
      onReceiveProgress: requestOptions.onReceiveProgress,
    );
  }

  DioException _wrapError(DioException err) {
    final status = err.response?.statusCode;

    if (status == 401) {
      return DioException(
        requestOptions: err.requestOptions,
        response: err.response,
        error: const UnauthorizedException(),
        type: err.type,
        stackTrace: err.stackTrace,
      );
    }

    final message = _extractMessage(err);

    return DioException(
      requestOptions: err.requestOptions,
      response: err.response,
      error: ApiException(message, statusCode: status, raw: err),
      type: err.type,
      stackTrace: err.stackTrace,
    );
  }

  String _extractMessage(DioException err) {
    final data = err.response?.data;
    if (data is Map) {
      if (data['message'] is String) {
        return data['message'] as String;
      }
      final errors = data['errors'];
      if (errors is Map && errors.isNotEmpty) {
        final first = errors.values.first;
        if (first is List && first.isNotEmpty && first.first is String) {
          return first.first as String;
        }
      }
    }
    return err.message ?? 'Network error';
  }
}

Future<String?> _refreshAccessToken(Dio dio, SecureStorage storage) async {
  final refreshToken = await storage.readRefreshToken();
  if (refreshToken == null || refreshToken.isEmpty) {
    await storage.clear();
    return null;
  }

  try {
    final response = await dio.post<Map<String, dynamic>>(
      '/auth/refresh',
      data: {'refresh_token': refreshToken},
      options: Options(
        extra: {'skipAuthRefresh': true},
      ),
    );
    final data = response.data?['data'];
    if (data is! Map<String, dynamic>) {
      await storage.clear();
      return null;
    }

    final token = data['token'];
    final nextRefreshToken = data['refresh_token'];
    if (token is! String || token.isEmpty) {
      await storage.clear();
      return null;
    }

    await storage.saveTokens(
      accessToken: token,
      refreshToken: nextRefreshToken is String ? nextRefreshToken : null,
    );
    return token;
  } catch (_) {
    await storage.clear();
    return null;
  }
}
