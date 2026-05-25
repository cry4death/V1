import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Безопасное хранилище для токенов (access / refresh).
///
/// Используется в `dioProvider` для прикладывания `Authorization`-заголовка
/// и в `pushTokenSyncProvider` при необходимости.
class SecureStorage {
  static const _keyAccessToken = 'auth_access_token';
  static const _keyRefreshToken = 'auth_refresh_token';

  final FlutterSecureStorage _storage;

  SecureStorage([FlutterSecureStorage? storage])
      : _storage = storage ?? const FlutterSecureStorage();

  Future<String?> readAccessToken() => _storage.read(key: _keyAccessToken);
  Future<String?> readRefreshToken() => _storage.read(key: _keyRefreshToken);

  Future<void> saveTokens({
    required String accessToken,
    String? refreshToken,
  }) async {
    await _storage.write(key: _keyAccessToken, value: accessToken);
    if (refreshToken != null && refreshToken.isNotEmpty) {
      await _storage.write(key: _keyRefreshToken, value: refreshToken);
    }
  }

  Future<void> clear() async {
    await _storage.delete(key: _keyAccessToken);
    await _storage.delete(key: _keyRefreshToken);
  }
}
