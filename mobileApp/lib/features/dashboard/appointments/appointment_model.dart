// ─── Enums ───────────────────────────────────────────────────────────────────

enum AppointmentApiStatus { newApt, processing, completed, cancelled, rescheduled }

AppointmentApiStatus _parseStatus(String? value) {
  switch (value) {
    case 'new':
      return AppointmentApiStatus.newApt;
    case 'processing':
      return AppointmentApiStatus.processing;
    case 'completed':
      return AppointmentApiStatus.completed;
    case 'cancelled':
      return AppointmentApiStatus.cancelled;
    case 'rescheduled':
      return AppointmentApiStatus.rescheduled;
    default:
      return AppointmentApiStatus.newApt;
  }
}

// ─── Nested Models ────────────────────────────────────────────────────────────

class AppointmentDoctorInfo {
  final int id;
  final String slug;
  final String lastName;
  final String firstName;
  final String middleName;
  final String specialty;
  final String? photoUrl;

  const AppointmentDoctorInfo({
    required this.id,
    required this.slug,
    required this.lastName,
    required this.firstName,
    required this.middleName,
    required this.specialty,
    this.photoUrl,
  });

  String get fullName {
    final parts = [lastName, firstName, middleName]
        .map((s) => s.trim())
        .where((s) => s.isNotEmpty)
        .toList();
    return parts.join(' ');
  }

  factory AppointmentDoctorInfo.fromJson(Map<String, dynamic> json) {
    return AppointmentDoctorInfo(
      id: (json['id'] as num?)?.toInt() ?? 0,
      slug: (json['slug'] as String?) ?? '',
      lastName: (json['last_name'] as String?) ?? '',
      firstName: (json['first_name'] as String?) ?? '',
      middleName: (json['middle_name'] as String?) ?? '',
      specialty: (json['specialty'] as String?) ?? '',
      photoUrl: json['photo_url'] as String?,
    );
  }
}

class AppointmentServiceInfo {
  final int id;
  final String slug;
  final String name;
  final String priceLabel;

  const AppointmentServiceInfo({
    required this.id,
    required this.slug,
    required this.name,
    required this.priceLabel,
  });

  factory AppointmentServiceInfo.fromJson(Map<String, dynamic> json) {
    return AppointmentServiceInfo(
      id: (json['id'] as num?)?.toInt() ?? 0,
      slug: (json['slug'] as String?) ?? '',
      name: (json['name'] as String?) ?? '',
      priceLabel: (json['price_label'] as String?) ?? '',
    );
  }
}

// ─── Main Model ───────────────────────────────────────────────────────────────

class AppointmentModel {
  final int id;
  final AppointmentApiStatus status;
  final DateTime? startAt;
  final DateTime? endAt;
  final String? note;
  final String? cancellationReason;
  final DateTime? cancelledAt;
  final AppointmentDoctorInfo? doctor;
  final AppointmentServiceInfo? service;

  const AppointmentModel({
    required this.id,
    required this.status,
    this.startAt,
    this.endAt,
    this.note,
    this.cancellationReason,
    this.cancelledAt,
    this.doctor,
    this.service,
  });

  bool get isUpcoming =>
      status == AppointmentApiStatus.newApt ||
      status == AppointmentApiStatus.processing;

  bool get isCompleted => status == AppointmentApiStatus.completed;

  bool get isCancelled =>
      status == AppointmentApiStatus.cancelled ||
      status == AppointmentApiStatus.rescheduled;

  factory AppointmentModel.fromJson(Map<String, dynamic> json) {
    final doctorRaw = json['doctor'];
    final serviceRaw = json['service'];

    return AppointmentModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      status: _parseStatus(json['status'] as String?),
      startAt: json['start_at'] != null
          ? DateTime.tryParse(json['start_at'] as String)
          : null,
      endAt: json['end_at'] != null
          ? DateTime.tryParse(json['end_at'] as String)
          : null,
      note: json['note'] as String?,
      cancellationReason: json['cancellation_reason'] as String?,
      cancelledAt: json['cancelled_at'] != null
          ? DateTime.tryParse(json['cancelled_at'] as String)
          : null,
      doctor: doctorRaw is Map<String, dynamic>
          ? AppointmentDoctorInfo.fromJson(doctorRaw)
          : null,
      service: serviceRaw is Map<String, dynamic>
          ? AppointmentServiceInfo.fromJson(serviceRaw)
          : null,
    );
  }
}
