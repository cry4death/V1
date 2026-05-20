import 'dart:async';
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
import '../registration/steps/_numpad.dart';

class LoginPinScreen extends ConsumerStatefulWidget {
  const LoginPinScreen({super.key});

  @override
  ConsumerState<LoginPinScreen> createState() => _LoginPinScreenState();
}

class _LoginPinScreenState extends ConsumerState<LoginPinScreen> {
  static const _pinLength = 4;
  static const _maxAttempts = 5;
  static const _lockDurationSec = 5 * 60;

  String _current = '';
  int _shakeKey = 0;
  _Status _status = _Status.idle;
  int _attempts = 0;
  int? _lockUntilMs;
  int _lockRemaining = 0;
  Timer? _lockTimer;

  String _storedPin = '0000';

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    await ref.read(authControllerProvider.notifier).bootstrap();
    _storedPin = await ref.read(authControllerProvider.notifier).getStoredPin();
    if (mounted) setState(() {});
  }

  @override
  void dispose() {
    _lockTimer?.cancel();
    super.dispose();
  }

  void _startLockTimer() {
    _lockTimer?.cancel();
    _lockTimer = Timer.periodic(const Duration(milliseconds: 500), (timer) {
      if (_lockUntilMs == null) {
        timer.cancel();
        return;
      }
      final remaining =
          ((_lockUntilMs! - DateTime.now().millisecondsSinceEpoch) / 1000)
              .ceil();
      if (remaining <= 0) {
        setState(() {
          _lockUntilMs = null;
          _attempts = 0;
          _status = _Status.idle;
          _current = '';
          _lockRemaining = 0;
        });
        timer.cancel();
      } else {
        setState(() => _lockRemaining = remaining);
      }
    });
  }

  void _addDigit(String d) {
    if (_status == _Status.locked || _status == _Status.success) return;
    if (_current.length >= _pinLength) return;

    final next = _current + d;
    setState(() {
      _current = next;
      _status = _Status.idle;
    });

    if (next.length == _pinLength) {
      Future.delayed(const Duration(milliseconds: 200), () => _checkPin(next));
    }
  }

  void _deleteDigit() {
    if (_status == _Status.locked || _status == _Status.success) return;
    setState(() {
      if (_current.isNotEmpty) {
        _current = _current.substring(0, _current.length - 1);
      }
      _status = _Status.idle;
    });
  }

  Future<void> _tryBiometric() async {
    if (_status == _Status.locked || _status == _Status.success) return;

    try {
      final biometric = ref.read(biometricAuthServiceProvider);
      final ok = await biometric.authenticate(
        localizedReason: 'Подтвердите личность для входа в приложение',
      );
      if (!mounted) return;
      if (ok) {
        setState(() => _status = _Status.success);
        ref.read(authControllerProvider.notifier).markAuthenticated();
        Future.delayed(const Duration(milliseconds: 900), () {
          if (mounted) context.go('/dashboard');
        });
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Вход по биометрии отменён. Попробуйте снова или введите PIN.'),
          ),
        );
      }
    } on PlatformException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(biometricPlatformErrorMessage(e))),
      );
    }
  }

  void _checkPin(String pin) {
    if (pin == _storedPin) {
      setState(() => _status = _Status.success);
      ref.read(authControllerProvider.notifier).markAuthenticated();
      Future.delayed(const Duration(milliseconds: 900), () {
        if (mounted) context.go('/dashboard');
      });
    } else {
      final nextAttempts = _attempts + 1;
      setState(() {
        _attempts = nextAttempts;
        _shakeKey++;
        _status = _Status.error;
      });

      if (nextAttempts >= _maxAttempts) {
        setState(() {
          _lockUntilMs = DateTime.now().millisecondsSinceEpoch +
              _lockDurationSec * 1000;
          _lockRemaining = _lockDurationSec;
          _status = _Status.locked;
        });
        _startLockTimer();
      }

      Future.delayed(const Duration(milliseconds: 600), () {
        if (mounted && nextAttempts < _maxAttempts) {
          setState(() {
            _current = '';
            _status = _Status.idle;
          });
        }
      });
    }
  }

  Color _dotColor(int index) {
    if (_status == _Status.success) return AppColors.success;
    if (_status == _Status.error) return AppColors.error;
    return index < _current.length ? AppColors.primary : Colors.transparent;
  }

  String _formatLock(int secs) {
    final m = secs ~/ 60;
    final s = secs % 60;
    return '$m:${s.toString().padLeft(2, '0')}';
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final firstName = auth.firstName;
    final faceIdEnabled = auth.faceIdEnabled;
    final isDisabled =
        _status == _Status.locked || _status == _Status.success;

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            // Back button top-left
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: () => context.go('/login'),
                    child: Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: const Color(0xFFF4F8FB),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.arrow_back_ios_new,
                        size: 16,
                        color: Color(0xFF101623),
                      ),
                    ),
                  ),
                ],
              ),
            ).animate().fadeIn(duration: 300.ms),

            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Column(
                  children: [
                    const SizedBox(height: 16),
                    const ClinicLogo(width: 110, color: AppColors.primary)
                        .animate()
                        .fadeIn(duration: 400.ms),
                    const SizedBox(height: 14),
                    Text(
                      'Добро пожаловать, ',
                      style: GoogleFonts.inter(
                          fontSize: 15, color: AppColors.textSecondary),
                    ),
                    Text(
                      firstName,
                      style: GoogleFonts.inter(
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Введите PIN-код',
                      style: GoogleFonts.inter(
                        fontSize: 20,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 36),
                    TweenAnimationBuilder<double>(
                      key: ValueKey(_shakeKey),
                      tween: _status == _Status.error
                          ? Tween(begin: -10.0, end: 0.0)
                          : Tween(begin: 0.0, end: 0.0),
                      duration: const Duration(milliseconds: 400),
                      curve: Curves.elasticOut,
                      builder: (context, offset, child) => Transform.translate(
                          offset: Offset(offset, 0), child: child),
                      child: _status == _Status.success
                          ? const _CheckIcon()
                          : Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: List.generate(_pinLength, (i) {
                                final color = _dotColor(i);
                                final isFilled = i < _current.length;
                                return _PinDot(
                                  isFilled: isFilled,
                                  color: color,
                                );
                              }),
                            ),
                    ),
                    const SizedBox(height: 4),
                    SizedBox(
                      height: 36,
                      child: Center(
                        child: _status == _Status.locked
                            ? Text(
                                'Слишком много попыток. Повторите через ${_formatLock(_lockRemaining)}',
                                textAlign: TextAlign.center,
                                style: GoogleFonts.inter(
                                    fontSize: 13, color: AppColors.error),
                              )
                            : _status == _Status.error && _attempts < _maxAttempts
                                ? Text(
                                    'Неверный PIN. Осталось попыток: ${_maxAttempts - _attempts}',
                                    style: GoogleFonts.inter(
                                        fontSize: 13,
                                        color: AppColors.error),
                                  )
                                : null,
                      ),
                    ),
                    if (faceIdEnabled && !isDisabled) ...[
                      const SizedBox(height: 8),
                      GestureDetector(
                        onTap: _tryBiometric,
                        child: Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: Column(
                            children: [
                              _FaceIdSvgIcon(
                                  size: 30, color: AppColors.primary),
                              const SizedBox(height: 4),
                              Text(
                                'Face ID',
                                style: GoogleFonts.inter(
                                    fontSize: 12,
                                    color: AppColors.primary,
                                    fontWeight: FontWeight.w500),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                    const SizedBox(height: 8),
                  ],
                ),
              ),
            ),

            Numpad(
              onDigit: _addDigit,
              onDelete: _deleteDigit,
              isDisabled: isDisabled,
            ),

            const SizedBox(height: 10),
          ],
        ),
      ),
    );
  }
}

