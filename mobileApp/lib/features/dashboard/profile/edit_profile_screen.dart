import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/constants/app_colors.dart';
import '../../auth/presentation/controllers/auth_controller.dart';

/// Экран редактирования профиля пациента.
/// Отправляет `PATCH /api/v1/me` через [AuthController.updateProfile].
/// Поле «Телефон» — read-only (смена номера — отдельный визард, вне MVP).
class EditProfileScreen extends ConsumerStatefulWidget {
  const EditProfileScreen({super.key});

  @override
  ConsumerState<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends ConsumerState<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();

  late final TextEditingController _lastNameCtrl;
  late final TextEditingController _firstNameCtrl;
  late final TextEditingController _middleNameCtrl;
  late final TextEditingController _birthDateCtrl;

  String _gender = '';
  bool _saving = false;
  String? _serverError;

  @override
  void initState() {
    super.initState();
    final s = ref.read(authControllerProvider);
    _lastNameCtrl = TextEditingController(text: s.lastName);
    _firstNameCtrl = TextEditingController(text: s.firstName);
    _middleNameCtrl = TextEditingController(text: s.middleName);
    _birthDateCtrl = TextEditingController(text: _toDisplayDate(s.birthDate));
    _gender = s.gender;
  }

  @override
  void dispose() {
    _lastNameCtrl.dispose();
    _firstNameCtrl.dispose();
    _middleNameCtrl.dispose();
    _birthDateCtrl.dispose();
    super.dispose();
  }

  // ── Helpers ────────────────────────────────────────────────────────────────

  /// Преобразует `YYYY-MM-DD` (из API) в `dd.MM.yyyy` для отображения.
  static String _toDisplayDate(String raw) {
    if (raw.isEmpty) return '';
    final parts = raw.split('-');
    if (parts.length == 3) return '${parts[2]}.${parts[1]}.${parts[0]}';
    return raw;
  }

  /// Преобразует `dd.MM.yyyy` (из формы) в `d.m.Y` для API.
  static String _toApiDate(String display) {
    final parts = display.split('.');
    if (parts.length == 3) return '${parts[0]}.${parts[1]}.${parts[2]}';
    return display;
  }

  bool _isValidDate(String v) {
    final parts = v.split('.');
    if (parts.length != 3) return false;
    final d = int.tryParse(parts[0]);
    final m = int.tryParse(parts[1]);
    final y = int.tryParse(parts[2]);
    if (d == null || m == null || y == null) return false;
    if (m < 1 || m > 12 || d < 1 || d > 31) return false;
    final date = DateTime.tryParse(
        '${parts[2].padLeft(4, '0')}-${parts[1].padLeft(2, '0')}-${parts[0].padLeft(2, '0')}');
    if (date == null) return false;
    return !date.isAfter(DateTime.now());
  }

  static final _cyrillicRe = RegExp(r'^[а-яёА-ЯЁ\s\-]+$');

  // ── Actions ────────────────────────────────────────────────────────────────

