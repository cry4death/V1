import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../core/constants/app_colors.dart';
import '../../core/network/api_exception.dart';
import '../../core/widgets/primary_button.dart';
import '../auth/data/patient_auth_providers.dart';

class LoginPhoneScreen extends ConsumerStatefulWidget {
  final String phone;
  final ValueChanged<String> onNext;

  const LoginPhoneScreen({
    super.key,
    required this.phone,
    required this.onNext,
  });

  @override
  ConsumerState<LoginPhoneScreen> createState() => _LoginPhoneScreenState();
}

class _LoginPhoneScreenState extends ConsumerState<LoginPhoneScreen> {
  late final TextEditingController _ctrl;
  bool _touched = false;
  String _phone = '';
  bool _loading = false;
  String? _phoneApiMessage;
  bool _showRegisterCta = false;

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

  Future<void> _requestOtp() async {
    setState(() => _touched = true);
    if (!_isValid || _loading) return;

    setState(() {
      _loading = true;
      _phoneApiMessage = null;
      _showRegisterCta = false;
    });
    try {
      await ref.read(patientAuthRepositoryProvider).requestLoginOtp(
            phone: '375$_phone',
          );
      if (!mounted) return;
      widget.onNext(_phone);
    } on DioException catch (e) {
      if (!mounted) return;
      if (e.response?.statusCode == 422) {
        final regMsg = _firstPhoneValidationMessage(e);
        if (regMsg != null) {
          setState(() {
            _phoneApiMessage = regMsg;
            _showRegisterCta = regMsg == 'Номер не найден';
          });
          return;
        }
      }
      final msg = e.error is ApiException
          ? (e.error! as ApiException).message
          : (e.message ?? 'Ошибка сети');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg)),
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
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
                                      _phoneApiMessage = null;
                                      _showRegisterCta = false;
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
                        if (_phoneApiMessage != null) ...[
                          const SizedBox(height: 8),
                          Text(
                            _phoneApiMessage!,
                            style: GoogleFonts.inter(
                              fontSize: 13,
                              color: AppColors.error,
                              height: 1.4,
                            ),
                          ),
                          if (_showRegisterCta)
                            Align(
                              alignment: Alignment.centerLeft,
                              child: TextButton(
                                onPressed: () => context.go('/register'),
                                child: Text(
                                  'Зарегистрироваться',
                                  style: GoogleFonts.inter(
                                    fontSize: 14,
                                    fontWeight: FontWeight.w600,
                                    color: AppColors.primary,
                                  ),
                                ),
                              ),
                            ),
                        ],
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
                label: _loading ? 'Отправка…' : 'Получить код',
                isEnabled: _isValid && !_loading,
                onTap: _requestOtp,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

String? _firstPhoneValidationMessage(DioException e) {
  final data = e.response?.data;
  if (data is! Map) return null;
  final errors = data['errors'];
  if (errors is! Map) return null;
  final phoneErr = errors['phone'];
  if (phoneErr is List && phoneErr.isNotEmpty && phoneErr.first is String) {
    return phoneErr.first as String;
  }
  return null;
}
