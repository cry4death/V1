import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/biometric/biometric_auth_service.dart';
import '../../core/constants/app_colors.dart';
import '../../core/storage/providers.dart';
import '../../core/widgets/clinic_logo.dart';
import '../auth/presentation/controllers/auth_controller.dart';

enum _ScanStatus { idle, scanning, success, failed }

class LoginFaceIdScreen extends ConsumerStatefulWidget {
  const LoginFaceIdScreen({super.key});

  @override
  ConsumerState<LoginFaceIdScreen> createState() => _LoginFaceIdScreenState();
}

class _LoginFaceIdScreenState extends ConsumerState<LoginFaceIdScreen>
    with TickerProviderStateMixin {
  _ScanStatus _status = _ScanStatus.idle;

  late final AnimationController _scanLineCtrl;
  late final AnimationController _pulseCtrl;

  @override
  void initState() {
    super.initState();
    _scanLineCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1800),
    );
    _pulseCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1600),
    );
    ref.read(authControllerProvider.notifier).bootstrap();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Future.delayed(const Duration(milliseconds: 500), () {
        if (mounted) _triggerScan();
      });
    });
  }

  @override
  void dispose() {
    _scanLineCtrl.dispose();
    _pulseCtrl.dispose();
    super.dispose();
  }

  Future<void> _triggerScan() async {
    if (_status == _ScanStatus.scanning || _status == _ScanStatus.success) {
      return;
    }

    setState(() => _status = _ScanStatus.scanning);
    _scanLineCtrl.repeat();
    _pulseCtrl.repeat();

    try {
      final biometric = ref.read(biometricAuthServiceProvider);
      final ok = await biometric.authenticate(
        localizedReason: 'Подтвердите личность для входа в приложение',
      );
      if (!mounted) return;

      if (ok) {
        setState(() => _status = _ScanStatus.success);
        ref.read(authControllerProvider.notifier).markAuthenticated();
        Future.delayed(const Duration(milliseconds: 1000), () {
          if (mounted) context.go('/dashboard');
        });
      } else {
        setState(() => _status = _ScanStatus.failed);
      }
    } on PlatformException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(biometricPlatformErrorMessage(e))),
      );
      setState(() => _status = _ScanStatus.failed);
    } finally {
      if (mounted) {
        _scanLineCtrl.stop();
        _pulseCtrl.stop();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final firstName = ref.watch(
        authControllerProvider.select((s) => s.firstName));
    final isScanning = _status == _ScanStatus.scanning;
    final isSuccess = _status == _ScanStatus.success;
    final isFailed = _status == _ScanStatus.failed;
    final showButtons = !isScanning && !isSuccess;

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            const SizedBox(height: 28),

            const ClinicLogo(width: 100, color: AppColors.primary)
                .animate()
                .fadeIn(duration: 400.ms),

            const Spacer(),

            // ── Face ID icon area ─────────────────────────────────────────
            SizedBox(
              width: 200,
              height: 200,
              child: Stack(
                alignment: Alignment.center,
                children: [
                  // Pulse rings when scanning
                  if (isScanning) ...[
                    _PulseRing(controller: _pulseCtrl, delayFraction: 0.0),
                    _PulseRing(controller: _pulseCtrl, delayFraction: 0.45),
                  ],

                  // Main icon container
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 400),
                    width: 128,
                    height: 128,
                    decoration: BoxDecoration(
                      color: isSuccess
                          ? const Color(0xFFD1FAE5)
                          : const Color(0xFFE8F4FD),
                      borderRadius: BorderRadius.circular(40),
                    ),
                    child: isSuccess
                        ? const _SuccessCheck()
                        : CustomPaint(
                            painter: _FaceIdPainter(
                              scanning: isScanning,
                              scanAnim: _scanLineCtrl,
                            ),
                          ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 32),

            // ── Status text ───────────────────────────────────────────────
            AnimatedSwitcher(
              duration: const Duration(milliseconds: 250),
              transitionBuilder: (child, animation) => FadeTransition(
                opacity: animation,
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0, 0.25),
                    end: Offset.zero,
                  ).animate(CurvedAnimation(
                    parent: animation,
                    curve: Curves.easeOut,
                  )),
                  child: child,
                ),
              ),
              child: Column(
                key: ValueKey(_status),
                children: [
                  Text(
                    'Добро пожаловать,',
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      color: AppColors.textSecondary,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    firstName,
                    style: GoogleFonts.inter(
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    isScanning
                        ? 'Посмотрите на экран телефона…'
                        : isSuccess
                            ? 'Вход выполнен успешно'
                            : isFailed
                                ? 'Face ID не распознан'
                                : 'Вход с помощью Face ID',
                    style: GoogleFonts.inter(
                      fontSize: 15,
                      color: isSuccess
                          ? AppColors.success
                          : isFailed
                              ? AppColors.error
                              : AppColors.textSecondary,
                      fontWeight: isSuccess || isFailed
                          ? FontWeight.w500
                          : FontWeight.w400,
                    ),
                  ),
                ],
              ),
            ),

            const Spacer(),

            // ── Action buttons ────────────────────────────────────────────
            AnimatedSwitcher(
              duration: const Duration(milliseconds: 200),
              child: showButtons
                  ? Padding(
                      key: const ValueKey('buttons'),
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      child: Column(
                        children: [
                          GestureDetector(
                            onTap: _triggerScan,
                            child: Container(
                              height: 54,
                              decoration: BoxDecoration(
                                gradient: AppColors.primaryGradient,
                                borderRadius: BorderRadius.circular(30),
                                boxShadow: [
                                  BoxShadow(
                                    color: AppColors.primary.withAlpha(80),
                                    blurRadius: 20,
                                    offset: const Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  _FaceIdInlineIcon(
                                    size: 22,
                                    color: Colors.white,
                                  ),
                                  const SizedBox(width: 10),
                                  Text(
                                    isFailed
                                        ? 'Попробовать снова'
                                        : 'Войти с Face ID',
                                    style: GoogleFonts.inter(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w600,
                                      color: Colors.white,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ).animate().fadeIn(duration: 300.ms),
                          const SizedBox(height: 4),
                          GestureDetector(
                            onTap: () => context.go('/login/pin'),
                            child: Container(
                              height: 48,
                              alignment: Alignment.center,
                              child: Text(
                                'Ввести PIN-код',
                                style: GoogleFonts.inter(
                                  fontSize: 15,
                                  color: AppColors.textSecondary,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    )
                  : const SizedBox(key: ValueKey('empty'), height: 106),
            ),

            // ── Security note ─────────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 16),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
                decoration: BoxDecoration(
                  color: const Color(0xFFE8F4FD),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.shield_outlined,
                      size: 15,
                      color: AppColors.primary,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'Биометрия обрабатывается системой телефона. На многих Android в приложениях доступен только отпечаток, а «лицо» остаётся для разблокировки экрана — это нормально. Данные не отправляются на сервер.',
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          color: const Color(0xFF1E5A99),
                          height: 1.5,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ).animate().fadeIn(delay: 300.ms),
          ],
        ),
      ),
    );
  }
}

// ─── Pulse ring ───────────────────────────────────────────────────────────────

class _PulseRing extends StatelessWidget {
  final AnimationController controller;
  final double delayFraction;

  const _PulseRing({required this.controller, required this.delayFraction});

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: controller,
      builder: (_, _) {
        final progress = (controller.value + delayFraction) % 1.0;
        final opacity = (1.0 - progress) * 0.55;
        final size = 128.0 + progress * 72.0;

        return Opacity(
          opacity: opacity.clamp(0.0, 1.0),
          child: Container(
            width: size,
            height: size,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(
                color: AppColors.primary.withAlpha(100),
                width: 2,
              ),
            ),
          ),
        );
      },
    );
  }
}

// ─── Success checkmark ────────────────────────────────────────────────────────

class _SuccessCheck extends StatelessWidget {
  const _SuccessCheck();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Container(
        width: 68,
        height: 68,
        decoration: const BoxDecoration(
          color: Color(0xFF22C55E),
          shape: BoxShape.circle,
        ),
        child: const Icon(Icons.check_rounded, color: Colors.white, size: 36),
      )
          .animate()
          .scale(
            begin: const Offset(0.3, 0.3),
            end: const Offset(1.0, 1.0),
            duration: 380.ms,
            curve: Curves.easeOutBack,
          )
          .fadeIn(duration: 200.ms),
    );
  }
}

// ─── Face ID painter ──────────────────────────────────────────────────────────

class _FaceIdPainter extends CustomPainter {
  final bool scanning;
  final Animation<double> scanAnim;

  const _FaceIdPainter({
    required this.scanning,
    required this.scanAnim,
  }) : super(repaint: scanAnim);

  @override
  void paint(Canvas canvas, Size size) {
    final s = size.width / 80;

    final bracketPaint = Paint()
      ..color = AppColors.primary
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3.5 * s
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;

    final r = Radius.circular(4 * s);

    // ── Corner brackets ─────────────────────────────────────────────────────
    // Top-left
    canvas.drawPath(
      Path()
        ..moveTo(4 * s, 20 * s)
        ..lineTo(4 * s, 8 * s)
        ..arcToPoint(Offset(8 * s, 4 * s), radius: r)
        ..lineTo(20 * s, 4 * s),
      bracketPaint,
    );
    // Top-right
    canvas.drawPath(
      Path()
        ..moveTo(60 * s, 4 * s)
        ..lineTo(72 * s, 4 * s)
        ..arcToPoint(Offset(76 * s, 8 * s), radius: r)
        ..lineTo(76 * s, 20 * s),
      bracketPaint,
    );
    // Bottom-right
    canvas.drawPath(
      Path()
        ..moveTo(76 * s, 60 * s)
        ..lineTo(76 * s, 72 * s)
        ..arcToPoint(Offset(72 * s, 76 * s), radius: r)
        ..lineTo(60 * s, 76 * s),
      bracketPaint,
    );
    // Bottom-left
    canvas.drawPath(
      Path()
        ..moveTo(20 * s, 76 * s)
        ..lineTo(8 * s, 76 * s)
        ..arcToPoint(Offset(4 * s, 72 * s), radius: r)
        ..lineTo(4 * s, 60 * s),
      bracketPaint,
    );

    final featurePaint = Paint()
      ..color = AppColors.primary
      ..strokeCap = StrokeCap.round;

    // ── Eyes ────────────────────────────────────────────────────────────────
    featurePaint.style = PaintingStyle.fill;
    canvas.drawCircle(Offset(28 * s, 30 * s), 3.5 * s, featurePaint);
    canvas.drawCircle(Offset(52 * s, 30 * s), 3.5 * s, featurePaint);

    // ── Nose ────────────────────────────────────────────────────────────────
    featurePaint
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.5 * s;
    canvas.drawLine(Offset(40 * s, 34 * s), Offset(40 * s, 45 * s), featurePaint);

    // ── Smile ────────────────────────────────────────────────────────────────
    canvas.drawPath(
      Path()
        ..moveTo(28 * s, 55 * s)
        ..quadraticBezierTo(40 * s, 65 * s, 52 * s, 55 * s),
      featurePaint,
    );

    // ── Scan line ────────────────────────────────────────────────────────────
    if (scanning) {
      final t = scanAnim.value;
      // Ping-pong: 0→1→0
      final pingPong = t < 0.5 ? t * 2 : (1 - t) * 2;
      final y = (15 + (65 - 15) * pingPong) * s;

      final scanPaint = Paint()
        ..color = AppColors.primary.withAlpha(120)
        ..strokeWidth = 1.8 * s
        ..strokeCap = StrokeCap.round;

      canvas.drawLine(Offset(10 * s, y), Offset(70 * s, y), scanPaint);

      // Gradient glow around scan line
      final glowPaint = Paint()
        ..shader = LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [
            Colors.transparent,
            AppColors.primary.withAlpha(40),
            Colors.transparent,
          ],
        ).createShader(Rect.fromLTWH(0, y - 8 * s, size.width, 16 * s));

      canvas.drawRect(
        Rect.fromLTWH(10 * s, y - 8 * s, 60 * s, 16 * s),
        glowPaint,
      );
    }
  }

  @override
  bool shouldRepaint(_FaceIdPainter old) =>
      scanning != old.scanning;
}

// ─── Inline Face ID icon for button ──────────────────────────────────────────

class _FaceIdInlineIcon extends StatelessWidget {
  final double size;
  final Color color;

  const _FaceIdInlineIcon({required this.size, required this.color});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: size,
      height: size,
      child: CustomPaint(
        painter: _FaceIdBracketPainter(color: color),
      ),
    );
  }
}

