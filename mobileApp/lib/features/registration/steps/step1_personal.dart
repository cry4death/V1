import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/widgets/primary_button.dart';
import '../presentation/controllers/registration_controller.dart';
import '_progress_bar.dart';
import '_reg_field.dart';

class Step1Personal extends ConsumerStatefulWidget {
  final VoidCallback onNext;
  final VoidCallback? onBack;

  const Step1Personal({
    super.key,
    required this.onNext,
    this.onBack,
  });

  @override
  ConsumerState<Step1Personal> createState() => _Step1PersonalState();
}

class _Step1PersonalState extends ConsumerState<Step1Personal> {
  bool _touchedLast = false;
  bool _touchedFirst = false;
  bool _touchedDate = false;

  bool _isCyrillic(String v) {
    if (v.trim().length < 2) return false;
    return RegExp(r'^[а-яёА-ЯЁ\s\-]+$').hasMatch(v);
  }

  bool _isValidDate(String v) {
    if (!RegExp(r'^\d{2}\.\d{2}\.\d{4}$').hasMatch(v)) return false;
    final parts = v.split('.');
    final d = int.tryParse(parts[0]) ?? 0;
    final m = int.tryParse(parts[1]) ?? 0;
    final y = int.tryParse(parts[2]) ?? 0;
    if (m < 1 || m > 12 || d < 1 || d > 31 || y < 1900) return false;
    final date = DateTime(y, m, d);
    if (date.isAfter(DateTime.now())) return false;
    return date.day == d && date.month == m;
  }

  String _formatDateInput(String raw) {
    final digits = raw.replaceAll(RegExp(r'\D'), '');
    final limited = digits.length > 8 ? digits.substring(0, 8) : digits;
    if (limited.length <= 2) return limited;
    if (limited.length <= 4) {
      return '${limited.substring(0, 2)}.${limited.substring(2)}';
    }
    return '${limited.substring(0, 2)}.${limited.substring(2, 4)}.${limited.substring(4)}';
  }

