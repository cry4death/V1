import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/storage/providers.dart';
import '../../../../core/storage/storage_service.dart';
import '../../domain/auth_state.dart';

/// Контроллер авторизации. Хранит текущее состояние пользователя
/// и предоставляет методы для регистрации, входа, логаута,
/// смены PIN / Face ID.
class AuthController extends StateNotifier<AuthState> {
  AuthController(this._storage)
      : super(const AuthState(status: AuthStatus.authenticated));

  final StorageService _storage;

  /// Загрузка состояния из persisted storage — вызывается на splash-экране.
  /// Идемпотентна: если пользователь уже авторизован, текущий статус
  /// сохраняется (обновляются только профильные поля).
  Future<void> bootstrap() async {
    final isRegistered = await _storage.isRegistered();
    if (!isRegistered) {
      state = state.copyWith(status: AuthStatus.unregistered);
      return;
    }

    final firstName = await _storage.getFirstName();
    final lastName = await _storage.getLastName();
    final phone = await _storage.getPhone();
    final faceId = await _storage.isFaceIdEnabled();

    final nextStatus = state.status == AuthStatus.authenticated
        ? AuthStatus.authenticated
        : AuthStatus.registeredLoggedOut;

    state = AuthState(
      status: nextStatus,
      firstName: firstName,
      lastName: lastName,
      phone: phone,
      faceIdEnabled: faceId,
    );
  }

  /// Завершение регистрации — сохраняет имя/телефон и переводит
  /// пользователя в состояние "зарегистрирован, но не вошёл".
  Future<void> completeRegistration({
    required String firstName,
    required String lastName,
    required String phone,
  }) async {
    await _storage.saveRegistration(
      firstName: firstName,
      lastName: lastName,
      phone: phone,
    );
    state = state.copyWith(
      status: AuthStatus.registeredLoggedOut,
      firstName: firstName,
      lastName: lastName,
      phone: phone,
    );
  }

  Future<void> savePin(String pin) => _storage.savePin(pin);

  Future<String> getStoredPin() => _storage.getPin();

  Future<void> setFaceIdEnabled(bool enabled) async {
    await _storage.saveFaceId(enabled);
    state = state.copyWith(faceIdEnabled: enabled);
  }

  /// Успешный вход (PIN / Face ID / OTP).
  void markAuthenticated() {
    state = state.copyWith(status: AuthStatus.authenticated);
  }

  /// Выход — очищает всё и возвращает к экрану /auth.
  Future<void> logout() async {
    await _storage.clear();
    state = const AuthState(status: AuthStatus.unregistered);
  }
}

final authControllerProvider =
    StateNotifierProvider<AuthController, AuthState>((ref) {
  final storage = ref.watch(storageServiceProvider);
  return AuthController(storage);
});
