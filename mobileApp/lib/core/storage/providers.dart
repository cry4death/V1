import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../biometric/biometric_auth_service.dart';
import 'secure_storage.dart';
import 'storage_service.dart';

final storageServiceProvider = Provider<StorageService>((ref) {
  return StorageService();
});

final secureStorageProvider = Provider<SecureStorage>((ref) {
  return SecureStorage();
});

final biometricAuthServiceProvider = Provider<BiometricAuthService>((ref) {
  return BiometricAuthService();
});
