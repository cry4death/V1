import 'package:flutter_riverpod/flutter_riverpod.dart';

class LoginFlowState {
  final int step;
  final String phone;

  const LoginFlowState({this.step = 1, this.phone = ''});

  LoginFlowState copyWith({int? step, String? phone}) =>
      LoginFlowState(step: step ?? this.step, phone: phone ?? this.phone);
}

class LoginFlowController extends StateNotifier<LoginFlowState> {
  LoginFlowController() : super(const LoginFlowState());

  void toPhone() => state = state.copyWith(step: 1);

  void toOtp(String phone) => state = LoginFlowState(step: 2, phone: phone);

  void reset() => state = const LoginFlowState();
}

final loginFlowControllerProvider =
    StateNotifierProvider.autoDispose<LoginFlowController, LoginFlowState>(
  (ref) => LoginFlowController(),
);
