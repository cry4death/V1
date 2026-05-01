import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';

class RegField extends StatelessWidget {
  final String label;
  final String? hint;
  final String value;
  final String? placeholder;
  final bool? isValid;
  final String? errorMsg;
  final ValueChanged<String> onChanged;

  const RegField({
    super.key,
    required this.label,
    this.hint,
    required this.value,
    this.placeholder,
    this.isValid,
    this.errorMsg,
    required this.onChanged,
  });

  Color get _borderColor {
    if (isValid == false) return AppColors.error;
    if (isValid == true) return AppColors.primary;
    return AppColors.border;
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 13,
            fontWeight: FontWeight.w500,
            color: const Color(0xFF444444),
          ),
        ),
        const SizedBox(height: 4),
        AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          height: 48,
          decoration: BoxDecoration(
            color: AppColors.inputFill,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: _borderColor, width: 1.5),
          ),
          child: TextField(
            controller: TextEditingController(text: value)
              ..selection = TextSelection.collapsed(offset: value.length),
            decoration: InputDecoration(
              hintText: placeholder,
              hintStyle: GoogleFonts.inter(
                fontSize: 15,
                color: AppColors.textHint,
              ),
              border: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(horizontal: 14),
            ),
            style: GoogleFonts.inter(
              fontSize: 15,
              color: AppColors.textPrimary,
            ),
            onChanged: onChanged,
          ),
        ),
        if (hint != null && isValid != false)
          Padding(
            padding: const EdgeInsets.only(top: 4),
            child: Text(
              hint!,
              style: GoogleFonts.inter(
                fontSize: 11.5,
                color: AppColors.textHint,
              ),
            ),
          ),
        if (isValid == false && errorMsg != null)
          Padding(
            padding: const EdgeInsets.only(top: 4),
            child: Text(
              errorMsg!,
              style: GoogleFonts.inter(
                fontSize: 11.5,
                color: AppColors.error,
              ),
            ),
          ),
      ],
    );
  }
}
