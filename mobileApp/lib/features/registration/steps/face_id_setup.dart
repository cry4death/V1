import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/widgets/primary_button.dart';
import '../../../core/widgets/outline_button.dart';
import '../../auth/presentation/controllers/auth_controller.dart';
import '_progress_bar.dart';

class FaceIdSetupScreen extends ConsumerWidget {
  const FaceIdSetupScreen({super.key});

  Future<void> _enable(BuildContext context, WidgetRef ref) async {
    await ref.read(authControllerProvider.notifier).setFaceIdEnabled(true);
    ref.read(authControllerProvider.notifier).markAuthenticated();
    if (context.mounted) context.go('/dashboard');
  }

  Future<void> _skip(BuildContext context, WidgetRef ref) async {
    await ref.read(authControllerProvider.notifier).setFaceIdEnabled(false);
    ref.read(authControllerProvider.notifier).markAuthenticated();
    if (context.mounted) context.go('/dashboard');
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            RegistrationProgressBar(step: 5, total: 5),
            const Spacer(),

            // Face ID icon
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: const Color(0xFFE8F4FD),
                borderRadius: BorderRadius.circular(28),
              ),
              child: const _FaceIdIcon(),
            )
                .animate()
                .fadeIn(duration: 400.ms)
                .scale(
                    begin: const Offset(0.8, 0.8),
                    end: const Offset(1, 1),
                    duration: 400.ms),

            const SizedBox(height: 28),

            Text(
              'Войти по Face ID',
              style: GoogleFonts.inter(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF101623),
              ),
            ).animate().fadeIn(delay: 100.ms),

            const SizedBox(height: 12),

            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 40),
              child: Text(
                'Используйте биометрию для быстрого и безопасного входа в приложение',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                  fontSize: 14,
                  color: AppColors.textSecondary,
                  height: 1.6,
                ),
              ),
            ).animate().fadeIn(delay: 150.ms),

            const Spacer(),

            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Column(
                children: [
                  PrimaryButton(
                    label: 'Включить Face ID',
                    onTap: () => _enable(context, ref),
                  ),
                  const SizedBox(height: 12),
                  AppOutlineButton(
                    label: 'Пропустить',
                    onTap: () => _skip(context, ref),
                  ),
                ],
              ),
            ).animate().fadeIn(delay: 200.ms),

            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }
}

class _FaceIdIcon extends StatelessWidget {
  const _FaceIdIcon();

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      painter: _FaceIdPainter(),
    );
  }
}

class _FaceIdPainter extends CustomPainter {
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

    // ── Corner brackets (Apple Face ID style) ────────────────────────────────
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
    canvas.drawLine(
      Offset(40 * s, 34 * s),
      Offset(40 * s, 45 * s),
      featurePaint,
    );

    // ── Smile ────────────────────────────────────────────────────────────────
    canvas.drawPath(
      Path()
        ..moveTo(28 * s, 55 * s)
        ..quadraticBezierTo(40 * s, 65 * s, 52 * s, 55 * s),
      featurePaint,
    );
  }

  @override
  bool shouldRepaint(_FaceIdPainter old) => false;
}
