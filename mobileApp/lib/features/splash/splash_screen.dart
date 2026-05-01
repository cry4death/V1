import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/widgets/clinic_logo.dart';
import '../auth/domain/auth_state.dart';
import '../auth/presentation/controllers/auth_controller.dart';
import 'package:google_fonts/google_fonts.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen>
    with TickerProviderStateMixin {
  late AnimationController _waveController;

  @override
  void initState() {
    super.initState();
    _waveController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 3),
    )..repeat();

    Future.delayed(const Duration(milliseconds: 3000), _navigate);
  }

  Future<void> _navigate() async {
    if (!mounted) return;
    await ref.read(authControllerProvider.notifier).bootstrap();
    if (!mounted) return;

    final auth = ref.read(authControllerProvider);
    switch (auth.status) {
      case AuthStatus.registeredLoggedOut:
        context.go(auth.faceIdEnabled ? '/login/faceid' : '/login/pin');
        break;
      case AuthStatus.authenticated:
        context.go('/dashboard');
        break;
      case AuthStatus.unregistered:
      case AuthStatus.unknown:
        context.go('/onboarding');
    }
  }

  @override
  void dispose() {
    _waveController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topRight,
            end: Alignment.bottomLeft,
            colors: [Color(0xFF4682B4), Color(0xFF1E5A99)],
            stops: [0.0, 1.0],
          ),
        ),
        child: Stack(
          children: [
            // Logo + tagline (center)
            Positioned.fill(
              bottom: 160,
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const ClinicLogo(width: 180, color: Colors.white)
                        .animate()
                        .fadeIn(duration: 600.ms, curve: Curves.easeOut)
                        .scale(
                          begin: const Offset(0.88, 0.88),
                          end: const Offset(1, 1),
                          duration: 900.ms,
                          curve: Curves.easeOut,
                        ),
                    const SizedBox(height: 16),
                    Text(
                      'Ваша медицинская клиника',
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        color: Colors.white.withAlpha(217),
                        letterSpacing: 0.8,
                      ),
                    )
                        .animate()
                        .fadeIn(delay: 500.ms, duration: 600.ms),
                  ],
                ),
              ),
            ),

            // Animated waves at bottom
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              height: 160,
              child: AnimatedBuilder(
                animation: _waveController,
                builder: (context, child) {
                  return ClipRect(
                    child: Stack(
                      children: [
                        _WaveLayer(
                          opacity: 0.15,
                          offsetY: 20,
                          phase: _waveController.value * 2 * math.pi,
                        ),
                        _WaveLayer(
                          opacity: 0.25,
                          offsetY: 5,
                          phase: _waveController.value * 2 * math.pi + 1.0,
                        ),
                        _WaveLayer(
                          opacity: 0.45,
                          offsetY: -10,
                          phase: _waveController.value * 2 * math.pi + 2.0,
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),

          ],
        ),
      ),
    );
  }
}

class _WaveLayer extends StatelessWidget {
  final double opacity;
  final double offsetY;
  final double phase;

  const _WaveLayer({
    required this.opacity,
    required this.offsetY,
    required this.phase,
  });

  @override
  Widget build(BuildContext context) {
    return Positioned(
      bottom: offsetY,
      left: 0,
      right: 0,
      height: 90,
      child: CustomPaint(
        painter: _WavePainter(opacity: opacity, phase: phase),
      ),
    );
  }
}

class _WavePainter extends CustomPainter {
  final double opacity;
  final double phase;

  _WavePainter({required this.opacity, required this.phase});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withValues(alpha: opacity)
      ..style = PaintingStyle.fill;

    final path = Path();
    path.moveTo(0, size.height * 0.5);

    for (double x = 0; x <= size.width; x++) {
      final y = size.height * 0.5 +
          math.sin((x / size.width) * 2 * math.pi + phase) * size.height * 0.25;
      path.lineTo(x, y);
    }

    path.lineTo(size.width, size.height);
    path.lineTo(0, size.height);
    path.close();

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(_WavePainter oldDelegate) =>
      oldDelegate.phase != phase || oldDelegate.opacity != opacity;
}
