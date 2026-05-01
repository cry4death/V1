import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import 'doctor_models.dart';
import 'doctors_repository.dart';

final doctorsRepositoryProvider = Provider<DoctorsRepository>((ref) {
  return DoctorsRepository(ref.watch(dioProvider));
});

final doctorsListProvider = FutureProvider<List<DoctorModel>>((ref) async {
  return ref.watch(doctorsRepositoryProvider).fetchList();
});

final doctorDetailProvider =
    FutureProvider.family<DoctorModel, String>((ref, slug) async {
  return ref.watch(doctorsRepositoryProvider).fetchDetail(slug);
});

/// Slug врача: с главной «Записаться» сразу открыть карточку врача на вкладке «Врачи».
final doctorsPendingSlugProvider = StateProvider<String?>((ref) => null);
