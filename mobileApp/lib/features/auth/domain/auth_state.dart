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
  final String middleName;
  final String phone;
  final String birthDate;
  final String gender;
  final bool faceIdEnabled;

  const AuthState({
    this.status = AuthStatus.unknown,
    this.firstName = '',
    this.lastName = '',
    this.middleName = '',
    this.phone = '',
    this.birthDate = '',
    this.gender = '',
    this.faceIdEnabled = false,
  });

  const AuthState.unknown() : this();

  /// Полное имя: «Фамилия Имя Отчество» или «Имя Фамилия» без отчества.
  String get fullName {
    final parts = [lastName, firstName, if (middleName.isNotEmpty) middleName];
    return parts.where((e) => e.isNotEmpty).join(' ');
  }

  /// Краткое имя для шапки: «Имя Фамилия».
  String get shortName {
    final parts = [firstName, if (lastName.isNotEmpty) lastName];
    return parts.where((e) => e.isNotEmpty).join(' ');
  }

  AuthState copyWith({
    AuthStatus? status,
    String? firstName,
    String? lastName,
    String? middleName,
    String? phone,
    String? birthDate,
    String? gender,
    bool? faceIdEnabled,
  }) {
    return AuthState(
      status: status ?? this.status,
      firstName: firstName ?? this.firstName,
      lastName: lastName ?? this.lastName,
      middleName: middleName ?? this.middleName,
      phone: phone ?? this.phone,
      birthDate: birthDate ?? this.birthDate,
      gender: gender ?? this.gender,
      faceIdEnabled: faceIdEnabled ?? this.faceIdEnabled,
    );
  }
}
