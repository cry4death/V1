import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../../core/constants/app_colors.dart';
import '../../state/booking_wizard_state.dart';

class BookingProgressBar extends StatelessWidget {
  final List<BookingStep> steps;
  final int currentIndex;

  const BookingProgressBar({
    super.key,
    required this.steps,
    required this.currentIndex,
  });

  static String _labelFor(BookingStep step) {
    switch (step) {
      case BookingStep.service:
        return 'Услуга';
      case BookingStep.doctor:
        return 'Врач';
      case BookingStep.date:
        return 'Дата';
      case BookingStep.slot:
        return 'Время';
      case BookingStep.confirm:
        return 'Итог';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
      child: Row(
        children: List.generate(steps.length, (index) {
          final step = steps[index];
          final isCompleted = index < currentIndex;
          final isCurrent = index == currentIndex;
          final isLast = index == steps.length - 1;

          return Expanded(
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        height: 4,
                        decoration: BoxDecoration(
                          color: isCompleted || isCurrent
                              ? AppColors.primary
                              : const Color(0xFFE2E6EA),
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                      const SizedBox(height: 5),
                      Text(
                        _labelFor(step),
                        style: GoogleFonts.inter(
                          fontSize: 10,
                          fontWeight: isCurrent
                              ? FontWeight.w700
                              : FontWeight.w400,
                          color: isCurrent
                              ? AppColors.primary
                              : isCompleted
                                  ? AppColors.textSecondary
                                  : AppColors.textHint,
                        ),
                      ),
                    ],
                  ),
                ),
                if (!isLast) const SizedBox(width: 4),
              ],
            ),
          );
        }),
      ),
    );
  }
}
