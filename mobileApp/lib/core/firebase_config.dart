/// google-services.json добавлен — Firebase включён по умолчанию.
/// Для явного отключения: flutter run --dart-define=USE_FIREBASE=false
const bool kFirebaseEnabled = bool.fromEnvironment(
  'USE_FIREBASE',
  defaultValue: true,
);
