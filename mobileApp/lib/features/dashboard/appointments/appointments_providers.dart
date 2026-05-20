import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import 'appointment_model.dart';
import 'appointments_repository.dart';

final appointmentsRepositoryProvider = Provider<AppointmentsRepository>((ref) {
  return AppointmentsRepository(ref.watch(dioProvider));
});

final upcomingAppointmentsProvider =
    FutureProvider<List<AppointmentModel>>((ref) async {
  return ref.watch(appointmentsRepositoryProvider).fetchUpcoming();
});

final pastAppointmentsProvider =
    FutureProvider<List<AppointmentModel>>((ref) async {
  return ref.watch(appointmentsRepositoryProvider).fetchPast();
});

/// Запись с главной: открыть лист деталей на вкладке «Записи», затем сбросить в null.
final appointmentsPendingDetailProvider =
    StateProvider<AppointmentModel?>((ref) => null);
