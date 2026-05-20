import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../../core/constants/app_colors.dart';
import '../../data/booking_providers.dart';
import '../../state/booking_wizard_notifier.dart';

class BookingSlotStep extends ConsumerWidget {
  final BookingWizardParams params;

  const BookingSlotStep({super.key, required this.params});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(bookingWizardProvider(params));
    final serviceSlug = state.service?.slug ?? params.serviceSlug ?? '';
    final doctorSlug = state.doctor?.slug ?? params.doctorSlug ?? '';
    final date = state.date ?? '';

    final slotsAsync = ref.watch(bookingSlotsProvider(bookingSlotsParams(
      serviceSlug: serviceSlug,
      doctorSlug: doctorSlug,
      date: date,
    )));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 24, 20, 4),
          child: Text(
            'Выберите время',
            style: GoogleFonts.inter(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
        ),
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 2, 20, 16),
          child: Text(
            BookingWizardNotifier.formatDate(date),
            style: GoogleFonts.inter(
              fontSize: 13,
              color: AppColors.textSecondary,
            ),
          ),
        ),
        Expanded(
          child: slotsAsync.when(
            loading: () => const Center(
              child: CircularProgressIndicator(
                color: AppColors.primary,
                strokeWidth: 2.5,
              ),
            ),
            error: (e, _) => Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'Не удалось загрузить слоты',
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      color: AppColors.textSecondary,
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextButton(
                    onPressed: () => ref.invalidate(
                      bookingSlotsProvider(bookingSlotsParams(
                        serviceSlug: serviceSlug,
                        doctorSlug: doctorSlug,
                        date: date,
                      )),
                    ),
                    child: Text(
                      'Повторить',
                      style: GoogleFonts.inter(
                        fontWeight: FontWeight.w600,
                        color: AppColors.primary,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            data: (slots) {
              if (slots.isEmpty) {
                return Center(
                  child: Padding(
                    padding: const EdgeInsets.all(32),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 64,
                          height: 64,
                          decoration: BoxDecoration(
                            color: const Color(0xFFF4F7FB),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Icon(
                            Icons.access_time_outlined,
                            size: 28,
                            color: AppColors.primary,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Нет свободного времени',
                          style: GoogleFonts.inter(
                            fontSize: 15,
                            fontWeight: FontWeight.w600,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          'Выберите другой день',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 20),
                        TextButton(
                          onPressed: () => ref
                              .read(bookingWizardProvider(params).notifier)
                              .back(),
                          child: Text(
                            'Назад к выбору даты',
                            style: GoogleFonts.inter(
                              fontWeight: FontWeight.w600,
                              color: AppColors.primary,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }
              return GridView.builder(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
                gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                  maxCrossAxisExtent: 110,
                  mainAxisExtent: 48,
                  mainAxisSpacing: 10,
                  crossAxisSpacing: 10,
                ),
                itemCount: slots.length,
                itemBuilder: (context, index) {
                  final slot = slots[index];
                  return _SlotChip(
                    time: BookingWizardNotifier.formatTime(slot),
                    onTap: () => ref
                        .read(bookingWizardProvider(params).notifier)
                        .selectSlot(slot),
                  );
                },
              );
            },
          ),
        ),
      ],
    );
  }
}

class _SlotChip extends StatelessWidget {
  final String time;
  final VoidCallback onTap;

  const _SlotChip({required this.time, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: const Color(0xFFE8F4FD),
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Center(
          child: Text(
            time,
            style: GoogleFonts.inter(
              fontSize: 15,
              fontWeight: FontWeight.w600,
              color: AppColors.primary,
            ),
          ),
        ),
      ),
    );
  }
}
