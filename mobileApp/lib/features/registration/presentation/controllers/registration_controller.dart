import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../domain/registration_data.dart';

/// Контроллер мастера регистрации. Хранит шаг визарда и данные
/// формы между экранами.
class RegistrationState {
  final int step;
  final RegistrationData data;

  const RegistrationState({this.step = 1, this.data = const RegistrationData()});

  RegistrationState copyWith({int? step, RegistrationData? data}) {
    return RegistrationState(
      step: step ?? this.step,
      data: data ?? this.data,
    );
  }
}

class RegistrationController extends StateNotifier<RegistrationState> {
  RegistrationController() : super(const RegistrationState());

  void goToStep(int step) => state = state.copyWith(step: step);

  void updateData(RegistrationData data) => state = state.copyWith(data: data);

  void patch({
    String? lastName,
    String? firstName,
    String? middleName,
    String? birthDate,
    String? gender,
    String? phone,
  }) {
    state = state.copyWith(
      data: state.data.copyWith(
        lastName: lastName,
        firstName: firstName,
        middleName: middleName,
        birthDate: birthDate,
        gender: gender,
        phone: phone,
      ),
    );
  }

  void reset() => state = const RegistrationState();
}

final registrationControllerProvider =
    StateNotifierProvider<RegistrationController, RegistrationState>(
  (ref) => RegistrationController(),
);
