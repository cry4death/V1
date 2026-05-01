import 'package:shared_preferences/shared_preferences.dart';

/// Хранилище пользовательских данных в `SharedPreferences`.
///
/// Этот класс остаётся низкоуровневым сервисом — всю работу с ним
/// в UI-слое стоит вести через Riverpod-контроллеры (`AuthController`).
class StorageService {
  StorageService();

  static const _keyRegistered = 'mayak_registered';
  static const _keyFirstName = 'mayak_firstName';
  static const _keyLastName = 'mayak_lastName';
  static const _keyPhone = 'mayak_phone';
  static const _keyPin = 'mayak_pin';
  static const _keyFaceId = 'mayak_faceId';

  Future<SharedPreferences> get _prefs => SharedPreferences.getInstance();

  Future<bool> isRegistered() async =>
      (await _prefs).getBool(_keyRegistered) ?? false;

  Future<bool> isFaceIdEnabled() async =>
      (await _prefs).getBool(_keyFaceId) ?? false;

  Future<String> getFirstName() async =>
      (await _prefs).getString(_keyFirstName) ?? 'Пользователь';

  Future<String> getLastName() async =>
      (await _prefs).getString(_keyLastName) ?? '';

  Future<String> getPhone() async =>
      (await _prefs).getString(_keyPhone) ?? '';

  Future<String> getPin() async =>
      (await _prefs).getString(_keyPin) ?? '0000';

  Future<void> saveRegistration({
    required String firstName,
    required String lastName,
    required String phone,
  }) async {
    final p = await _prefs;
    await p.setString(_keyFirstName, firstName);
    await p.setString(_keyLastName, lastName);
    await p.setString(_keyPhone, phone);
    await p.setBool(_keyRegistered, true);
  }

  Future<void> savePin(String pin) async {
    final p = await _prefs;
    await p.setString(_keyPin, pin);
  }

  Future<void> saveFaceId(bool enabled) async {
    final p = await _prefs;
    await p.setBool(_keyFaceId, enabled);
  }

  Future<void> clear() async {
    final p = await _prefs;
    await p.clear();
  }
}
