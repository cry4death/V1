import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'login_otp.dart';
import 'login_phone.dart';
import 'presentation/controllers/login_flow_controller.dart';

class LoginScreen extends ConsumerWidget {
  const LoginScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(loginFlowControllerProvider);
    final ctrl = ref.read(loginFlowControllerProvider.notifier);

    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 280),
      switchInCurve: Curves.easeOut,
      child: state.step == 1
          ? LoginPhoneScreen(
              key: const ValueKey(1),
              phone: state.phone,
              onNext: ctrl.toOtp,
            )
          : LoginOtpScreen(
              key: const ValueKey(2),
              phone: state.phone,
              onBack: ctrl.toPhone,
            ),
    );
  }
}
