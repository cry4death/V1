import 'package:flutter/material.dart';

class DoctorWorkItem {
  final String period;
  final String role;
  final String org;
  const DoctorWorkItem(this.period, this.role, this.org);
}

class DoctorEduItem {
  final String year;
  final String title;
  const DoctorEduItem(this.year, this.title);
}

class DoctorReviewVm {
  final String initials;
  final String name;
  final int? age;
  final String date;
  final String text;
  const DoctorReviewVm(
    this.initials,
    this.name,
    this.age,
    this.date,
    this.text,
  );
}

enum DoctorGrade { highest, first, second }

enum DoctorAgeGroup { adults, children, all }

class DoctorModel {
  final int id;
  final String slug;
  final String lastName;
  final String firstName;
  final String patronymic;
  final String specialty;
  final int experience;
  final String experienceSummary;
  final DoctorGrade grade;
  final DoctorAgeGroup ageGroup;
  final String? academicDegree;
  final double rating;
  final String photo;
  final String about;
  final String address;
  final String phone;
  final String schedule;
  final List<String> services;
  final List<DoctorWorkItem> workHistory;
  final List<DoctorEduItem> education;
  final List<DoctorReviewVm> reviews;

  const DoctorModel({
    required this.id,
    required this.slug,
    required this.lastName,
    required this.firstName,
    required this.patronymic,
    required this.specialty,
    required this.experience,
    required this.experienceSummary,
    required this.grade,
    required this.ageGroup,
    this.academicDegree,
    required this.rating,
    required this.photo,
    required this.about,
    required this.address,
    required this.phone,
    required this.schedule,
    required this.services,
    required this.workHistory,
    required this.education,
    required this.reviews,
  });

  String get fullName => '$lastName $firstName $patronymic';

  /// Формат «Иванов И. И.» для узких карточек.
  String get compactName {
    final ln = lastName.trim();
    final fn = firstName.trim();
    final pat = patronymic.trim();
    final parts = <String>[];
    if (ln.isNotEmpty) parts.add(ln);
    if (fn.isNotEmpty) parts.add('${fn[0].toUpperCase()}.');
    if (pat.isNotEmpty) parts.add('${pat[0].toUpperCase()}.');
    return parts.join(' ');
  }

  /// «N лет / года / год».
  static String experienceYearsLabel(int years) {
    if (years <= 0) return '';
    if (years >= 11 && years <= 14) return '$years лет';
    final last = years % 10;
    if (last == 1) return '$years год';
    if (last >= 2 && last <= 4) return '$years года';
    return '$years лет';
  }

  /// Стаж для подписи в списке: summary с API или склонение лет.
  String get experienceListLine {
    final s = experienceSummary.trim();
    if (s.isNotEmpty) return s;
    return experienceYearsLabel(experience);
  }

  /// Вторая строка ФИО: «И.·О.» с неразрывным пробелом между инициалами.
  String get nameInitialsLine {
    final fn = firstName.trim();
    final pat = patronymic.trim();
    if (fn.isEmpty && pat.isEmpty) return '';
    if (fn.isNotEmpty && pat.isNotEmpty) {
      return '${fn[0].toUpperCase()}.\u{00A0}${pat[0].toUpperCase()}.';
    }
    if (fn.isNotEmpty) return '${fn[0].toUpperCase()}.';
    return '${pat[0].toUpperCase()}.';
  }

  /// Узкая карточка на главной: стаж цифрами; длинный summary не режем посередине.
  String get homeCardExperienceText {
    if (experience > 0) return experienceYearsLabel(experience);
    final s = experienceSummary.trim();
    if (s.isEmpty) return '';
    if (s.length <= 36) return s;
    return '${s.substring(0, 33)}…';
  }

  String get gradeName {
    switch (grade) {
      case DoctorGrade.highest:
        return 'Высшая категория';
      case DoctorGrade.first:
        return 'Первая категория';
      case DoctorGrade.second:
        return 'Вторая категория';
    }
  }

  Color get gradeColor {
    switch (grade) {
      case DoctorGrade.highest:
        return const Color(0xFF1E5A99);
      case DoctorGrade.first:
        return const Color(0xFF2E8A55);
      case DoctorGrade.second:
        return const Color(0xFFC87A2A);
    }
  }

  Color get gradeBg {
    switch (grade) {
      case DoctorGrade.highest:
        return const Color(0xFFE8F4FD);
      case DoctorGrade.first:
        return const Color(0xFFE8FDF0);
      case DoctorGrade.second:
        return const Color(0xFFFDF5E8);
    }
  }

  static DoctorGrade parseCategory(String? raw) {
    return switch (raw) {
      'highest' => DoctorGrade.highest,
      'second' => DoctorGrade.second,
      _ => DoctorGrade.first,
    };
  }

