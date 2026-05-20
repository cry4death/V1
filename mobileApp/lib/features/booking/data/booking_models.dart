class BookingServiceItem {
  final int id;
  final String slug;
  final String name;
  final String priceLabel;
  final String description;

  const BookingServiceItem({
    required this.id,
    required this.slug,
    required this.name,
    required this.priceLabel,
    required this.description,
  });

  factory BookingServiceItem.fromJson(Map<String, dynamic> json) {
    return BookingServiceItem(
      id: (json['id'] as num).toInt(),
      slug: (json['slug'] as String?) ?? '',
      name: (json['name'] as String?) ?? '',
      priceLabel: (json['price_label'] as String?) ?? '—',
      description: (json['description'] as String?) ?? '',
    );
  }
}

class BookingDoctorItem {
  final int id;
  final String slug;
  final String lastName;
  final String firstName;
  final String middleName;
  final String specialty;
  final String? photoUrl;
  final double rating;

  const BookingDoctorItem({
    required this.id,
    required this.slug,
    required this.lastName,
    required this.firstName,
    required this.middleName,
    required this.specialty,
    this.photoUrl,
    required this.rating,
  });

  String get fullName {
    final parts = [lastName, firstName, middleName]
        .map((s) => s.trim())
        .where((s) => s.isNotEmpty)
        .toList();
    return parts.join(' ');
  }

  factory BookingDoctorItem.fromJson(Map<String, dynamic> json) {
    return BookingDoctorItem(
      id: (json['id'] as num).toInt(),
      slug: (json['slug'] as String?) ?? '',
      lastName: (json['last_name'] as String?) ?? '',
      firstName: (json['first_name'] as String?) ?? '',
      middleName: (json['middle_name'] as String?) ?? '',
      specialty: (json['specialty'] as String?) ?? '',
      photoUrl: json['photo_url'] as String?,
      rating: ((json['rating'] as num?)?.toDouble()) ?? 0.0,
    );
  }
}
