import '../data/booking_models.dart';

enum BookingStep { service, doctor, date, slot, confirm }

class BookingWizardState {
  final List<BookingStep> steps;
  final int stepIndex;
  final BookingServiceItem? service;
  final BookingDoctorItem? doctor;
  final String? date;
  final DateTime? slot;
  final bool isSubmitting;
  final String? error;
  final bool done;

  const BookingWizardState({
    required this.steps,
    required this.stepIndex,
    this.service,
    this.doctor,
    this.date,
    this.slot,
    this.isSubmitting = false,
    this.error,
    this.done = false,
  });

  BookingStep get currentStep => steps[stepIndex];

  int get totalSteps => steps.length;

  bool get canGoBack => stepIndex > 0;

  BookingWizardState copyWith({
    List<BookingStep>? steps,
    int? stepIndex,
    BookingServiceItem? service,
    bool clearService = false,
    BookingDoctorItem? doctor,
    bool clearDoctor = false,
    String? date,
    bool clearDate = false,
    DateTime? slot,
    bool clearSlot = false,
    bool? isSubmitting,
    String? error,
    bool clearError = false,
    bool? done,
  }) {
    return BookingWizardState(
      steps: steps ?? this.steps,
      stepIndex: stepIndex ?? this.stepIndex,
      service: clearService ? null : (service ?? this.service),
      doctor: clearDoctor ? null : (doctor ?? this.doctor),
      date: clearDate ? null : (date ?? this.date),
      slot: clearSlot ? null : (slot ?? this.slot),
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : (error ?? this.error),
      done: done ?? this.done,
    );
  }
}
