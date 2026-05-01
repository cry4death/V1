class RegistrationData {
  final String lastName;
  final String firstName;
  final String middleName;
  final String birthDate;
  final String gender;
  final String phone;

  const RegistrationData({
    this.lastName = '',
    this.firstName = '',
    this.middleName = '',
    this.birthDate = '',
    this.gender = '',
    this.phone = '',
  });

  const RegistrationData.empty() : this();

  RegistrationData copyWith({
    String? lastName,
    String? firstName,
    String? middleName,
    String? birthDate,
    String? gender,
    String? phone,
  }) {
    return RegistrationData(
      lastName: lastName ?? this.lastName,
      firstName: firstName ?? this.firstName,
      middleName: middleName ?? this.middleName,
      birthDate: birthDate ?? this.birthDate,
      gender: gender ?? this.gender,
      phone: phone ?? this.phone,
    );
  }
}
