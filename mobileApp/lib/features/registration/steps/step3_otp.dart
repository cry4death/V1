import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/widgets/primary_button.dart';
import '../../auth/presentation/controllers/auth_controller.dart';
import '../presentation/controllers/registration_controller.dart';
import '_progress_bar.dart';

class Step3Otp extends ConsumerStatefulWidget {
  final VoidCallback onNext;
  final VoidCallback onBack;

  const Step3Otp({
    super.key,
    required this.onNext,
    required this.onBack,
  });

  @override
  ConsumerState<Step3Otp> createState() => _Step3OtpState();
}

class _Step3OtpState extends ConsumerState<Step3Otp> {
  static const _otpLength = 6;
  final List<TextEditingController> _controllers =
      List.generate(_otpLength, (_) => TextEditingController());
  final List<FocusNode> _focusNodes =
      List.generate(_otpLength, (_) => FocusNode());

  String get _otp => _controllers.map((c) => c.text).join();
  bool get _isComplete => _otp.length == _otpLength;

  @override
  void dispose() {
    for (final c in _controllers) {
      c.dispose();
    }
    for (final f in _focusNodes) {
      f.dispose();
    }
    super.dispose();
  }

  void _onDigitChanged(int index, String value) {
    if (value.length == 1 && index < _otpLength - 1) {
      _focusNodes[index + 1].requestFocus();
    }
    setState(() {});
    if (_isComplete) {
      _confirm();
    }
  }

  void _onKeyBackspace(int index) {
    if (_controllers[index].text.isEmpty && index > 0) {
      _focusNodes[index - 1].requestFocus();
      _controllers[index - 1].clear();
    }
    setState(() {});
  }

  Future<void> _confirm() async {
    final data = ref.read(registrationControllerProvider).data;
    await ref.read(authControllerProvider.notifier).completeRegistration(
          firstName: data.firstName,
          lastName: data.lastName,
          phone: data.phone,
        );
    if (!mounted) return;
    widget.onNext();
  }

  @override
  Widget build(BuildContext context) {
    final phone =
        ref.watch(registrationControllerProvider.select((s) => s.data.phone));

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            RegistrationProgressBar(step: 3, total: 5),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 4, 20, 16),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: widget.onBack,
                    child: const Icon(
                      Icons.arrow_back,
                      color: Color(0xFF101623),
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Код подтверждения',
                          style: GoogleFonts.inter(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF101623),
                          ),
                        ),
                        Text(
                          'Введите 6-значный код из SMS на +375$phone',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            color: const Color(0xFF717784),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.only(bottom: 8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const SizedBox(height: 32),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      child: Row(
                        children: [
                          for (var i = 0; i < _otpLength; i++) ...[
                            if (i > 0) const SizedBox(width: 6),
                            Expanded(
                              child: _OtpBox(
                                controller: _controllers[i],
                                focusNode: _focusNodes[i],
                                onChanged: (v) => _onDigitChanged(i, v),
                                onBackspace: () => _onKeyBackspace(i),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ).animate().fadeIn(duration: 400.ms).moveY(begin: 20, end: 0),
                    const SizedBox(height: 24),
                    Text(
                      'Демо: используйте любой 6-значный код',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        color: AppColors.textHint,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 12),
              child: PrimaryButton(
                label: 'Подтвердить',
                onTap: _confirm,
                isEnabled: _isComplete,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _OtpBox extends StatelessWidget {
  final TextEditingController controller;
  final FocusNode focusNode;
  final ValueChanged<String> onChanged;
  final VoidCallback onBackspace;

  const _OtpBox({
    required this.controller,
    required this.focusNode,
    required this.onChanged,
    required this.onBackspace,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 56,
      child: TextField(
        controller: controller,
        focusNode: focusNode,
        textAlign: TextAlign.center,
        keyboardType: TextInputType.number,
        inputFormatters: [
          FilteringTextInputFormatter.digitsOnly,
          LengthLimitingTextInputFormatter(1),
        ],
        decoration: InputDecoration(
          filled: true,
          fillColor: AppColors.inputFill,
          contentPadding: EdgeInsets.zero,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: AppColors.border, width: 1.5),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: BorderSide(
              color: controller.text.isNotEmpty
                  ? AppColors.primary
                  : AppColors.border,
              width: 1.5,
            ),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide:
                const BorderSide(color: AppColors.primary, width: 2),
          ),
        ),
        style: GoogleFonts.inter(
          fontSize: 22,
          fontWeight: FontWeight.w600,
          color: AppColors.textPrimary,
        ),
        onChanged: onChanged,
        onTapOutside: (_) => FocusScope.of(context).unfocus(),
      ),
    );
  }
}
