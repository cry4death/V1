import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/constants/app_colors.dart';
import '../../core/widgets/clinic_logo.dart';
import '../../core/widgets/primary_button.dart';
import '../../core/widgets/outline_button.dart';

class AuthScreen extends StatelessWidget {
  const AuthScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            // Main content
            Expanded(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 32),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Logo
                    const ClinicLogo(width: 160, color: AppColors.primary)
                        .animate()
                        .fadeIn(duration: 600.ms, curve: Curves.easeOut)
                        .moveY(begin: -20, end: 0, duration: 600.ms),

                    const SizedBox(height: 36),

                    // Heading
                    Column(
                      children: [
                        Text(
                          'Начнём!',
                          style: GoogleFonts.inter(
                            fontSize: 26,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF101623),
                            height: 1.25,
                          ),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          'Войдите или зарегистрируйтесь, чтобы получить доступ ко всем возможностям клиники',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.inter(
                            fontSize: 15,
                            color: const Color(0xFF717784),
                            height: 1.6,
                          ),
                        ),
                      ],
                    )
                        .animate()
                        .fadeIn(delay: 120.ms, duration: 550.ms)
                        .moveY(begin: 14, end: 0, duration: 550.ms),

                    const SizedBox(height: 40),

                    // Buttons
                    Column(
                      children: [
                        PrimaryButton(
                          label: 'Войти',
                          onTap: () => context.go('/login'),
                        ),
                        const SizedBox(height: 12),
                        AppOutlineButton(
                          label: 'Зарегистрироваться',
                          onTap: () => context.go('/register'),
                        ),
                      ],
                    )
                        .animate()
                        .fadeIn(delay: 240.ms, duration: 500.ms)
                        .moveY(begin: 18, end: 0, duration: 500.ms),
                  ],
                ),
              ),
            ),

            // Privacy footer
            Padding(
              padding: const EdgeInsets.fromLTRB(24, 8, 24, 20),
              child: RichText(
                textAlign: TextAlign.center,
                text: TextSpan(
                  style: GoogleFonts.inter(
                    fontSize: 11.5,
                    color: AppColors.textHint,
                    height: 1.7,
                  ),
                  children: [
                    const TextSpan(text: 'Продолжая, вы соглашаетесь с '),
                    WidgetSpan(
                      child: GestureDetector(
                        child: Text(
                          'Политикой конфиденциальности',
                          style: GoogleFonts.inter(
                            fontSize: 11.5,
                            color: AppColors.primary,
                            height: 1.7,
                          ),
                        ),
                      ),
                    ),
                    const TextSpan(text: ' и '),
                    WidgetSpan(
                      child: GestureDetector(
                        child: Text(
                          'Пользовательским соглашением',
                          style: GoogleFonts.inter(
                            fontSize: 11.5,
                            color: AppColors.primary,
                            height: 1.7,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ).animate().fadeIn(delay: 450.ms, duration: 400.ms),

          ],
        ),
      ),
    );
  }
}
