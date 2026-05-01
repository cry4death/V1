import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/constants/app_colors.dart';
import 'appointments/appointments_screen.dart';
import 'dashboard_tab_provider.dart';
import 'doctors/doctors_screen.dart';
import 'home/home_screen.dart';
import 'profile/profile_screen.dart';
import 'services/services_providers.dart';
import 'services/services_screen.dart';

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen> {
  static const _kExitConfirmWindow = Duration(seconds: 2);
  static const _kBackSnackText =
      'Нажмите «Назад» ещё раз, чтобы выйти из приложения';

  DateTime? _lastBackForExitAt;

  @override
  Widget build(BuildContext context) {
    final currentIndex = ref.watch(dashboardTabIndexProvider);
    ref.listen<int>(dashboardTabIndexProvider, (prev, next) {
      if (prev == next) return;
      if (_lastBackForExitAt != null) {
        setState(() => _lastBackForExitAt = null);
      }
    });

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (bool didPop, Object? result) {
        if (didPop) return;
        if (ref.read(dashboardTabIndexProvider) == 2 &&
            ref.read(servicesSubNavProvider.notifier).popOneStep()) {
          return;
        }
        final now = DateTime.now();
        if (_lastBackForExitAt != null &&
            now.difference(_lastBackForExitAt!) < _kExitConfirmWindow) {
          SystemNavigator.pop(animated: true);
        } else {
          setState(() => _lastBackForExitAt = now);
          ScaffoldMessenger.of(context).hideCurrentSnackBar();
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                _kBackSnackText,
                style: GoogleFonts.inter(fontSize: 14),
              ),
              duration: _kExitConfirmWindow,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.background,
        body: IndexedStack(
          index: currentIndex,
          children: const [
            HomeScreen(),
            DoctorsScreen(),
            ServicesScreen(),
            AppointmentsScreen(),
            ProfileScreen(),
          ],
        ),
        bottomNavigationBar: _BottomNav(
          currentIndex: currentIndex,
          onTap: (i) {
            ref.read(dashboardTabIndexProvider.notifier).state = i;
          },
        ),
      ),
    );
  }
}

class _BottomNav extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTap;

  const _BottomNav({required this.currentIndex, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final items = [
      _NavItem(icon: Icons.home_outlined, activeIcon: Icons.home, label: 'Главная'),
      _NavItem(icon: Icons.people_outline, activeIcon: Icons.people, label: 'Врачи'),
      _NavItem(icon: Icons.medical_services_outlined, activeIcon: Icons.medical_services, label: 'Услуги'),
      _NavItem(icon: Icons.calendar_month_outlined, activeIcon: Icons.calendar_month, label: 'Записи'),
      _NavItem(icon: Icons.person_outline, activeIcon: Icons.person, label: 'Профиль'),
    ];

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: Color(0xFFEEF2F7), width: 1)),
      ),
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 60,
          child: Row(
            children: List.generate(items.length, (i) {
              final item = items[i];
              final isActive = i == currentIndex;
              return Expanded(
                child: GestureDetector(
                  behavior: HitTestBehavior.opaque,
                  onTap: () => onTap(i),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        isActive ? item.activeIcon : item.icon,
                        size: 22,
                        color: isActive
                            ? AppColors.primary
                            : const Color(0xFFA0AABF),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        item.label,
                        style: GoogleFonts.inter(
                          fontSize: 10,
                          fontWeight:
                              isActive ? FontWeight.w600 : FontWeight.w400,
                          color: isActive
                              ? AppColors.primary
                              : const Color(0xFFA0AABF),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }),
          ),
        ),
      ),
    );
  }
}

class _NavItem {
  final IconData icon;
  final IconData activeIcon;
  final String label;
  _NavItem({required this.icon, required this.activeIcon, required this.label});
}

