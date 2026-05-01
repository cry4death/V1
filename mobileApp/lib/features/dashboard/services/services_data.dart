// Модели категорий и услуг; данные с API (см. [ServicesRepository]).

class ServiceCategory {
  final int id;
  final String slug;
  final String label;
  final int count;
  const ServiceCategory({
    required this.id,
    required this.slug,
    required this.label,
    required this.count,
  });

  factory ServiceCategory.fromJson(Map<String, dynamic> json) {
    return ServiceCategory(
      id: (json['id'] as num).toInt(),
      slug: json['slug'] as String? ?? '',
      label: json['name'] as String? ?? '',
      count: (json['services_count'] as num?)?.toInt() ?? 0,
    );
  }
}

class ServiceOffer {
  final int id;
  final String slug;
  final String name;
  final String desc;
  final String price;
  /// Развёрнутое описание на странице услуги (если пусто — берётся [desc])
  final String? longAbout;
  final String? indications;
  final String? preparation;
  const ServiceOffer({
    required this.id,
    required this.slug,
    required this.name,
    required this.desc,
    required this.price,
    this.longAbout,
    this.indications,
    this.preparation,
  });

  factory ServiceOffer.fromJson(Map<String, dynamic> json) {
    final long = json['long_description'];
    return ServiceOffer(
      id: (json['id'] as num).toInt(),
      slug: json['slug'] as String? ?? '',
      name: json['name'] as String? ?? '',
      desc: (json['description'] as String?)?.trim() ?? '',
      price: json['price_label'] as String? ?? 'По запросу',
      longAbout: long is String && long.trim().isNotEmpty ? long : null,
      indications: _nullableString(json['indications']),
      preparation: _nullableString(json['preparation']),
    );
  }
}

String? _nullableString(dynamic v) {
  if (v is! String) return null;
  final t = v.trim();
  return t.isEmpty ? null : t;
}

class ServiceDoctorSnapshot {
  final String lastName;
  final String firstName;
  final String patronymic;
  final String specialty;
  final int experienceYears;
  final String gradeName;
  final double rating;
  final String photoUrl;
  const ServiceDoctorSnapshot({
    required this.lastName,
    required this.firstName,
    required this.patronymic,
    required this.specialty,
    required this.experienceYears,
    required this.gradeName,
    required this.rating,
    required this.photoUrl,
  });
}

/// Врачи для блока на странице услуги (пока не подключено к API).
const serviceDoctorsSnapshots = <ServiceDoctorSnapshot>[
  ServiceDoctorSnapshot(
    lastName: 'Денисевич',
    firstName: 'Юлия',
    patronymic: 'Александровна',
    specialty: 'Акушер-гинеколог',
    experienceYears: 8,
    gradeName: 'Высшая категория',
    rating: 4.9,
    photoUrl:
        'https://images.unsplash.com/photo-1758691463345-bb7f8e7e0d0c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400',
  ),
  ServiceDoctorSnapshot(
    lastName: 'Морозов',
    firstName: 'Дмитрий',
    patronymic: 'Павлович',
    specialty: 'Офтальмолог',
    experienceYears: 6,
    gradeName: 'Вторая категория',
    rating: 4.8,
    photoUrl:
        'https://images.unsplash.com/photo-1659353887804-fc7f9313021a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400',
  ),
  ServiceDoctorSnapshot(
    lastName: 'Волкова',
    firstName: 'Мария',
    patronymic: 'Игоревна',
    specialty: 'Дерматолог',
    experienceYears: 12,
    gradeName: 'Высшая категория',
    rating: 4.9,
    photoUrl:
        'https://images.unsplash.com/photo-1758691463345-bb7f8e7e0d0c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400',
  ),
  ServiceDoctorSnapshot(
    lastName: 'Фёдоров',
    firstName: 'Алексей',
    patronymic: 'Николаевич',
    specialty: 'Стоматолог',
    experienceYears: 9,
    gradeName: 'Первая категория',
    rating: 4.8,
    photoUrl:
        'https://images.unsplash.com/photo-1758691463393-a2aa9900af8a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400',
  ),
];

String categoryWord(int n) {
  if (n % 10 == 1 && n % 100 != 11) return 'категория';
  if (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)) {
    return 'категории';
  }
  return 'категорий';
}

String serviceWord(int n) {
  if (n % 10 == 1 && n % 100 != 11) return 'услуга';
  if (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)) {
    return 'услуги';
  }
  return 'услуг';
}