  static DoctorAgeGroup parsePatientAge(String? raw) {
    return switch (raw) {
      'children' => DoctorAgeGroup.children,
      'both' => DoctorAgeGroup.all,
      _ => DoctorAgeGroup.adults,
    };
  }

  static String initialsFromName(String name) {
    final t = name.trim();
    if (t.isEmpty) return '?';
    return t.substring(0, 1).toUpperCase();
  }

  static String? _parseOptionalString(dynamic v) {
    if (v == null) return null;
    final s = v.toString().trim();
    return s.isEmpty ? null : s;
  }

  factory DoctorModel.fromApiList(Map<String, dynamic> j) {
    return DoctorModel(
      id: (j['id'] as num).toInt(),
      slug: j['slug'] as String,
      lastName: j['last_name'] as String? ?? '',
      firstName: j['first_name'] as String? ?? '',
      patronymic: j['middle_name'] as String? ?? '',
      specialty: j['specialty'] as String? ?? '',
      experience: (j['experience_years'] as num?)?.toInt() ?? 0,
      experienceSummary: j['experience_summary'] as String? ?? '',
      grade: DoctorModel.parseCategory(j['category'] as String?),
      ageGroup: DoctorModel.parsePatientAge(j['patient_age'] as String?),
      academicDegree: null,
      rating: (j['rating'] as num?)?.toDouble() ?? 0,
      photo: j['photo_url'] as String? ?? '',
      about: '',
      address: '',
      phone: '',
      schedule: '',
      services: const [],
      workHistory: const [],
      education: const [],
      reviews: const [],
    );
  }

  factory DoctorModel.fromApiDetail(Map<String, dynamic> j) {
    final clinic = j['clinic'] as Map<String, dynamic>?;
    final eduRaw = j['education'] as List<dynamic>?;

    return DoctorModel(
      id: (j['id'] as num).toInt(),
      slug: j['slug'] as String,
      lastName: j['last_name'] as String? ?? '',
      firstName: j['first_name'] as String? ?? '',
      patronymic: j['middle_name'] as String? ?? '',
      specialty: j['specialty'] as String? ?? '',
      experience: (j['experience_years'] as num?)?.toInt() ?? 0,
      experienceSummary: j['experience_summary'] as String? ?? '',
      grade: DoctorModel.parseCategory(j['category'] as String?),
      ageGroup: DoctorModel.parsePatientAge(j['patient_age'] as String?),
      academicDegree: _parseOptionalString(j['academic_degree']),
      rating: (j['rating'] as num?)?.toDouble() ?? 0,
      photo: j['photo_url'] as String? ?? '',
      about: j['description'] as String? ?? '',
      address: clinic?['address'] as String? ?? '',
      phone: clinic?['phone'] as String? ?? '',
      schedule: clinic?['schedule'] as String? ?? '',
      services:
          (j['services'] as List<dynamic>?)
              ?.map((e) => e.toString())
              .toList() ??
          const [],
      workHistory: _parseWorkItems(eduRaw),
      education: _parseEduItems(eduRaw),
      reviews: _parseReviews(j['reviews'] as List<dynamic>?),
    );
  }

  static List<DoctorWorkItem> _parseWorkItems(List<dynamic>? list) {
    if (list == null) return [];
    final out = <DoctorWorkItem>[];
    for (final e in list) {
      if (e is! Map<String, dynamic>) continue;
      if ((e['type'] ?? 'experience') != 'experience') continue;
      final period = e['period']?.toString() ?? '';
      final title = e['title']?.toString() ?? '';
      final inst = e['institution']?.toString() ?? '';
      out.add(DoctorWorkItem(period, title, inst));
    }
    return out;
  }

  static List<DoctorEduItem> _parseEduItems(List<dynamic>? list) {
    if (list == null) return [];
    final out = <DoctorEduItem>[];
    for (final e in list) {
      if (e is! Map<String, dynamic>) continue;
      if ((e['type'] ?? '') != 'education') continue;
      final period = e['period']?.toString() ?? '';
      final title = e['title']?.toString() ?? '';
      out.add(DoctorEduItem(period, title));
    }
    return out;
  }

  static List<DoctorReviewVm> _parseReviews(List<dynamic>? list) {
    if (list == null) return [];
    return list.map((raw) {
      final m = raw as Map<String, dynamic>;
      final name = m['author_name'] as String? ?? '';
      final iso = m['published_at'] as String?;
      return DoctorReviewVm(
        initialsFromName(name),
        name,
        null,
        _formatReviewDate(iso),
        m['text'] as String? ?? '',
      );
    }).toList();
  }

  static String _formatReviewDate(String? iso) {
    if (iso == null || iso.isEmpty) return '';
    final d = DateTime.tryParse(iso);
    if (d == null) return iso;
    return '${d.day.toString().padLeft(2, '0')}.'
        '${d.month.toString().padLeft(2, '0')}.'
        '${d.year}';
  }
}
