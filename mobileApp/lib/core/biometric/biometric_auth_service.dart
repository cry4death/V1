import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:local_auth/error_codes.dart' as auth_error;
import 'package:local_auth/local_auth.dart';
import 'package:local_auth_android/local_auth_android.dart';
import 'package:local_auth_darwin/local_auth_darwin.dart';
import 'package:local_auth_platform_interface/local_auth_platform_interface.dart';
import 'package:local_auth_windows/local_auth_windows.dart';

/// Русские подписи системных диалогов [local_auth] (по умолчанию они на английском).
const List<AuthMessages> kLocalAuthMessagesRu = <AuthMessages>[
  IOSAuthMessages(
    lockOut:
        'Биометрия отключена. Заблокируйте и разблокируйте экран, затем повторите.',
    goToSettingsButton: 'Настройки',
    goToSettingsDescription:
        'На устройстве не настроена биометрия для приложений. Включите Face ID или Touch ID в настройках iPhone.',
    cancelButton: 'Отмена',
  ),
  AndroidAuthMessages(
    biometricHint:
        'Отпечаток или лицо — как предложит система (на части Android только отпечаток)',
    biometricNotRecognized: 'Не распознано. Повторите.',
    biometricRequiredTitle: 'Биометрия для приложений',
    biometricSuccess: 'Готово',
    cancelButton: 'Отмена',
    signInTitle: 'Подтверждение',
    deviceCredentialsRequiredTitle: 'Нужна защита устройства',
    deviceCredentialsSetupDescription:
        'Задайте PIN или пароль экрана блокировки в настройках телефона.',
    goToSettingsButton: 'В настройки',
    goToSettingsDescription:
        'Приложениям нужна «сильная» биометрия (часто отпечаток). '
        'Только разблокировка лицом для экрана без отпечатка Android часто не показывает. '
        'Откройте Настройки → Безопасность / Пароль и биометрия — добавьте отпечаток или данные для приложений.',
  ),
  WindowsAuthMessages(),
];

/// Обёртка над [LocalAuthentication] для входа и настройки биометрии.
class BiometricAuthService {
  BiometricAuthService({LocalAuthentication? localAuth})
      : _auth = localAuth ?? LocalAuthentication();

  final LocalAuthentication _auth;

  /// Есть ли на устройстве доступная для приложения биометрия (отпечаток, Face ID и т.д.).
  Future<bool> isBiometricAvailable() async {
    try {
      final supported = await _auth.isDeviceSupported();
      if (!supported) return false;
      final types = await _auth.getAvailableBiometrics();
      if (types.isNotEmpty) return true;
      return await _auth.canCheckBiometrics;
    } on PlatformException {
      return false;
    }
  }

  /// [biometricOnly]: `true` — только Face ID / отпечаток (без обхода PIN-ом телефона).
  /// Для включения функции в настройках регистрации используйте `true`;
  /// для обычного входа в приложение часто оставляют `false`, чтобы был запасной
  /// способ через код устройства.
  Future<bool> authenticate({
    required String localizedReason,
    bool biometricOnly = false,
  }) async {
    return _auth.authenticate(
      localizedReason: localizedReason,
      authMessages: kLocalAuthMessagesRu,
      options: AuthenticationOptions(
        stickyAuth: true,
        biometricOnly: biometricOnly,
        useErrorDialogs: true,
        // Android: true помогает части прошивок показать полноценный сценарий с Face Unlock
        // (подтверждение после распознавания). На iOS оставляем false — Face ID без лишнего шага.
        sensitiveTransaction: defaultTargetPlatform == TargetPlatform.android,
      ),
    );
  }
}

/// Сообщение пользователю по коду из [PlatformException] (биометрия).
String biometricPlatformErrorMessage(PlatformException e) {
  switch (e.code) {
    case auth_error.notEnrolled:
      return 'Для приложений не зарегистрирована биометрия. На Android часто нужен отпечаток в настройках, а не только лицо для экрана.';
    case auth_error.passcodeNotSet:
      return 'На устройстве не задан PIN или пароль экрана блокировки — они нужны вместе с биометрией.';
    case auth_error.notAvailable:
      return 'Биометрия недоступна на этом устройстве или для этого приложения.';
    case auth_error.lockedOut:
    case auth_error.permanentlyLockedOut:
      return 'Слишком много попыток. Разблокируйте устройство PIN-кодом и попробуйте позже.';
    default:
      return 'Не удалось использовать биометрию. Повторите попытку или войдите по PIN.';
  }
}
