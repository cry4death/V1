import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';
import '../../auth/presentation/controllers/auth_controller.dart';

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
      subtitle: 'ФИО, дата рождения, контакты',
    ),
    _MenuItem(
      id: 'addresses',
      icon: Icons.location_on_outlined,
      label: 'Адреса',
      subtitle: 'Управление адресами доставки',
    ),
  ]),
  _MenuSection(title: 'Медицинская информация', items: [
    _MenuItem(
      id: 'medical-history',
      icon: Icons.description_outlined,
      label: 'Медицинская карта',
      subtitle: 'История болезней и анализы',
    ),
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
      subtitle: 'PIN, Face ID, пароль',
    ),
    _MenuItem(
      id: 'payment',
      icon: Icons.credit_card_outlined,
      label: 'Способы оплаты',
      subtitle: 'Карты и методы оплаты',
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
    // подтянуть актуальные данные если ещё не загружены
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(authControllerProvider.notifier).bootstrap();
    });
  }

  Future<void> _logout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Выйти из аккаунта?',
            style: GoogleFonts.inter(
                fontWeight: FontWeight.w700, fontSize: 17)),
        content: Text(
          'Вы уверены, что хотите выйти?',
          style: GoogleFonts.inter(fontSize: 14, color: const Color(0xFF717784)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Отмена',
                style: GoogleFonts.inter(color: AppColors.primary)),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text('Выйти',
                style: GoogleFonts.inter(
                    color: const Color(0xFFE05252),
                    fontWeight: FontWeight.w600)),
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
    final displayName = auth.firstName.isEmpty ? 'Пользователь' : auth.firstName;
    final displayLast = auth.lastName;
    final phone = auth.phone;
    final bottomPad = MediaQuery.of(context).padding.bottom + 80;

    return Scaffold(
      backgroundColor: const Color(0xFFF7F9FC),
      body: SafeArea(
        bottom: false,
        child: SingleChildScrollView(
          physics: const ClampingScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [

              // ── Header: gradient top half / gray bottom half ──
              Stack(
                children: [
                  // Two-tone background (fills Stack height adaptively)
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
                          child: Container(
                              color: const Color(0xFFF7F9FC)),
                        ),
                      ],
                    ),
                  ),

                  // Content (determines Stack height)
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
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Avatar
                        Stack(
                          clipBehavior: Clip.none,
                          children: [
                            Container(
                              width: 80,
                              height: 80,
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: Colors.white, width: 3),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withAlpha(26),
                                    blurRadius: 12,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                                color: const Color(0xFFE8F4FD),
                              ),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(17),
                                child: Image.network(
                                  'https://images.unsplash.com/photo-1494790108377-be9c29b29330?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400',
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, _, _) => const Icon(
                                      Icons.person,
                                      size: 40,
                                      color: AppColors.primary),
                                ),
                              ),
                            ),
                            Positioned(
                              bottom: -4,
                              right: -4,
                              child: Container(
                                width: 28,
                                height: 28,
                                decoration: BoxDecoration(
                                  color: AppColors.primary,
                                  borderRadius: BorderRadius.circular(9),
                                  border: Border.all(color: Colors.white, width: 2),
                                  boxShadow: [
                                    BoxShadow(
                                      color: AppColors.primary.withAlpha(100),
                                      blurRadius: 8,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: const Icon(Icons.edit_outlined,
                                    size: 13, color: Colors.white),
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(width: 16),

                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '$displayName Ивановна',
                                style: GoogleFonts.inter(
                                  fontSize: 17,
                                  fontWeight: FontWeight.w700,
                                  color: const Color(0xFF222222),
                                  height: 1.2,
                                ),
                              ),
                              if (displayLast.isNotEmpty) ...[
                                const SizedBox(height: 2),
                                Text(
                                  displayLast,
                                  style: GoogleFonts.inter(
                                    fontSize: 15,
                                    fontWeight: FontWeight.w700,
                                    color: const Color(0xFF222222),
                                  ),
                                ),
                              ],
                              const SizedBox(height: 10),
                              if (phone.isNotEmpty)
                                _InfoChip(
                                  icon: Icons.phone_outlined,
                                  text: phone,
                                ),
                              const SizedBox(height: 4),
                              const _InfoChip(
                                icon: Icons.calendar_today_outlined,
                                text: '15.03.1990',
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                        ).animate().fadeIn(duration: 350.ms).slideY(
                            begin: 0.1, curve: Curves.easeOut),
                      ],
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 16),

              // ── Menu sections ──
              ..._sections.asMap().entries.map((e) => Padding(
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                    child: _SectionCard(
                      section: e.value,
                      delay: (e.key * 60).ms,
                    ),
                  )),

              // ── Logout ──
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
                        const Icon(Icons.logout_outlined,
                            size: 18, color: Color(0xFFE05252)),
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

              // ── Version ──
              Padding(
                padding: EdgeInsets.fromLTRB(0, 12, 0, bottomPad),
                child: Center(
                  child: Text(
                    'Маяк здоровья v1.0.0',
                    style: GoogleFonts.inter(
                        fontSize: 11,
                        color: const Color(0xFFC8D8E8)),
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

// ─── Info chip row ─────────────────────────────────────────────────────────────

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
            style: GoogleFonts.inter(
                fontSize: 12, color: const Color(0xFF717784)),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}

// ─── Section card ─────────────────────────────────────────────────────────────

class _SectionCard extends StatelessWidget {
  final _MenuSection section;
  final Duration delay;
  const _SectionCard({required this.section, required this.delay});

  @override
  Widget build(BuildContext context) {
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
              final item = e.value;
              final isLast = e.key == section.items.length - 1;
              return _MenuTile(item: item, isLast: isLast);
            }).toList(),
          ),
        ),
      ],
    ).animate().fadeIn(delay: delay, duration: 280.ms).slideY(
        begin: 0.08, curve: Curves.easeOut);
  }
}

// ─── Menu tile ────────────────────────────────────────────────────────────────

class _MenuTile extends StatefulWidget {
  final _MenuItem item;
  final bool isLast;
  const _MenuTile({required this.item, required this.isLast});

  @override
  State<_MenuTile> createState() => _MenuTileState();
}

class _MenuTileState extends State<_MenuTile> {
  bool _pressed = false;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => setState(() => _pressed = true),
      onTapUp: (_) => setState(() => _pressed = false),
      onTapCancel: () => setState(() => _pressed = false),
      onTap: () {},
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 100),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: _pressed ? const Color(0xFFF7F9FC) : Colors.white,
          border: widget.isLast
              ? null
              : const Border(
                  bottom: BorderSide(color: Color(0xFFF0F4F8))),
          borderRadius: widget.isLast
              ? null
              : null,
        ),
        child: Row(
          children: [
            // Icon box
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: const Color(0xFFE8F4FD),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(widget.item.icon,
                  size: 18, color: const Color(0xFF4682B4)),
            ),
            const SizedBox(width: 14),
            // Text
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
                      style: GoogleFonts.inter(
                          fontSize: 12,
                          color: const Color(0xFFA0AABF)),
                    ),
                  ],
                ],
              ),
            ),
            const Icon(Icons.chevron_right,
                size: 18, color: Color(0xFFC8D8E8)),
          ],
        ),
      ),
    );
  }
}

