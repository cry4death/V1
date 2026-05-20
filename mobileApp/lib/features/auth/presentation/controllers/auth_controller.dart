import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/storage/providers.dart';
import '../../../../core/storage/secure_storage.dart';
import '../../../../core/storage/storage_service.dart';
import '../../data/patient_auth_providers.dart';
import '../../data/patient_auth_repository.dart';
import '../../domain/auth_state.dart';

/// Контроллер авторизации. Хранит текущее состояние пользователя
/// и предоставляет методы для регистрации, входа, логаута,
/// смены PIN / Face ID и редактирования профиля.
class AuthController extends StateNotifier<AuthState> {
  AuthController(this._storage, this._secureStorage, this._authRepository)
      : super(const AuthState(status: AuthStatus.unknown));

  final StorageService _storage;
  final SecureStorage _secureStorage;
  final PatientAuthRepository _authRepository;

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
    final middleName = await _storage.getMiddleName();
    final phone = await _storage.getPhone();
    final birthDate = await _storage.getBirthDate();
    final gender = await _storage.getGender();
    final faceId = await _storage.isFaceIdEnabled();

    final nextStatus = state.status == AuthStatus.authenticated
        ? AuthStatus.authenticated
        : AuthStatus.registeredLoggedOut;

    state = AuthState(
      status: nextStatus,
      firstName: firstName,
      lastName: lastName,
      middleName: middleName,
      phone: phone,
      birthDate: birthDate,
      gender: gender,
      faceIdEnabled: faceId,
    );

    final token = await _secureStorage.readAccessToken();
    if (token != null && token.isNotEmpty) {
      try {
        final p = await _authRepository.me();
        await _applyPatientData(p, keepPhone: phone);
      } on DioException {
        // Сеть / протухший токен — остаёмся на локальных данных
      }
    }
  }

  /// Завершение регистрации после успешной верификации OTP на сервере.
  Future<void> completeRegistrationWithApiToken({
    required String accessToken,
    required String firstName,
    required String lastName,
    required String phone,
    String middleName = '',
    String birthDate = '',
    String gender = '',
  }) async {
    await _secureStorage.saveTokens(accessToken: accessToken);
    await _storage.saveRegistration(
      firstName: firstName,
      lastName: lastName,
      middleName: middleName,
      phone: phone,
      birthDate: birthDate,
      gender: gender,
    );
    state = state.copyWith(
      status: AuthStatus.registeredLoggedOut,
      firstName: firstName,
      lastName: lastName,
      middleName: middleName,
      phone: phone,
      birthDate: birthDate,
      gender: gender,
    );
  }

  /// Вход по OTP: токен + профиль сохраняются, но статус остаётся
  /// [registeredLoggedOut] — пользователь должен пройти PIN/биометрию.
  Future<void> completeLoginWithApiToken({
    required String accessToken,
    required Map<String, dynamic> patient,
  }) async {
    await _secureStorage.saveTokens(accessToken: accessToken);
    await _applyPatientData(patient, token: accessToken);
    state = state.copyWith(status: AuthStatus.registeredLoggedOut);
  }

  /// Обновление профиля через `PATCH /api/v1/me`.
  ///
  /// Возвращает сообщение об ошибке или `null` при успехе.
  Future<String?> updateProfile({
    required String lastName,
    required String firstName,
    String middleName = '',
    required String birthDate,
    required String gender,
  }) async {
    try {
      final p = await _authRepository.updateMe(
        lastName: lastName,
        firstName: firstName,
        middleName: middleName.isNotEmpty ? middleName : null,
        birthDate: birthDate,
        gender: gender,
      );
      await _applyPatientData(p, keepPhone: state.phone);
      return null;
    } on DioException catch (e) {
      final errors = e.response?.data?['errors'];
      if (errors is Map) {
        final first = errors.values.first;
        if (first is List && first.isNotEmpty) return first.first.toString();
      }
      return e.response?.data?['message']?.toString() ??
          'Не удалось сохранить. Проверьте подключение.';
    }
  }

  /// Применяет данные из ответа API к состоянию и `SharedPreferences`.
  Future<void> _applyPatientData(
    Map<String, dynamic> p, {
    String? keepPhone,
    String? token,
  }) async {
    final fn = (p['first_name'] as String?) ?? state.firstName;
    final ln = (p['last_name'] as String?) ?? state.lastName;
    final mn = (p['middle_name'] as String?) ?? state.middleName;
    final ph = (p['phone'] as String?) ?? keepPhone ?? state.phone;
    final bd = (p['birth_date'] as String?) ?? state.birthDate;
    final gn = (p['gender'] as String?) ?? state.gender;

    await _storage.saveRegistration(
      firstName: fn,
      lastName: ln,
      middleName: mn,
      phone: ph,
      birthDate: bd,
      gender: gn,
    );

    state = state.copyWith(
      firstName: fn,
      lastName: ln,
      middleName: mn,
      phone: ph,
      birthDate: bd,
      gender: gn,
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

  /// Выход — отзыв токена на сервере (если есть), очистка локально.
  Future<void> logout() async {
    try {
      await _authRepository.logout();
    } on DioException {
      // всё равно выходим локально
    }
    await _storage.clear();
    await _secureStorage.clear();
    state = const AuthState(status: AuthStatus.unregistered);
  }
}

final authControllerProvider =
    StateNotifierProvider<AuthController, AuthState>((ref) {
  final storage = ref.watch(storageServiceProvider);
  final secure = ref.watch(secureStorageProvider);
  final authRepo = ref.watch(patientAuthRepositoryProvider);
  return AuthController(storage, secure, authRepo);
});
