import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';

class RegistrationProgressBar extends StatelessWidget {
  final int step;
  final int total;

  const RegistrationProgressBar({
    super.key,
    required this.step,
    required this.total,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 0),
      child: Row(
        children: List.generate(total, (i) {
          final isActive = i < step;
          return Expanded(
            child: Container(
              height: 3,
              margin: EdgeInsets.only(right: i < total - 1 ? 4 : 0),
              decoration: BoxDecoration(
                color: isActive ? AppColors.primary : const Color(0xFFE2E6EA),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          );
        }),
      ),
    );
  }
}