enum _Status { idle, error, success, locked }

class _CheckIcon extends StatelessWidget {
  const _CheckIcon();

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 52,
      height: 52,
      decoration: const BoxDecoration(
        color: AppColors.success,
        shape: BoxShape.circle,
      ),
      child: const Icon(Icons.check, color: Colors.white, size: 28),
    ).animate().scale(
          begin: const Offset(0.5, 0.5),
          end: const Offset(1, 1),
          duration: 300.ms,
          curve: Curves.easeOut,
        );
  }
}

// ─── Pin Dot with spring animation ───────────────────────────────────────────

class _PinDot extends StatefulWidget {
  final bool isFilled;
  final Color color;

  const _PinDot({required this.isFilled, required this.color});

  @override
  State<_PinDot> createState() => _PinDotState();
}

class _PinDotState extends State<_PinDot> with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _scale;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 260),
      value: widget.isFilled ? 1.0 : 0.0,
    );
    _scale = CurvedAnimation(parent: _ctrl, curve: Curves.easeOutBack);
  }

  @override
  void didUpdateWidget(_PinDot old) {
    super.didUpdateWidget(old);
    if (widget.isFilled != old.isFilled) {
      if (widget.isFilled) {
        _ctrl.forward(from: 0.0);
      } else {
        _ctrl.animateTo(
          0.0,
          duration: const Duration(milliseconds: 180),
          curve: Curves.easeIn,
        );
      }
    }
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _scale,
      builder: (_, _) => Container(
        width: 38,
        height: 38,
        margin: const EdgeInsets.symmetric(horizontal: 5),
        alignment: Alignment.center,
        child: Stack(
          alignment: Alignment.center,
          children: [
            // Empty ring (always visible)
            Container(
              width: 16,
              height: 16,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: widget.isFilled
                      ? widget.color
                      : const Color(0xFFC8CDD4),
                  width: 2,
                ),
              ),
            ),
            // Filled dot with spring scale
            Transform.scale(
              scale: _scale.value,
              child: Container(
                width: 16,
                height: 16,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: widget.color,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Inline Face ID SVG icon ─────────────────────────────────────────────────

class _FaceIdSvgIcon extends StatelessWidget {
  final double size;
  final Color color;

  const _FaceIdSvgIcon({required this.size, required this.color});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: size,
      height: size,
      child: CustomPaint(painter: _FaceIdBracketPainter(color: color)),
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
      ..strokeWidth = 3.5 * s
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;

    final r = Radius.circular(4 * s);

    // Top-left bracket
    canvas.drawPath(
      Path()
        ..moveTo(4 * s, 20 * s)
        ..lineTo(4 * s, 8 * s)
        ..arcToPoint(Offset(8 * s, 4 * s), radius: r)
        ..lineTo(20 * s, 4 * s),
      paint,
    );
    // Top-right bracket
    canvas.drawPath(
      Path()
        ..moveTo(60 * s, 4 * s)
        ..lineTo(72 * s, 4 * s)
        ..arcToPoint(Offset(76 * s, 8 * s), radius: r)
        ..lineTo(76 * s, 20 * s),
      paint,
    );
    // Bottom-right bracket
    canvas.drawPath(
      Path()
        ..moveTo(76 * s, 60 * s)
        ..lineTo(76 * s, 72 * s)
        ..arcToPoint(Offset(72 * s, 76 * s), radius: r)
        ..lineTo(60 * s, 76 * s),
      paint,
    );
    // Bottom-left bracket
    canvas.drawPath(
      Path()
        ..moveTo(20 * s, 76 * s)
        ..lineTo(8 * s, 76 * s)
        ..arcToPoint(Offset(4 * s, 72 * s), radius: r)
        ..lineTo(4 * s, 60 * s),
      paint,
    );

    // Eyes
    paint.style = PaintingStyle.fill;
    canvas.drawCircle(Offset(28 * s, 30 * s), 3.5 * s, paint);
    canvas.drawCircle(Offset(52 * s, 30 * s), 3.5 * s, paint);

    // Nose
    paint
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.5 * s;
    canvas.drawLine(Offset(40 * s, 34 * s), Offset(40 * s, 45 * s), paint);

    // Smile
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
