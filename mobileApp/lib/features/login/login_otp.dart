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
import '../auth/presentation/controllers/auth_controller.dart';
import '../registration/steps/face_id_setup.dart';
import '../registration/steps/pin_setup.dart';

class LoginOtpScreen extends ConsumerStatefulWidget {
  final String phone;
  final VoidCallback onBack;

  const LoginOtpScreen({
    super.key,
    required this.phone,
    required this.onBack,
  });

  @override
  ConsumerState<LoginOtpScreen> createState() => _LoginOtpScreenState();
}

class _LoginOtpScreenState extends ConsumerState<LoginOtpScreen> {
  static const _otpLength = 6;
  final List<TextEditingController> _controllers =
      List.generate(_otpLength, (_) => TextEditingController());
  final List<FocusNode> _focusNodes =
      List.generate(_otpLength, (_) => FocusNode());

  bool _isSuccess = false;
  bool _busy = false;

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
    if (_isComplete && !_busy) {
      _confirm();
    }
  }

  Future<void> _confirm() async {
    if (!_isComplete || _busy) return;
    FocusScope.of(context).unfocus();

    setState(() => _busy = true);
    try {
      final result = await ref.read(patientAuthRepositoryProvider).verifyLoginOtp(
            phone: '375${widget.phone}',
            otp: _otp,
          );
      await ref.read(authControllerProvider.notifier).completeLoginWithApiToken(
            accessToken: result.token,
            patient: result.patient,
          );
      if (!mounted) return;
      setState(() {
        _isSuccess = true;
        _busy = false;
      });
      // После OTP-входа — установка PIN (а не сразу дашборд).
      Future.delayed(const Duration(milliseconds: 1600), () {
        if (!mounted) return;
        Navigator.of(context).push(
          MaterialPageRoute<void>(
            builder: (_) => PinSetupScreen(
              showProgress: false,
              onNext: () {
                // После PIN → запрос биометрии (или пропуск).
                // FaceIdSetupScreen сама вызовет markAuthenticated() + go('/dashboard').
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute<void>(
                    builder: (_) => const FaceIdSetupScreen(showProgress: false),
                  ),
                );
              },
            ),
          ),
        );
      });
    } on DioException catch (e) {
      if (!mounted) return;
      final msg = e.error is ApiException
          ? (e.error! as ApiException).message
          : (e.message ?? 'Ошибка сети');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg)),
      );
      for (final c in _controllers) {
        c.clear();
      }
      _focusNodes[0].requestFocus();
      setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isSuccess) {
      return const Scaffold(
        backgroundColor: Colors.white,
        body: SafeArea(child: _SuccessView()),
      );
    }

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 8, 20, 0),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: widget.onBack,
                    child: const SizedBox(
                      width: 40,
                      height: 40,
                      child: Icon(
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
                          'Введите код из SMS',
                          style: GoogleFonts.inter(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF101623),
                            height: 1.2,
                          ),
                        ),
                        GestureDetector(
                          onTap: widget.onBack,
                          child: Text(
                            'Код отправлен на +375${widget.phone}',
                            style: GoogleFonts.inter(
                              fontSize: 13,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ).animate().fadeIn(duration: 300.ms),

            Expanded(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 28),
                child: Column(
                  children: [
                    const SizedBox(height: 36),
                    Row(
                      children: [
                        for (var i = 0; i < _otpLength; i++) ...[
                          if (i > 0) const SizedBox(width: 6),
                          Expanded(
                            child: SizedBox(
                              height: 54,
                              child: TextField(
                                controller: _controllers[i],
                                focusNode: _focusNodes[i],
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
                                    borderSide: const BorderSide(
                                        color: AppColors.border, width: 1.5),
                                  ),
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                    borderSide: BorderSide(
                                      color: _controllers[i].text.isNotEmpty
                                          ? AppColors.primary
                                          : AppColors.border,
                                      width: 1.5,
                                    ),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                    borderSide: const BorderSide(
                                        color: AppColors.primary, width: 2),
                                  ),
                                ),
                                style: GoogleFonts.inter(
                                  fontSize: 22,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.textPrimary,
                                ),
                                onChanged: (v) => _onDigitChanged(i, v),
                                onTapOutside: (_) =>
                                    FocusScope.of(context).unfocus(),
                              ),
                            ),
                          ),
                        ],
                      ],
                    ).animate().fadeIn(delay: 150.ms).moveY(begin: 20, end: 0),
                  ],
                ),
              ),
            ),

            Padding(
              padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
              child: PrimaryButton(
                label: _busy ? 'Проверка…' : 'Войти',
                isEnabled: _isComplete && !_busy,
                onTap: _confirm,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SuccessView extends StatelessWidget {
  const _SuccessView();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          _CheckmarkCircle(),
          const SizedBox(height: 24),
          Text(
            'Вход выполнен!',
            style: GoogleFonts.inter(
              fontSize: 22,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF101623),
            ),
          ).animate().fadeIn(delay: 300.ms),
          const SizedBox(height: 8),
          Text(
            'Добро пожаловать обратно…',
            style: GoogleFonts.inter(
              fontSize: 15,
              color: AppColors.textSecondary,
            ),
          ).animate().fadeIn(delay: 400.ms),
        ],
      ),
    );
  }
}

class _CheckmarkCircle extends StatefulWidget {
  @override
  State<_CheckmarkCircle> createState() => _CheckmarkCircleState();
}

class _CheckmarkCircleState extends State<_CheckmarkCircle>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _scale;
  late final Animation<double> _check;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    );
    _scale = CurvedAnimation(
      parent: _ctrl,
      curve: const Interval(0.0, 0.5, curve: Curves.easeOutBack),
    );
    _check = CurvedAnimation(
      parent: _ctrl,
      curve: const Interval(0.3, 0.9, curve: Curves.easeOut),
    );
    _ctrl.forward();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _ctrl,
      builder: (_, _) {
        return Transform.scale(
          scale: _scale.value,
          child: Container(
            width: 88,
            height: 88,
            decoration: BoxDecoration(
              gradient: AppColors.primaryGradient,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: AppColors.primary.withAlpha(115),
                  blurRadius: 36,
                  spreadRadius: 4,
                ),
              ],
            ),
            child: CustomPaint(
              painter: _CheckPainter(progress: _check.value),
            ),
          ),
        );
      },
    );
  }
}

class _CheckPainter extends CustomPainter {
  final double progress;
  const _CheckPainter({required this.progress});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white
      ..strokeWidth = 3.5
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round
      ..style = PaintingStyle.stroke;

    final cx = size.width / 2;
    final cy = size.height / 2;

    final path = Path()
      ..moveTo(cx - 14, cy)
      ..lineTo(cx - 4, cy + 10)
      ..lineTo(cx + 14, cy - 10);

    final metrics = path.computeMetrics().first;
    final drawn = metrics.extractPath(0, metrics.length * progress);
    canvas.drawPath(drawn, paint);
  }

  @override
  bool shouldRepaint(_CheckPainter old) => old.progress != progress;
}
