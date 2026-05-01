import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/widgets/primary_button.dart';
import '../presentation/controllers/registration_controller.dart';
import '_progress_bar.dart';

class Step2Phone extends ConsumerStatefulWidget {
  final VoidCallback onNext;
  final VoidCallback onBack;

  const Step2Phone({
    super.key,
    required this.onNext,
    required this.onBack,
  });

  @override
  ConsumerState<Step2Phone> createState() => _Step2PhoneState();
}

class _Step2PhoneState extends ConsumerState<Step2Phone> {
  late final TextEditingController _ctrl;
  bool _touched = false;

  @override
  void initState() {
    super.initState();
    final initial = ref.read(registrationControllerProvider).data.phone;
    _ctrl = TextEditingController(text: initial);
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final phone = ref.watch(
        registrationControllerProvider.select((s) => s.data.phone));
    final regCtrl = ref.read(registrationControllerProvider.notifier);
    final isValid = phone.length == 9;

    void handleNext() {
      setState(() => _touched = true);
      if (isValid) widget.onNext();
    }

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            RegistrationProgressBar(step: 2, total: 5),
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
                          'Номер телефона',
                          style: GoogleFonts.inter(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF101623),
                          ),
                        ),
                        Text(
                          'Введите ваш белорусский номер',
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
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Column(
                  children: [
                    const SizedBox(height: 24),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Телефон *',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: const Color(0xFF444444),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Container(
                          height: 56,
                          decoration: BoxDecoration(
                            color: AppColors.inputFill,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(
                              color: _touched && !isValid
                                  ? AppColors.error
                                  : isValid
                                      ? AppColors.primary
                                      : AppColors.border,
                              width: 1.5,
                            ),
                          ),
                          child: Row(
                            children: [
                              Padding(
                                padding:
                                    const EdgeInsets.symmetric(horizontal: 14),
                                child: Text(
                                  '+375',
                                  style: GoogleFonts.inter(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                    color: AppColors.textPrimary,
                                  ),
                                ),
                              ),
                              Container(
                                width: 1,
                                height: 24,
                                color: AppColors.border,
                              ),
                              Expanded(
                                child: TextField(
                                  controller: _ctrl,
                                  keyboardType: TextInputType.phone,
                                  inputFormatters: [
                                    FilteringTextInputFormatter.digitsOnly,
                                    LengthLimitingTextInputFormatter(9),
                                  ],
                                  decoration: InputDecoration(
                                    hintText: '(XX) XXX-XX-XX',
                                    hintStyle: GoogleFonts.inter(
                                      fontSize: 15,
                                      color: AppColors.textHint,
                                    ),
                                    border: InputBorder.none,
                                    contentPadding: const EdgeInsets.symmetric(
                                        horizontal: 14),
                                  ),
                                  style: GoogleFonts.inter(
                                    fontSize: 16,
                                    color: AppColors.textPrimary,
                                    letterSpacing: 1,
                                  ),
                                  onChanged: (v) {
                                    setState(() => _touched = true);
                                    regCtrl.patch(phone: v);
                                  },
                                ),
                              ),
                            ],
                          ),
                        ),
                        if (_touched && !isValid)
                          Padding(
                            padding: const EdgeInsets.only(top: 4),
                            child: Text(
                              'Введите 9 цифр номера',
                              style: GoogleFonts.inter(
                                fontSize: 11.5,
                                color: AppColors.error,
                              ),
                            ),
                          ),
                        const SizedBox(height: 8),
                        Text(
                          'На этот номер придёт код подтверждения',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            color: AppColors.textHint,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 12),
              child: PrimaryButton(
                label: 'Получить код',
                onTap: handleNext,
                isEnabled: isValid,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
