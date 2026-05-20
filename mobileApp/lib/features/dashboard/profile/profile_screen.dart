import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';
import '../../auth/presentation/controllers/auth_controller.dart';
import '../dashboard_tab_provider.dart';
import 'edit_profile_screen.dart';

// ─── Menu Data ────────────────────────────────────────────────────────────────

class _MenuItem {
  final String id;
  final IconData icon;
  final String label;
  final String? subtitle;
  const _MenuItem({
    required this.id,
    required this.icon,
    required this.label,
    this.subtitle,
  });
}

class _MenuSection {
  final String title;
  final List<_MenuItem> items;
  const _MenuSection({required this.title, required this.items});
}

const _sections = [
  _MenuSection(title: 'Личные данные', items: [
    _MenuItem(
      id: 'edit-profile',
      icon: Icons.edit_outlined,
      label: 'Редактировать профиль',
      subtitle: 'ФИО, дата рождения, пол',
    ),
  ]),
  _MenuSection(title: 'Медицинская информация', items: [
    _MenuItem(
      id: 'appointments',
      icon: Icons.calendar_month_outlined,
      label: 'История записей',
      subtitle: 'Все посещения врачей',
    ),
  ]),
  _MenuSection(title: 'Настройки', items: [
    _MenuItem(
      id: 'notifications',
      icon: Icons.notifications_none_outlined,
      label: 'Уведомления',
      subtitle: 'Управление уведомлениями',
    ),
    _MenuItem(
      id: 'security',
      icon: Icons.shield_outlined,
      label: 'Безопасность',
      subtitle: 'Смена номера, выход со всех устройств',
    ),
  ]),
  _MenuSection(title: 'Поддержка', items: [
    _MenuItem(
      id: 'help',
      icon: Icons.help_outline,
      label: 'Помощь',
      subtitle: 'FAQ и поддержка',
    ),
    _MenuItem(
      id: 'about',
      icon: Icons.info_outline,
      label: 'О приложении',
      subtitle: 'Версия 1.0.0',
    ),
  ]),
];