class _FaceIdBracketPainter extends CustomPainter {
  final Color color;
  const _FaceIdBracketPainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final s = size.width / 80;
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 4 * s
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;

    final r = Radius.circular(4 * s);

    canvas.drawPath(
      Path()
        ..moveTo(4 * s, 20 * s)
        ..lineTo(4 * s, 8 * s)
        ..arcToPoint(Offset(8 * s, 4 * s), radius: r)
        ..lineTo(20 * s, 4 * s),
      paint,
    );
    canvas.drawPath(
      Path()
        ..moveTo(60 * s, 4 * s)
        ..lineTo(72 * s, 4 * s)
        ..arcToPoint(Offset(76 * s, 8 * s), radius: r)
        ..lineTo(76 * s, 20 * s),
      paint,
    );
    canvas.drawPath(
      Path()
        ..moveTo(76 * s, 60 * s)
        ..lineTo(76 * s, 72 * s)
        ..arcToPoint(Offset(72 * s, 76 * s), radius: r)
        ..lineTo(60 * s, 76 * s),
      paint,
    );
    canvas.drawPath(
      Path()
        ..moveTo(20 * s, 76 * s)
        ..lineTo(8 * s, 76 * s)
        ..arcToPoint(Offset(4 * s, 72 * s), radius: r)
        ..lineTo(4 * s, 60 * s),
      paint,
    );

    paint.style = PaintingStyle.fill;
    canvas.drawCircle(Offset(28 * s, 30 * s), 4 * s, paint);
    canvas.drawCircle(Offset(52 * s, 30 * s), 4 * s, paint);

    paint
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3 * s;
    canvas.drawLine(Offset(40 * s, 34 * s), Offset(40 * s, 45 * s), paint);
    canvas.drawPath(
      Path()
        ..moveTo(28 * s, 55 * s)
        ..quadraticBezierTo(40 * s, 65 * s, 52 * s, 55 * s),
      paint,
    );
  }

  @override
  bool shouldRepaint(_FaceIdBracketPainter old) => color != old.color;
}

