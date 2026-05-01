/// Включайте, когда в проекте есть `google-services.json` (Android) и
/// настроен iOS, затем: `flutter run --dart-define=USE_FIREBASE=true`
const bool kFirebaseEnabled = bool.fromEnvironment(
  'USE_FIREBASE',
  defaultValue: false,
);
