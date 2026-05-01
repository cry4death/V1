import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import 'services_data.dart';
import 'services_repository.dart';

/// Вложенная навигация вкладки «Услуги» (категория → список → деталь).
/// Вынесена в провайдер, чтобы [DashboardScreen] обрабатывал «Назад» и двойной
/// выход из приложения, не полагаясь на несколько [PopScope] в [IndexedStack].
class ServicesSubNavState {
  const ServicesSubNavState({
    this.selectedCategory,
    this.selectedService,
  });

  final ServiceCategory? selectedCategory;
  final ({ServiceCategory cat, ServiceOffer offer})? selectedService;

  bool get isAtRoot => selectedCategory == null && selectedService == null;
}

class ServicesSubNavNotifier extends Notifier<ServicesSubNavState> {
  @override
  ServicesSubNavState build() => const ServicesSubNavState();

  void openCategory(ServiceCategory category) {
    state = ServicesSubNavState(
      selectedCategory: category,
      selectedService: null,
    );
  }

  void openService(ServiceOffer offer) {
    final cat = state.selectedCategory;
    if (cat == null) return;
    state = ServicesSubNavState(
      selectedCategory: cat,
      selectedService: (cat: cat, offer: offer),
    );
  }

  void clearAll() {
    state = const ServicesSubNavState();
  }

  /// Список → деталь → список → категории. [true] если шаг обработан.
  bool popOneStep() {
    if (state.selectedService != null) {
      state = ServicesSubNavState(
        selectedCategory: state.selectedCategory,
        selectedService: null,
      );
      return true;
    }
    if (state.selectedCategory != null) {
      state = const ServicesSubNavState();
      return true;
    }
    return false;
  }
}

final servicesSubNavProvider =
    NotifierProvider<ServicesSubNavNotifier, ServicesSubNavState>(
  ServicesSubNavNotifier.new,
);

final servicesRepositoryProvider = Provider<ServicesRepository>((ref) {
  return ServicesRepository(ref.watch(dioProvider));
});

final serviceDirectionsProvider = FutureProvider<List<ServiceCategory>>((ref) {
  return ref.watch(servicesRepositoryProvider).fetchDirections();
});

final servicesInDirectionProvider =
    FutureProvider.family<List<ServiceOffer>, String>((ref, slug) {
  return ref.watch(servicesRepositoryProvider).fetchServicesInDirection(slug);
});

/// Slug направления: при открытии вкладки «Услуги» сразу показать список этой категории (с главного экрана).
final servicesPendingCategorySlugProvider = StateProvider<String?>((ref) => null);
