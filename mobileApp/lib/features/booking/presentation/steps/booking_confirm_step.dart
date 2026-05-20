import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../../core/constants/app_colors.dart';
import '../../state/booking_wizard_notifier.dart';

class BookingConfirmStep extends ConsumerStatefulWidget {
  final BookingWizardParams params;

  const BookingConfirmStep({super.key, required this.params});

  @override
  ConsumerState<BookingConfirmStep> createState() => _BookingConfirmStepState();
}

class _BookingConfirmStepState extends ConsumerState<BookingConfirmStep> {
  final _noteCtrl = TextEditingController();

  @override
  void dispose() {
    _noteCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(bookingWizardProvider(widget.params));
    final notifier = ref.read(bookingWizardProvider(widget.params).notifier);

    final doctorName = state.doctor?.fullName ?? '—';
    final specialty = state.doctor?.specialty ?? '';
    final photoUrl = state.doctor?.photoUrl;
    final serviceName = state.service?.name ?? '—';
    final priceLabel = state.service?.priceLabel ?? '—';
    final dateStr = state.date != null
        ? BookingWizardNotifier.formatDate(state.date!)
        : '—';
    final timeStr = state.slot != null
        ? BookingWizardNotifier.formatTime(state.slot!)
        : '—';

    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Подтверждение записи',
            style: GoogleFonts.inter(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Проверьте детали и нажмите «Записаться»',
            style: GoogleFonts.inter(
              fontSize: 13,
              color: AppColors.textSecondary,
            ),
          ),
          const SizedBox(height: 20),

          // Doctor card
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: const Color(0xFFEEF2F7)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withAlpha(10),
                  blurRadius: 10,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(14),
                  child: photoUrl != null && photoUrl.isNotEmpty
                      ? Image.network(
                          photoUrl,
                          width: 54,
                          height: 54,
                          fit: BoxFit.cover,
                          alignment: Alignment.topCenter,
                          errorBuilder: (_, _, _) => _AvatarBox(),
                        )
                      : _AvatarBox(),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        doctorName,
                        style: GoogleFonts.inter(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                          height: 1.3,
                        ),
                      ),
                      if (specialty.isNotEmpty) ...[
                        const SizedBox(height: 2),
                        Text(
                          specialty,
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            color: AppColors.primary,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          // Details
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: const Color(0xFFEEF2F7)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withAlpha(10),
                  blurRadius: 10,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              children: [
                _Row(
                  icon: Icons.medical_services_outlined,
                  label: 'Услуга',
                  value: serviceName,
                  isFirst: true,
                ),
                _Row(
                  icon: Icons.calendar_today_outlined,
                  label: 'Дата',
                  value: dateStr,
                ),
                _Row(
                  icon: Icons.access_time_outlined,
                  label: 'Время',
                  value: timeStr,
                ),
                _Row(
                  icon: Icons.receipt_long_outlined,
                  label: 'Стоимость',
                  value: priceLabel,
                  isLast: true,
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Note field
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFEEF2F7)),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
            child: TextField(
              controller: _noteCtrl,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Примечание (необязательно)',
                hintStyle: GoogleFonts.inter(
                  fontSize: 13,
                  color: AppColors.textHint,
                ),
                border: InputBorder.none,
              ),
              style: GoogleFonts.inter(
                fontSize: 13,
                color: AppColors.textPrimary,
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Error
          if (state.error != null) ...[
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFFDE8E8),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Row(
                children: [
                  const Icon(Icons.error_outline,
                      size: 18, color: Color(0xFFD94F4F)),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      state.error!,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        color: const Color(0xFFD94F4F),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
          ],

          // Submit
          SizedBox(
            width: double.infinity,
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: state.isSubmitting
                    ? const LinearGradient(
                        colors: [Color(0xFF9BB8D4), Color(0xFF6E99BE)],
                      )
                    : AppColors.primaryGradient,
                borderRadius: BorderRadius.circular(16),
                boxShadow: state.isSubmitting
                    ? null
                    : [
                        BoxShadow(
                          color: AppColors.primary.withAlpha(90),
                          blurRadius: 18,
                          offset: const Offset(0, 6),
                        ),
                      ],
              ),
              child: TextButton(
                style: TextButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16)),
                ),
                onPressed: state.isSubmitting
                    ? null
                    : () => notifier.submit(
                          _noteCtrl.text.trim().isEmpty
                              ? null
                              : _noteCtrl.text.trim(),
                        ),
                child: state.isSubmitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : Text(
                        'Записаться',
                        style: GoogleFonts.inter(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                          color: Colors.white,
                        ),
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _AvatarBox extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: 54,
      height: 54,
      color: const Color(0xFFE8F4FD),
      child: const Icon(Icons.person, color: AppColors.primary, size: 26),
    );
  }
}

class _Row extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final bool isFirst;
  final bool isLast;

  const _Row({
    required this.icon,
    required this.label,
    required this.value,
    this.isFirst = false,
    this.isLast = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        border: isLast
            ? null
            : const Border(
                bottom: BorderSide(color: Color(0xFFEEF2F7)),
              ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(
              color: const Color(0xFFE8F4FD),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, size: 15, color: AppColors.primary),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: AppColors.textPrimary,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
