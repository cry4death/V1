/// Состояние авторизации пользователя.
///
/// - [unknown] — ещё не проверили `SharedPreferences` (splash);
/// - [unregistered] — первый запуск приложения;
/// - [registeredLoggedOut] — пользователь зарегистрирован, но не прошёл PIN / Face ID;
/// - [authenticated] — пользователь вошёл в аккаунт.
enum AuthStatus {
  unknown,
  unregistered,
  registeredLoggedOut,
  authenticated,
}

class AuthState {
  final AuthStatus status;
  final String firstName;
  final String lastName;
  final String phone;
  final bool faceIdEnabled;

  const AuthState({
    this.status = AuthStatus.unknown,
    this.firstName = '',
    this.lastName = '',
    this.phone = '',
    this.faceIdEnabled = false,
  });

  const AuthState.unknown() : this();

  AuthState copyWith({
    AuthStatus? status,
    String? firstName,
    String? lastName,
    String? phone,
    bool? faceIdEnabled,
  }) {
    return AuthState(
      status: status ?? this.status,
      firstName: firstName ?? this.firstName,
      lastName: lastName ?? this.lastName,
      phone: phone ?? this.phone,
      faceIdEnabled: faceIdEnabled ?? this.faceIdEnabled,
    );
  }
}
