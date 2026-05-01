import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'presentation/controllers/registration_controller.dart';
import 'steps/step1_personal.dart';
import 'steps/step2_phone.dart';
import 'steps/step3_otp.dart';
import 'steps/pin_setup.dart';
import 'steps/face_id_setup.dart';

class RegistrationScreen extends ConsumerStatefulWidget {
  const RegistrationScreen({super.key});

  @override
  ConsumerState<RegistrationScreen> createState() => _RegistrationScreenState();
}

class _RegistrationScreenState extends ConsumerState<RegistrationScreen> {
  @override
  void initState() {
    super.initState();
    // Сброс данных предыдущей регистрации при заходе на экран.
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(registrationControllerProvider.notifier).reset();
    });
  }

  @override
  Widget build(BuildContext context) {
    final step = ref.watch(registrationControllerProvider).step;
    final ctrl = ref.read(registrationControllerProvider.notifier);

    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 280),
      switchInCurve: Curves.easeOut,
      switchOutCurve: Curves.easeIn,
      transitionBuilder: (child, animation) {
        final offsetAnimation = Tween<Offset>(
          begin: const Offset(1.0, 0.0),
          end: Offset.zero,
        ).animate(animation);
        return SlideTransition(position: offsetAnimation, child: child);
      },
      child: _buildStep(step, ctrl),
    );
  }

  Widget _buildStep(int step, RegistrationController ctrl) {
    switch (step) {
      case 1:
        return Step1Personal(
          key: const ValueKey(1),
          onNext: () => ctrl.goToStep(2),
          onBack: null,
        );
      case 2:
        return Step2Phone(
          key: const ValueKey(2),
          onNext: () => ctrl.goToStep(3),
          onBack: () => ctrl.goToStep(1),
        );
      case 3:
        return Step3Otp(
          key: const ValueKey(3),
          onNext: () => ctrl.goToStep(4),
          onBack: () => ctrl.goToStep(2),
        );
      case 4:
        return PinSetupScreen(
          key: const ValueKey(4),
          onNext: () => ctrl.goToStep(5),
        );
      case 5:
        return const FaceIdSetupScreen(key: ValueKey(5));
      default:
        return const SizedBox.shrink();
    }
  }
}
