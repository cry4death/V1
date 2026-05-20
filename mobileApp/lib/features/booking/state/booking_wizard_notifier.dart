import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../core/network/api_exception.dart';
import '../../dashboard/appointments/appointments_providers.dart';
import '../data/booking_models.dart';
import '../data/booking_providers.dart';
import 'booking_wizard_state.dart';

class BookingWizardParams {
  final String? doctorSlug;
  final String? serviceSlug;
  /// When non-null, the wizard is in reschedule mode:
  /// skips service/doctor steps and calls the reschedule endpoint.
  final int? appointmentId;

  const BookingWizardParams({
    this.doctorSlug,
    this.serviceSlug,
    this.appointmentId,
  });

  bool get isReschedule => appointmentId != null;

  @override
  bool operator ==(Object other) =>
      other is BookingWizardParams &&
      other.doctorSlug == doctorSlug &&
      other.serviceSlug == serviceSlug &&
      other.appointmentId == appointmentId;

  @override
  int get hashCode => Object.hash(doctorSlug, serviceSlug, appointmentId);
}

class BookingWizardNotifier
    extends AutoDisposeFamilyNotifier<BookingWizardState, BookingWizardParams> {
  @override
  BookingWizardState build(BookingWizardParams arg) {
    final steps = _computeSteps(
      hasDoctor: arg.doctorSlug != null,
      hasService: arg.serviceSlug != null,
    );
    final initial = BookingWizardState(steps: steps, stepIndex: 0);

    // When serviceSlug is pre-supplied, fetch the full item so confirm shows it.
    if (arg.serviceSlug != null) {
      Future.microtask(() => _prefetchService(arg.serviceSlug!));
    }
    // When BOTH are pre-supplied (steps=[date,slot,confirm]), also prefetch doctor.
    if (arg.doctorSlug != null && arg.serviceSlug != null) {
      Future.microtask(
        () => _prefetchDoctor(arg.serviceSlug!, arg.doctorSlug!),
      );
    }

    return initial;
  }

  /// Fetch services list and find the item matching [slug], then store it.
  Future<void> _prefetchService(String slug) async {
    try {
      final catalog = ref.read(bookingCatalogRepositoryProvider);
      final items = await catalog.fetchServices(
        doctorSlug: arg.doctorSlug,
      );
      final found = items.cast<BookingServiceItem?>().firstWhere(
            (s) => s?.slug == slug,
            orElse: () => null,
          );
      if (found != null) {
        state = state.copyWith(service: found);
      }
    } catch (_) {
      // Non-fatal — confirm will show "—" and submit will handle gracefully.
    }
  }

  /// Fetch doctors for [serviceSlug] and find the item matching [doctorSlug].
  Future<void> _prefetchDoctor(String serviceSlug, String doctorSlug) async {
    try {
      final catalog = ref.read(bookingCatalogRepositoryProvider);
      final items = await catalog.fetchDoctors(serviceSlug);
      final found = items.cast<BookingDoctorItem?>().firstWhere(
            (d) => d?.slug == doctorSlug,
            orElse: () => null,
          );
      if (found != null) {
        state = state.copyWith(doctor: found);
      }
    } catch (_) {}
  }

  static List<BookingStep> _computeSteps({
    required bool hasDoctor,
    required bool hasService,
  }) {
    final steps = <BookingStep>[];
    if (!hasService) steps.add(BookingStep.service);
    if (!hasDoctor) steps.add(BookingStep.doctor);
    steps.add(BookingStep.date);
    steps.add(BookingStep.slot);
    steps.add(BookingStep.confirm);
    return steps;
  }

  void selectService(BookingServiceItem item) {
    state = state.copyWith(
      service: item,
      clearDoctor: true,
      clearDate: true,
      clearSlot: true,
      clearError: true,
      stepIndex: state.stepIndex + 1,
    );
    // If doctor is pre-selected via URL param, auto-fetch it using chosen service.
    if (arg.doctorSlug != null) {
      Future.microtask(() => _prefetchDoctor(item.slug, arg.doctorSlug!));
    }
  }

  void selectDoctor(BookingDoctorItem item) {
    state = state.copyWith(
      doctor: item,
      clearDate: true,
      clearSlot: true,
      clearError: true,
      stepIndex: state.stepIndex + 1,
    );
  }

  void selectDate(String date) {
    state = state.copyWith(
      date: date,
      clearSlot: true,
      clearError: true,
      stepIndex: state.stepIndex + 1,
    );
  }

  void selectSlot(DateTime slot) {
    state = state.copyWith(
      slot: slot,
      clearError: true,
      stepIndex: state.stepIndex + 1,
    );
  }

  void back() {
    if (!state.canGoBack) return;
    state = state.copyWith(stepIndex: state.stepIndex - 1, clearError: true);
  }

  void setNote(String value) {
    // Note is kept in the confirm step widget locally and passed to submit.
  }

  Future<bool> submit(String? note) async {
    var service = state.service;
    var doctor = state.doctor;
    final slot = state.slot;

    // Resolve pre-selected service from URL param if not yet loaded.
    if (service == null && arg.serviceSlug != null) {
      try {
        final items = await ref
            .read(bookingCatalogRepositoryProvider)
            .fetchServices();
        service = items.cast<BookingServiceItem?>().firstWhere(
              (s) => s?.slug == arg.serviceSlug,
              orElse: () => null,
            );
      } catch (_) {}
    }

    // Resolve pre-selected doctor from URL param if not yet loaded.
    if (doctor == null && arg.doctorSlug != null && service != null) {
      try {
        final items = await ref
            .read(bookingCatalogRepositoryProvider)
            .fetchDoctors(service.slug);
        doctor = items.cast<BookingDoctorItem?>().firstWhere(
              (d) => d?.slug == arg.doctorSlug,
              orElse: () => null,
            );
      } catch (_) {}
    }

    if (service == null || doctor == null || slot == null) {
      state = state.copyWith(error: 'Заполните все шаги перед отправкой');
      return false;
    }

    state = state.copyWith(isSubmitting: true, clearError: true);

    try {
      if (arg.isReschedule) {
        await ref
            .read(appointmentsRepositoryProvider)
            .rescheduleAppointment(arg.appointmentId!, slot);
      } else {
        await ref.read(bookingRepositoryProvider).createAppointment(
              serviceId: service.id,
              doctorId: doctor.id,
              startAt: slot,
              note: note,
            );
      }

      // Invalidate upcoming list so it refreshes on return.
      ref.invalidate(upcomingAppointmentsProvider);
      ref.invalidate(pastAppointmentsProvider);

      state = state.copyWith(isSubmitting: false, done: true);
      return true;
    } catch (e) {
      final msg = _parseError(e);
      state = state.copyWith(isSubmitting: false, error: msg);
      return false;
    }
  }

  static String _parseError(Object e) {
    if (e is DioException) {
      // _ErrorInterceptor wraps every backend error into e.error as ApiException.
      // Only surface the message when a real HTTP response was received (statusCode != null).
      final inner = e.error;
      if (inner is UnauthorizedException) return 'Необходима авторизация';
      if (inner is ApiException && inner.statusCode != null) {
        if (inner.statusCode == 429) {
          return 'Слишком много запросов. Подождите немного.';
        }
        final msg = inner.message;
        if (msg.isNotEmpty) return msg;
      }
      // Fallback: try raw response body (in case interceptor did not run).
      final data = e.response?.data;
      if (data is Map) {
        final msg = data['message'];
        if (msg is String && msg.isNotEmpty) return msg;
      }
    }
    return 'Не удалось создать запись. Попробуйте ещё раз.';
  }

  /// Human-readable date for the confirm step.
  static String formatDate(String isoDate) {
    try {
      final dt = DateTime.parse(isoDate);
      const months = [
        'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
      ];
      final weekdays = [
        'пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс',
      ];
      final wd = weekdays[dt.weekday - 1];
      return '${dt.day} ${months[dt.month - 1]}, $wd';
    } catch (_) {
      return isoDate;
    }
  }

  static String formatTime(DateTime dt) {
    return DateFormat('HH:mm').format(dt);
  }
}

final bookingWizardProvider = NotifierProvider.autoDispose
    .family<BookingWizardNotifier, BookingWizardState, BookingWizardParams>(
  BookingWizardNotifier.new,
);
