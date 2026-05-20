import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../constants/app_colors.dart';

class PrimaryButton extends StatelessWidget {
  final String label;
  final VoidCallback? onTap;
  final double height;
  final double borderRadius;
  final bool isEnabled;
  final Widget? icon;

  const PrimaryButton({
    super.key,
    required this.label,
    this.onTap,
    this.height = 54,
    this.borderRadius = 30,
    this.isEnabled = true,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: isEnabled ? onTap : null,
      child: AnimatedContainer(
        width: double.infinity,
        duration: const Duration(milliseconds: 200),
        height: height,
        decoration: BoxDecoration(
          gradient: isEnabled ? AppColors.primaryGradient : null,
          color: isEnabled ? null : AppColors.border,
          borderRadius: BorderRadius.circular(borderRadius),
          boxShadow: isEnabled
              ? [
                  BoxShadow(
                    color: AppColors.primary.withAlpha(80),
                    blurRadius: 20,
                    offset: const Offset(0, 6),
                  ),
                ]
              : null,
        ),
        child: Center(
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (icon != null) ...[icon!, const SizedBox(width: 8)],
              Text(
                label,
                style: GoogleFonts.inter(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: isEnabled ? Colors.white : AppColors.textHint,
                  letterSpacing: 0.1,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
