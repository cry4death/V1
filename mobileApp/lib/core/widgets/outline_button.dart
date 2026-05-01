import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../constants/app_colors.dart';

class AppOutlineButton extends StatelessWidget {
  final String label;
  final VoidCallback? onTap;
  final double height;
  final double borderRadius;
  final Color borderColor;
  final Color textColor;

  const AppOutlineButton({
    super.key,
    required this.label,
    this.onTap,
    this.height = 54,
    this.borderRadius = 30,
    this.borderColor = AppColors.primary,
    this.textColor = AppColors.primary,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: height,
        decoration: BoxDecoration(
          color: Colors.transparent,
          borderRadius: BorderRadius.circular(borderRadius),
          border: Border.all(color: borderColor, width: 1.5),
        ),
        child: Center(
          child: Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 16,
              fontWeight: FontWeight.w500,
              color: textColor,
              letterSpacing: 0.1,
            ),
          ),
        ),
      ),
    );
  }
}
