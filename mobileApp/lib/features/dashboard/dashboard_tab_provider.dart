import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Активная вкладка [DashboardScreen]: 0 — главная, 1 — врачи, 2 — услуги,
/// 3 — записи, 4 — профиль.
final dashboardTabIndexProvider = StateProvider<int>((ref) => 0);
