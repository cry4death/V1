import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';

class Numpad extends StatelessWidget {
  final ValueChanged<String> onDigit;
  final VoidCallback onDelete;
  final bool isDisabled;
  final Widget? extraLeftWidget;

  const Numpad({
    super.key,
    required this.onDigit,
    required this.onDelete,
    this.isDisabled = false,
    this.extraLeftWidget,
  });

  @override
  Widget build(BuildContext context) {
    const rows = [
      ['1', '2', '3'],
      ['4', '5', '6'],
      ['7', '8', '9'],
    ];

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Column(
        children: [
          ...rows.map((row) => Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: row
                    .map((n) => _NumKey(
                          label: n,
                          onPress: () => onDigit(n),
                          disabled: isDisabled,
                        ))
                    .toList(),
              )),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (extraLeftWidget != null)
                SizedBox(width: 74, height: 74, child: extraLeftWidget)
              else
                const SizedBox(width: 74, height: 74),
              _NumKey(
                label: '0',
                onPress: () => onDigit('0'),
                disabled: isDisabled,
              ),
              _DeleteKey(onPress: onDelete, disabled: isDisabled),
            ],
          ),
        ],
      ),
    );
  }
}

class _NumKey extends StatelessWidget {
  final String label;
  final VoidCallback onPress;
  final bool disabled;

  const _NumKey({
    required this.label,
    required this.onPress,
    this.disabled = false,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: disabled ? null : onPress,
      child: Container(
        width: 74,
        height: 74,
        alignment: Alignment.center,
        color: Colors.transparent,
        child: Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 28,
            fontWeight: FontWeight.w300,
            color: disabled
                ? AppColors.textPrimary.withAlpha(89)
                : AppColors.textPrimary,
          ),
        ),
      ),
    );
  }
}

class _DeleteKey extends StatelessWidget {
  final VoidCallback onPress;
  final bool disabled;

  const _DeleteKey({required this.onPress, this.disabled = false});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: disabled ? null : onPress,
      child: Container(
        width: 74,
        height: 74,
        alignment: Alignment.center,
        color: Colors.transparent,
        child: Icon(
          Icons.backspace_outlined,
          size: 26,
          color: disabled
              ? AppColors.textPrimary.withAlpha(77)
              : AppColors.textPrimary,
        ),
      ),
    );
  }
}