  @override
  Widget build(BuildContext context) {
    final data = ref.watch(registrationControllerProvider).data;
    final ctrl = ref.read(registrationControllerProvider.notifier);

    final validLast = _isCyrillic(data.lastName);
    final validFirst = _isCyrillic(data.firstName);
    final validMiddle =
        data.middleName.isEmpty || _isCyrillic(data.middleName);
    final validDate = _isValidDate(data.birthDate);
    final validGender = data.gender.isNotEmpty;
    final canProceed =
        validLast && validFirst && validMiddle && validDate && validGender;

    void handleNext() {
      setState(() {
        _touchedLast = true;
        _touchedFirst = true;
        _touchedDate = true;
      });
      if (canProceed) widget.onNext();
    }

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            RegistrationProgressBar(step: 1, total: 5),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 4, 20, 16),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: () => context.go('/auth'),
                    child: const Icon(
                      Icons.arrow_back,
                      color: Color(0xFF101623),
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Личные данные',
                          style: GoogleFonts.inter(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF101623),
                          ),
                        ),
                        Text(
                          'Заполните информацию о себе',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            color: const Color(0xFF717784),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
                child: Column(
                  children: [
                    RegField(
                      label: 'Фамилия *',
                      value: data.lastName,
                      placeholder: 'Иванова',
                      isValid: _touchedLast ? validLast : null,
                      errorMsg: 'Минимум 2 буквы, только кириллица',
                      onChanged: (v) {
                        setState(() => _touchedLast = true);
                        ctrl.patch(lastName: v);
                      },
                    ),
                    const SizedBox(height: 14),
                    RegField(
                      label: 'Имя *',
                      value: data.firstName,
                      placeholder: 'Мария',
                      isValid: _touchedFirst ? validFirst : null,
                      errorMsg: 'Минимум 2 буквы, только кириллица',
                      onChanged: (v) {
                        setState(() => _touchedFirst = true);
                        ctrl.patch(firstName: v);
                      },
                    ),
                    const SizedBox(height: 14),
                    RegField(
                      label: 'Отчество',
                      hint: 'При наличии',
                      value: data.middleName,
                      placeholder: 'Сергеевна',
                      onChanged: (v) => ctrl.patch(middleName: v),
                    ),
                    const SizedBox(height: 14),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Дата рождения *',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: const Color(0xFF444444),
                          ),
                        ),
                        const SizedBox(height: 4),
                        _DateField(
                          value: data.birthDate,
                          isValid: _touchedDate ? validDate : null,
                          onChanged: (v) {
                            setState(() => _touchedDate = true);
                            ctrl.patch(birthDate: _formatDateInput(v));
                          },
                          onCalendarPick: (v) {
                            setState(() => _touchedDate = true);
                            ctrl.patch(birthDate: v);
                          },
                        ),
                        if (_touchedDate && !validDate)
                          Padding(
                            padding: const EdgeInsets.only(top: 4),
                            child: Text(
                              'Введите корректную дату (не в будущем)',
                              style: GoogleFonts.inter(
                                fontSize: 11.5,
                                color: AppColors.error,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Пол *',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: const Color(0xFF444444),
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF0F4F8),
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: Row(
                            children: [
                              _GenderButton(
                                label: 'Мужской',
                                isSelected: data.gender == 'male',
                                onTap: () => ctrl.patch(gender: 'male'),
                              ),
                              const SizedBox(width: 4),
                              _GenderButton(
                                label: 'Женский',
                                isSelected: data.gender == 'female',
                                onTap: () => ctrl.patch(gender: 'female'),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 12),
              child: PrimaryButton(
                label: 'Далее',
                onTap: handleNext,
                isEnabled: canProceed,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _DateField extends StatefulWidget {
  final String value;
  final bool? isValid;
  final ValueChanged<String> onChanged;
  final ValueChanged<String> onCalendarPick;

  const _DateField({
    required this.value,
    this.isValid,
    required this.onChanged,
    required this.onCalendarPick,
  });

  @override
  State<_DateField> createState() => _DateFieldState();
}

class _DateFieldState extends State<_DateField> {
  late final TextEditingController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = TextEditingController(text: widget.value);
  }

  @override
  void didUpdateWidget(_DateField oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.value != _ctrl.text) {
      _ctrl.text = widget.value;
      _ctrl.selection =
          TextSelection.collapsed(offset: widget.value.length);
    }
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  Color get _borderColor {
    if (widget.isValid == false) return AppColors.error;
    if (widget.isValid == true) return AppColors.primary;
    return AppColors.border;
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: now.subtract(const Duration(days: 365 * 25)),
      firstDate: DateTime(1900),
      lastDate: now,
      locale: const Locale('ru', 'RU'),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppColors.primary,
              onPrimary: Colors.white,
              surface: Colors.white,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      final d = picked.day.toString().padLeft(2, '0');
      final m = picked.month.toString().padLeft(2, '0');
      final y = picked.year.toString();
      widget.onCalendarPick('$d.$m.$y');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 48,
      decoration: BoxDecoration(
        color: AppColors.inputFill,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: _borderColor, width: 1.5),
      ),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: _ctrl,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                hintText: 'ДД.ММ.ГГГГ',
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
                letterSpacing: 1,
              ),
              onChanged: widget.onChanged,
            ),
          ),
          GestureDetector(
            onTap: _pickDate,
            child: Padding(
              padding: const EdgeInsets.only(right: 12),
              child: Icon(
                Icons.calendar_today_outlined,
                size: 20,
                color: widget.isValid == true
                    ? AppColors.primary
                    : AppColors.textHint,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _GenderButton extends StatelessWidget {
  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  const _GenderButton({
    required this.label,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          height: 40,
          decoration: BoxDecoration(
            gradient: isSelected ? AppColors.primaryGradient : null,
            color: isSelected ? null : Colors.transparent,
            borderRadius: BorderRadius.circular(11),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: AppColors.primary.withAlpha(77),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    )
                  ]
                : null,
          ),
          child: Center(
            child: Text(
              label,
              style: GoogleFonts.inter(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: isSelected
                    ? Colors.white
                    : const Color(0xFF5F6368),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
