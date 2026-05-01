import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/constants/app_colors.dart';
import '../../core/widgets/primary_button.dart';

class LoginPhoneScreen extends StatefulWidget {
  final String phone;
  final ValueChanged<String> onNext;

  const LoginPhoneScreen({
    super.key,
    required this.phone,
    required this.onNext,
  });

  @override
  State<LoginPhoneScreen> createState() => _LoginPhoneScreenState();
}

class _LoginPhoneScreenState extends State<LoginPhoneScreen> {
  late final TextEditingController _ctrl;
  bool _touched = false;
  String _phone = '';

  bool get _isValid => _phone.length == 9;

  @override
  void initState() {
    super.initState();
    _phone = widget.phone;
    _ctrl = TextEditingController(text: _phone);
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Top header row (back button + title) ──
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 8, 20, 0),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: () => context.go('/auth'),
                    child: Container(
                      width: 40,
                      height: 40,
                      decoration: const BoxDecoration(
                        color: Colors.transparent,
                      ),
                      child: const Icon(
                        Icons.arrow_back_ios_new,
                        size: 20,
                        color: Color(0xFF101623),
                      ),
                    ),
                  ),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Вход в аккаунт',
                          style: GoogleFonts.inter(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF101623),
                            height: 1.2,
                          ),
                        ),
                        Text(
                          'Введите номер, указанный при регистрации',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            color: AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ).animate().fadeIn(duration: 300.ms),

            // ── Content ──
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 24),

                    // Phone field
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Номер телефона',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: const Color(0xFF444444),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Container(
                          height: 56,
                          decoration: BoxDecoration(
                            color: AppColors.inputFill,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: _touched && !_isValid
                                  ? AppColors.error
                                  : _isValid
                                      ? AppColors.primary
                                      : AppColors.border,
                              width: 2,
                            ),
                          ),
                          child: Row(
                            children: [
                              Padding(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 14),
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
                                  color: AppColors.border),
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
                                        color: AppColors.textHint),
                                    border: InputBorder.none,
                                    contentPadding:
                                        const EdgeInsets.symmetric(
                                            horizontal: 14),
                                  ),
                                  style: GoogleFonts.inter(
                                    fontSize: 16,
                                    color: AppColors.textPrimary,
                                    letterSpacing: 1,
                                  ),
                                  onChanged: (v) {
                                    setState(() {
                                      _phone = v;
                                      _touched = true;
                                    });
                                  },
                                ),
                              ),
                              if (_isValid)
                                Padding(
                                  padding: const EdgeInsets.only(right: 14),
                                  child: Container(
                                    width: 20,
                                    height: 20,
                                    decoration: const BoxDecoration(
                                      color: AppColors.primary,
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(
                                      Icons.check,
                                      size: 13,
                                      color: Colors.white,
                                    ),
                                  ).animate().scale(
                                      begin: const Offset(0, 0),
                                      end: const Offset(1, 1),
                                      duration: 200.ms,
                                      curve: Curves.easeOutBack),
                                ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          'Формат: +375 (XX) XXX-XX-XX',
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            color: const Color(0xFFA1A8B0),
                          ),
                        ),
                      ],
                    ).animate().fadeIn(delay: 100.ms),

                    const SizedBox(height: 16),

                    // Info block
                    Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFE8F4FD),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      padding: const EdgeInsets.all(14),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(
                            Icons.info_outline,
                            size: 18,
                            color: AppColors.primary,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'На указанный номер будет отправлен код подтверждения. Стандартные тарифы оператора.',
                              style: GoogleFonts.inter(
                                fontSize: 13,
                                color: const Color(0xFF1E5A99),
                                height: 1.55,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ).animate().fadeIn(delay: 160.ms),
                  ],
                ),
              ),
            ),

            // ── Bottom button ──
            Padding(
              padding:
                  const EdgeInsets.fromLTRB(20, 0, 20, 12),
              child: PrimaryButton(
                label: 'Получить код',
                isEnabled: _isValid,
                onTap: () {
                  setState(() => _touched = true);
                  if (_isValid) widget.onNext(_phone);
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}
