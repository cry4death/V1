import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import 'booking_catalog_repository.dart';
import 'booking_models.dart';
import 'booking_repository.dart';

final bookingCatalogRepositoryProvider =
    Provider<BookingCatalogRepository>((ref) {
  return BookingCatalogRepository(ref.watch(dioProvider));
});

final bookingRepositoryProvider = Provider<BookingRepository>((ref) {
  return BookingRepository(ref.watch(dioProvider));
});

// ─── Catalog Providers ────────────────────────────────────────────────────────

final bookingServicesProvider = FutureProvider.autoDispose
    .family<List<BookingServiceItem>, String?>((ref, doctorSlug) {
  return ref
      .watch(bookingCatalogRepositoryProvider)
      .fetchServices(doctorSlug: doctorSlug);
});

final bookingDoctorsProvider = FutureProvider.autoDispose
    .family<List<BookingDoctorItem>, String>((ref, serviceSlug) {
  return ref
      .watch(bookingCatalogRepositoryProvider)
      .fetchDoctors(serviceSlug);
});

class _DatesParams {
  final String serviceSlug;
  final String doctorSlug;
  final String? from;
  final String? to;

  const _DatesParams({
    required this.serviceSlug,
    required this.doctorSlug,
    this.from,
    this.to,
  });

  @override
  bool operator ==(Object other) =>
      other is _DatesParams &&
      other.serviceSlug == serviceSlug &&
      other.doctorSlug == doctorSlug &&
      other.from == from &&
      other.to == to;

  @override
  int get hashCode =>
      Object.hash(serviceSlug, doctorSlug, from, to);
}

final bookingDatesProvider = FutureProvider.autoDispose
    .family<List<String>, _DatesParams>((ref, p) {
  return ref.watch(bookingCatalogRepositoryProvider).fetchDates(
        serviceSlug: p.serviceSlug,
        doctorSlug: p.doctorSlug,
        from: p.from,
        to: p.to,
      );
});

DatesParams bookingDatesParams({
  required String serviceSlug,
  required String doctorSlug,
  String? from,
  String? to,
}) =>
    _DatesParams(
      serviceSlug: serviceSlug,
      doctorSlug: doctorSlug,
      from: from,
      to: to,
    );

typedef DatesParams = _DatesParams;

class _SlotsParams {
  final String serviceSlug;
  final String doctorSlug;
  final String date;

  const _SlotsParams({
    required this.serviceSlug,
    required this.doctorSlug,
    required this.date,
  });

  @override
  bool operator ==(Object other) =>
      other is _SlotsParams &&
      other.serviceSlug == serviceSlug &&
      other.doctorSlug == doctorSlug &&
      other.date == date;

  @override
  int get hashCode => Object.hash(serviceSlug, doctorSlug, date);
}

final bookingSlotsProvider = FutureProvider.autoDispose
    .family<List<DateTime>, _SlotsParams>((ref, p) {
  return ref.watch(bookingCatalogRepositoryProvider).fetchSlots(
        serviceSlug: p.serviceSlug,
        doctorSlug: p.doctorSlug,
        date: p.date,
      );
});

typedef SlotsParams = _SlotsParams;

SlotsParams bookingSlotsParams({
  required String serviceSlug,
  required String doctorSlug,
  required String date,
}) =>
    _SlotsParams(
      serviceSlug: serviceSlug,
      doctorSlug: doctorSlug,
      date: date,
    );
