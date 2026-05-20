import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/constants/app_colors.dart';
import '../../dashboard/dashboard_tab_provider.dart';
import '../state/booking_wizard_notifier.dart';
import '../state/booking_wizard_state.dart';
import 'steps/booking_confirm_step.dart';
import 'steps/booking_date_step.dart';
import 'steps/booking_doctor_step.dart';
import 'steps/booking_service_step.dart';
import 'steps/booking_slot_step.dart';
import 'widgets/booking_progress_bar.dart';

class BookingScreen extends ConsumerWidget {
  final String? doctorSlug;
  final String? serviceSlug;
  final int? appointmentId;

  const BookingScreen({
    super.key,
    this.doctorSlug,
    this.serviceSlug,
    this.appointmentId,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final params = BookingWizardParams(
      doctorSlug: doctorSlug,
      serviceSlug: serviceSlug,
      appointmentId: appointmentId,
    );

    final state = ref.watch(bookingWizardProvider(params));

    // Navigate away once booking is done.
    ref.listen<BookingWizardState>(bookingWizardProvider(params), (prev, next) {
      if (!next.done) return;
      // Go to Appointments tab (index 3).
      ref.read(dashboardTabIndexProvider.notifier).state = 3;
      if (context.mounted) {
        context.go('/dashboard');
      }
    });

    final notifier = ref.read(bookingWizardProvider(params).notifier);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        shadowColor: const Color(0x1A000000),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 18),
          color: AppColors.textPrimary,
          onPressed: () {
            if (state.canGoBack) {
              notifier.back();
            } else {
              context.pop();
            }
          },
        ),
        title: Text(
          params.isReschedule ? 'Перенос записи' : 'Запись на приём',
          style: GoogleFonts.inter(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => context.pop(),
            child: Text(
              'Отмена',
              style: GoogleFonts.inter(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: AppColors.textSecondary,
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          BookingProgressBar(
            steps: state.steps,
            currentIndex: state.stepIndex,
          ),
          Expanded(
            child: AnimatedSwitcher(
              duration: const Duration(milliseconds: 250),
              transitionBuilder: (child, animation) {
                return FadeTransition(
                  opacity: animation,
                  child: SlideTransition(
                    position: Tween<Offset>(
                      begin: const Offset(0.06, 0),
                      end: Offset.zero,
                    ).animate(
                      CurvedAnimation(
                        parent: animation,
                        curve: Curves.easeOut,
                      ),
                    ),
                    child: child,
                  ),
                );
              },
              child: KeyedSubtree(
                key: ValueKey(state.currentStep),
                child: _buildStep(state.currentStep, params),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStep(BookingStep step, BookingWizardParams params) {
    switch (step) {
      case BookingStep.service:
        return BookingServiceStep(params: params);
      case BookingStep.doctor:
        return BookingDoctorStep(params: params);
      case BookingStep.date:
        return BookingDateStep(params: params);
      case BookingStep.slot:
        return BookingSlotStep(params: params);
      case BookingStep.confirm:
        return BookingConfirmStep(params: params);
    }
  }
}