  Future<void> _save() async {
    setState(() => _serverError = null);
    if (!_formKey.currentState!.validate()) return;
    if (_gender.isEmpty) {
      setState(() => _serverError = 'Выберите пол');
      return;
    }

    setState(() => _saving = true);
    final error = await ref.read(authControllerProvider.notifier).updateProfile(
          lastName: _lastNameCtrl.text.trim(),
          firstName: _firstNameCtrl.text.trim(),
          middleName: _middleNameCtrl.text.trim(),
          birthDate: _toApiDate(_birthDateCtrl.text.trim()),
          gender: _gender,
        );
    if (!mounted) return;
    setState(() => _saving = false);

    if (error != null) {
      setState(() => _serverError = error);
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Профиль сохранён',
            style: GoogleFonts.inter(color: Colors.white)),
        backgroundColor: AppColors.success,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
      ),
    );
    if (mounted) Navigator.of(context).pop();
  }

  // ── Build ──────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        elevation: 0,
        title: Text('Редактирование профиля',
            style: GoogleFonts.inter(
                fontSize: 17,
                fontWeight: FontWeight.w600,
                color: Colors.white)),
        actions: [
          if (_saving)
            const Padding(
              padding: EdgeInsets.only(right: 16),
              child: Center(
                child: SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                      color: Colors.white, strokeWidth: 2),
                ),
              ),
            )
          else
            TextButton(
              onPressed: _save,
              child: Text('Сохранить',
                  style: GoogleFonts.inter(
                      color: Colors.white, fontWeight: FontWeight.w600)),
            ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: EdgeInsets.fromLTRB(
              16, 20, 16, MediaQuery.of(context).padding.bottom + 32),
          children: [
            // ── Сообщение об ошибке сервера ──
            if (_serverError != null) ...[
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFDE8E8),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFFCA5A5)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline,
                        color: Color(0xFFE05252), size: 18),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(_serverError!,
                          style: GoogleFonts.inter(
                              fontSize: 13, color: const Color(0xFFE05252))),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
            ],

            // ── Раздел: Личные данные ──
            _sectionLabel('ЛИЧНЫЕ ДАННЫЕ'),
            const SizedBox(height: 10),
            _card(children: [
              _field(
                label: 'Фамилия *',
                controller: _lastNameCtrl,
                validator: (v) {
                  if (v == null || v.trim().isEmpty) { return 'Введите фамилию'; }
                  if (v.trim().length < 2) { return 'Минимум 2 символа'; }
                  if (!_cyrillicRe.hasMatch(v.trim())) { return 'Только кириллица'; }
                  return null;
                },
              ),
              _divider(),
              _field(
                label: 'Имя *',
                controller: _firstNameCtrl,
                validator: (v) {
                  if (v == null || v.trim().isEmpty) { return 'Введите имя'; }
                  if (v.trim().length < 2) { return 'Минимум 2 символа'; }
                  if (!_cyrillicRe.hasMatch(v.trim())) { return 'Только кириллица'; }
                  return null;
                },
              ),
              _divider(),
              _field(
                label: 'Отчество',
                controller: _middleNameCtrl,
                validator: (v) {
                  if (v != null && v.trim().isNotEmpty) {
                    if (!_cyrillicRe.hasMatch(v.trim())) { return 'Только кириллица'; }
                  }
                  return null;
                },
              ),
            ]),

            const SizedBox(height: 20),

            // ── Раздел: Дата рождения ──
            _sectionLabel('ДАТА РОЖДЕНИЯ'),
            const SizedBox(height: 10),
            _card(children: [
              _field(
                label: 'дд.мм.гггг *',
                controller: _birthDateCtrl,
                keyboardType: TextInputType.number,
                inputFormatters: [
                  FilteringTextInputFormatter.digitsOnly,
                  _DateInputFormatter(),
                ],
                validator: (v) {
                  if (v == null || v.trim().isEmpty) { return 'Введите дату рождения'; }
                  if (!_isValidDate(v.trim())) { return 'Некорректная дата (дд.мм.гггг)'; }
                  return null;
                },
              ),
            ]),

            const SizedBox(height: 20),

            // ── Раздел: Пол ──
            _sectionLabel('ПОЛ *'),
            const SizedBox(height: 10),
            Row(children: [
              Expanded(
                child: _GenderButton(
                  label: 'Мужской',
                  selected: _gender == 'male',
                  onTap: () => setState(() => _gender = 'male'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _GenderButton(
                  label: 'Женский',
                  selected: _gender == 'female',
                  onTap: () => setState(() => _gender = 'female'),
                ),
              ),
            ]),

            const SizedBox(height: 20),

            // ── Раздел: Телефон (read-only) ──
            _sectionLabel('ТЕЛЕФОН'),
            const SizedBox(height: 10),
            _card(children: [
              Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Номер телефона',
                              style: GoogleFonts.inter(
                                  fontSize: 12,
                                  color: AppColors.textSecondary)),
                          const SizedBox(height: 4),
                          Text(
                            auth.phone.isNotEmpty ? '+${auth.phone}' : '—',
                            style: GoogleFonts.inter(
                                fontSize: 15,
                                fontWeight: FontWeight.w500,
                                color: AppColors.textPrimary),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF0F4F8),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text('Не изменяется',
                          style: GoogleFonts.inter(
                              fontSize: 11,
                              color: AppColors.textSecondary)),
                    ),
                  ],
                ),
              ),
            ]),

            const SizedBox(height: 32),

            // ── Кнопка сохранить (внизу) ──
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                onPressed: _saving ? null : _save,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  disabledBackgroundColor: AppColors.primary.withAlpha(120),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14)),
                  elevation: 0,
                ),
                child: _saving
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                            color: Colors.white, strokeWidth: 2))
                    : Text('Сохранить изменения',
                        style: GoogleFonts.inter(
                            fontSize: 15,
                            fontWeight: FontWeight.w600,
                            color: Colors.white)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Вспомогательные виджеты ────────────────────────────────────────────────

  Widget _sectionLabel(String text) => Padding(
        padding: const EdgeInsets.only(left: 4),
        child: Text(
          text,
          style: GoogleFonts.inter(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: const Color(0xFFA0AABF),
            letterSpacing: 0.5,
          ),
        ),
      );

  Widget _card({required List<Widget> children}) => Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFF0F4F8)),
          boxShadow: [
            BoxShadow(
                color: Colors.black.withAlpha(10),
                blurRadius: 8,
                offset: const Offset(0, 2)),
          ],
        ),
        child: Column(children: children),
      );

  Widget _divider() => const Divider(
      height: 1, thickness: 1, color: Color(0xFFF0F4F8), indent: 16);

  Widget _field({
    required String label,
    required TextEditingController controller,
    String? Function(String?)? validator,
    TextInputType? keyboardType,
    List<TextInputFormatter>? inputFormatters,
  }) =>
      TextFormField(
        controller: controller,
        keyboardType: keyboardType,
        inputFormatters: inputFormatters,
        validator: validator,
        textCapitalization: TextCapitalization.words,
        style: GoogleFonts.inter(
            fontSize: 15,
            fontWeight: FontWeight.w500,
            color: AppColors.textPrimary),
        decoration: InputDecoration(
          labelText: label,
          labelStyle:
              GoogleFonts.inter(fontSize: 13, color: AppColors.textSecondary),
          border: InputBorder.none,
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          errorStyle: GoogleFonts.inter(fontSize: 11, color: AppColors.error),
        ),
      );
}

// ── Кнопка выбора пола ────────────────────────────────────────────────────────

class _GenderButton extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _GenderButton({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: selected ? AppColors.primary : Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: selected ? AppColors.primary : const Color(0xFFE2E6EA),
          ),
          boxShadow: selected
              ? [
                  BoxShadow(
                      color: AppColors.primary.withAlpha(60),
                      blurRadius: 8,
                      offset: const Offset(0, 2))
                ]
              : [],
        ),
        child: Center(
          child: Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: selected ? Colors.white : AppColors.textPrimary,
            ),
          ),
        ),
      ),
    );
  }
}

// ── Форматтер даты дд.мм.гггг ─────────────────────────────────────────────────

class _DateInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
      TextEditingValue oldValue, TextEditingValue newValue) {
    final digits = newValue.text.replaceAll('.', '');
    if (digits.length > 8) return oldValue;

    final buffer = StringBuffer();
    for (var i = 0; i < digits.length; i++) {
      if (i == 2 || i == 4) buffer.write('.');
      buffer.write(digits[i]);
    }
    final formatted = buffer.toString();
    return TextEditingValue(
      text: formatted,
      selection: TextSelection.collapsed(offset: formatted.length),
    );
  }
}
