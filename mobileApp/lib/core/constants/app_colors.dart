import 'package:flutter/material.dart';

class AppColors {
  AppColors._();

  static const Color primary = Color(0xFF4682B4);
  static const Color primaryDark = Color(0xFF1E5A99);
  static const Color background = Color(0xFFF7F9FC);
  static const Color surface = Color(0xFFFFFFFF);
  static const Color textPrimary = Color(0xFF101623);
  static const Color textSecondary = Color(0xFF717784);
  static const Color textHint = Color(0xFFA1A8B0);
  static const Color border = Color(0xFFE2E6EA);
  static const Color borderLight = Color(0xFFEEF2F7);
  static const Color error = Color(0xFFE53935);
  static const Color success = Color(0xFF22C55E);
  static const Color inputFill = Color(0xFFF8F9FA);
  static const Color cardShadow = Color(0x12000000);

  static const LinearGradient primaryGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [primary, primaryDark],
    stops: [0.0, 1.0],
  );

  static const LinearGradient splashGradient = LinearGradient(
    begin: Alignment(0.0, -1.0),
    end: Alignment(-0.5, 1.0),
    colors: [primary, primaryDark],
    stops: [0.0, 1.0],
  );
}