// ─── Screen ───────────────────────────────────────────────────────────────────

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(authControllerProvider.notifier).bootstrap();
    });
  }

  static String _formatBirthDate(String raw) {
    if (raw.isEmpty) return '';
    final parts = raw.split('-');
    if (parts.length == 3) return '${parts[2]}.${parts[1]}.${parts[0]}';
    return raw;
  }

  /// Инициалы из имени и фамилии (до 2 букв).
  static String _initials(String firstName, String lastName) {
    final f = firstName.isNotEmpty ? firstName[0].toUpperCase() : '';
    final l = lastName.isNotEmpty ? lastName[0].toUpperCase() : '';
    return '$f$l';
  }

  Future<void> _logout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Выйти из аккаунта?',
            style: GoogleFonts.inter(fontWeight: FontWeight.w700, fontSize: 17)),
        content: Text(
          'Вы уверены, что хотите выйти?',
          style: GoogleFonts.inter(fontSize: 14, color: const Color(0xFF717784)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Отмена', style: GoogleFonts.inter(color: AppColors.primary)),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text('Выйти',
                style: GoogleFonts.inter(
                    color: const Color(0xFFE05252), fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
    if (confirm == true && mounted) {
      await ref.read(authControllerProvider.notifier).logout();
      if (mounted) context.go('/auth');
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final firstName  = auth.firstName.isEmpty ? 'Пользователь' : auth.firstName;
    final lastName   = auth.lastName;
    final middleName = auth.middleName;
    final phone      = auth.phone;
    final birthDate  = _formatBirthDate(auth.birthDate);
    final genderLabel = auth.gender == 'male'
        ? 'Мужской'
        : auth.gender == 'female'
            ? 'Женский'
            : '';
    final initials   = _initials(firstName, lastName);
    final bottomPad  = MediaQuery.of(context).padding.bottom + 80;

    return Scaffold(
      backgroundColor: const Color(0xFFF7F9FC),
      body: SafeArea(
        bottom: false,
        child: SingleChildScrollView(
          physics: const ClampingScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [

              // ── Header ────────────────────────────────────────────
              Stack(
                children: [
                  Positioned.fill(
                    child: Column(
                      children: [
                        Flexible(
                          flex: 56,
                          child: Container(
                            decoration: const BoxDecoration(
                              gradient: LinearGradient(
                                colors: [Color(0xFF4682B4), Color(0xFF1E5A99)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                            ),
                          ),
                        ),
                        Flexible(
                          flex: 44,
                          child: Container(color: const Color(0xFFF7F9FC)),
                        ),
                      ],
                    ),
                  ),

                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Padding(
                          padding: const EdgeInsets.only(left: 4, bottom: 16),
                          child: Text(
                            'Профиль',
                            style: GoogleFonts.inter(
                              fontSize: 22,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                            ),
                          ),
                        ),

                        // ── Profile card ──────────────────────────
                        Container(
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xFFF0F4F8)),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withAlpha(26),
                                blurRadius: 20,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          padding: const EdgeInsets.all(20),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [
                              // Аватар с инициалами
                              Container(
                                width: 72,
                                height: 72,
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [Color(0xFF4682B4), Color(0xFF1E5A99)],
                                    begin: Alignment.topLeft,
                                    end: Alignment.bottomRight,
                                  ),
                                  borderRadius: BorderRadius.circular(20),
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xFF4682B4).withAlpha(60),
                                      blurRadius: 12,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                alignment: Alignment.center,
                                child: Text(
                                  initials,
                                  style: GoogleFonts.inter(
                                    fontSize: 26,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.white,
                                    height: 1,
                                  ),
                                ),
                              ),

                              const SizedBox(width: 16),

                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    // Имя + Отчество
                                    Text(
                                      [firstName, if (middleName.isNotEmpty) middleName].join(' '),
                                      style: GoogleFonts.inter(
                                        fontSize: 17,
                                        fontWeight: FontWeight.w700,
                                        color: const Color(0xFF222222),
                                        height: 1.2,
                                      ),
                                    ),
                                    // Фамилия
                                    if (lastName.isNotEmpty) ...[
                                      const SizedBox(height: 2),
                                      Text(
                                        lastName,
                                        style: GoogleFonts.inter(
                                          fontSize: 15,
                                          fontWeight: FontWeight.w700,
                                          color: const Color(0xFF222222),
                                        ),
                                      ),
                                    ],
                                    const SizedBox(height: 10),
                                    if (phone.isNotEmpty)
                                      _InfoChip(icon: Icons.phone_outlined, text: '+$phone'),
                                    if (birthDate.isNotEmpty) ...[
                                      const SizedBox(height: 4),
                                      _InfoChip(icon: Icons.cake_outlined, text: birthDate),
                                    ],
                                    if (genderLabel.isNotEmpty) ...[
                                      const SizedBox(height: 4),
                                      _InfoChip(icon: Icons.person_outline, text: genderLabel),
                                    ],
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ).animate().fadeIn(duration: 350.ms).slideY(begin: 0.1, curve: Curves.easeOut),
                      ],
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 16),

              // ── Menu sections ──────────────────────────────────────
              ..._sections.asMap().entries.map((e) => Padding(
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                    child: _SectionCard(section: e.value, delay: (e.key * 60).ms),
                  )),

              // ── Logout ─────────────────────────────────────────────
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
                child: GestureDetector(
                  onTap: _logout,
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFFDE8E8)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.logout_outlined, size: 18, color: Color(0xFFE05252)),
                        const SizedBox(width: 10),
                        Text(
                          'Выйти из аккаунта',
                          style: GoogleFonts.inter(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFFE05252),
                          ),
                        ),
                      ],
                    ),
                  ),
                ).animate().fadeIn(delay: 280.ms, duration: 300.ms),
              ),

              // ── Version ────────────────────────────────────────────
              Padding(
                padding: EdgeInsets.fromLTRB(0, 12, 0, bottomPad),
                child: Center(
                  child: Text(
                    'Маяк здоровья v1.0.0',
                    style: GoogleFonts.inter(
                        fontSize: 11, color: const Color(0xFFC8D8E8)),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Info chip ────────────────────────────────────────────────────────────────

class _InfoChip extends StatelessWidget {
  final IconData icon;
  final String text;
  const _InfoChip({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 12, color: const Color(0xFF717784)),
        const SizedBox(width: 5),
        Expanded(
          child: Text(
            text,
            style: GoogleFonts.inter(fontSize: 12, color: const Color(0xFF717784)),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}

// ─── Section card ─────────────────────────────────────────────────────────────

class _SectionCard extends ConsumerWidget {
  final _MenuSection section;
  final Duration delay;
  const _SectionCard({required this.section, required this.delay});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 10),
          child: Text(
            section.title.toUpperCase(),
            style: GoogleFonts.inter(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: const Color(0xFFA0AABF),
              letterSpacing: 0.5,
            ),
          ),
        ),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFF0F4F8)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withAlpha(10),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            children: section.items.asMap().entries.map((e) {
              final item  = e.value;
              final isLast = e.key == section.items.length - 1;
              final onTap  = _resolveOnTap(item.id, context, ref);
              return _MenuTile(item: item, isLast: isLast, onTap: onTap);
            }).toList(),
          ),
        ),
      ],
    ).animate().fadeIn(delay: delay, duration: 280.ms).slideY(begin: 0.08, curve: Curves.easeOut);
  }

  static VoidCallback? _resolveOnTap(String id, BuildContext context, WidgetRef ref) {
    switch (id) {
      case 'edit-profile':
        return () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const EditProfileScreen()),
            );
      case 'appointments':
        return () => ref.read(dashboardTabIndexProvider.notifier).state = 3;
      default:
        return null;
    }
  }
}

// ─── Menu tile ────────────────────────────────────────────────────────────────

class _MenuTile extends StatefulWidget {
  final _MenuItem item;
  final bool isLast;
  final VoidCallback? onTap;
  const _MenuTile({required this.item, required this.isLast, this.onTap});

  @override
  State<_MenuTile> createState() => _MenuTileState();
}

class _MenuTileState extends State<_MenuTile> {
  bool _pressed = false;

  @override
  Widget build(BuildContext context) {
    final hasAction = widget.onTap != null;

    return GestureDetector(
      onTapDown: hasAction ? (_) => setState(() => _pressed = true) : null,
      onTapUp: hasAction ? (_) => setState(() => _pressed = false) : null,
      onTapCancel: hasAction ? () => setState(() => _pressed = false) : null,
      onTap: widget.onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 100),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: _pressed ? const Color(0xFFF7F9FC) : Colors.white,
          border: widget.isLast
              ? null
              : const Border(bottom: BorderSide(color: Color(0xFFF0F4F8))),
        ),
        child: Row(
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: const Color(0xFFE8F4FD),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(widget.item.icon, size: 18, color: const Color(0xFF4682B4)),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    widget.item.label,
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF222222),
                    ),
                  ),
                  if (widget.item.subtitle != null) ...[
                    const SizedBox(height: 2),
                    Text(
                      widget.item.subtitle!,
                      style: GoogleFonts.inter(fontSize: 12, color: const Color(0xFFA0AABF)),
                    ),
                  ],
                ],
              ),
            ),
            if (hasAction)
              const Icon(Icons.chevron_right, size: 18, color: Color(0xFFC8D8E8)),
          ],
        ),
      ),
    );
  }
}
