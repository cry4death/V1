import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/widgets/clinic_logo.dart';
import '../../auth/presentation/controllers/auth_controller.dart';
import '_progress_bar.dart';
import '_numpad.dart';

class PinSetupScreen extends ConsumerStatefulWidget {
  final VoidCallback onNext;
  /// Показывать ли прогресс-бар регистрации (false — для входа по OTP).
  final bool showProgress;

  const PinSetupScreen({
    super.key,
    required this.onNext,
    this.showProgress = true,
  });

  @override
  ConsumerState<PinSetupScreen> createState() => _PinSetupScreenState();
}

class _PinSetupScreenState extends ConsumerState<PinSetupScreen> {
  static const _pinLength = 4;
  String _pin = '';
  String _confirmPin = '';
  bool _isConfirming = false;
  bool _hasError = false;
  int _shakeKey = 0;

  String get _current => _isConfirming ? _confirmPin : _pin;

  void _addDigit(String d) {
    if (_current.length >= _pinLength) return;
    setState(() {
      _hasError = false;
      if (_isConfirming) {
        _confirmPin += d;
      } else {
        _pin += d;
      }
    });

    if (_current.length == _pinLength) {
      Future.delayed(const Duration(milliseconds: 200), _onComplete);
    }
  }

  void _deleteDigit() {
    setState(() {
      _hasError = false;
      if (_isConfirming) {
        if (_confirmPin.isNotEmpty) {
          _confirmPin = _confirmPin.substring(0, _confirmPin.length - 1);
        }
      } else {
        if (_pin.isNotEmpty) {
          _pin = _pin.substring(0, _pin.length - 1);
        }
      }
    });
  }

  void _onComplete() {
    if (!_isConfirming) {
      setState(() {
        _isConfirming = true;
        _confirmPin = '';
      });
    } else {
      if (_pin == _confirmPin) {
        _savePin();
      } else {
        setState(() {
          _hasError = true;
          _shakeKey++;
          _confirmPin = '';
        });
      }
    }
  }

  Future<void> _savePin() async {
    await ref.read(authControllerProvider.notifier).savePin(_pin);
    if (!mounted) return;
    widget.onNext();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            if (widget.showProgress) RegistrationProgressBar(step: 4, total: 5),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Column(
                  children: [
                    const SizedBox(height: 32),
                    const ClinicLogo(width: 110, color: AppColors.primary)
                        .animate()
                        .fadeIn(duration: 400.ms),
                    const SizedBox(height: 24),
                    Text(
                      _isConfirming
                          ? 'Подтвердите PIN-код'
                          : 'Создайте PIN-код',
                      style: GoogleFonts.inter(
                        fontSize: 20,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF101623),
                      ),
                    ).animate().fadeIn(duration: 300.ms),
                    const SizedBox(height: 6),
                    Text(
                      _isConfirming
                          ? 'Введите PIN-код ещё раз'
                          : 'Используйте его для входа в приложение',
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        color: AppColors.textSecondary,
                      ),
                    ),
                    const SizedBox(height: 36),
                    TweenAnimationBuilder<double>(
                      key: ValueKey(_shakeKey),
                      tween: _hasError
                          ? Tween(begin: -10.0, end: 0.0)
                          : Tween(begin: 0.0, end: 0.0),
                      duration: const Duration(milliseconds: 400),
                      curve: Curves.elasticOut,
                      builder: (context, offset, child) => Transform.translate(
                        offset: Offset(offset, 0),
                        child: child,
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List.generate(_pinLength, (i) {
                          final filled = i < _current.length;
                          final color = _hasError
                              ? AppColors.error
                              : filled
                                  ? AppColors.primary
                                  : Colors.transparent;
                          return _PinDot(isFilled: filled, color: color);
                        }),
                      ),
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      height: 24,
                      child: _hasError
                          ? Text(
                              'PIN-коды не совпадают, попробуйте снова',
                              style: GoogleFonts.inter(
                                fontSize: 13,
                                color: AppColors.error,
                              ),
                            )
                          : null,
                    ),
                    const SizedBox(height: 16),
                  ],
                ),
              ),
            ),

            Numpad(
              onDigit: _addDigit,
              onDelete: _deleteDigit,
              isDisabled: false,
            ),

            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }
}

class _PinDot extends StatefulWidget {
  final bool isFilled;
  final Color color;

  const _PinDot({required this.isFilled, required this.color});

  @override
  State<_PinDot> createState() => _PinDotState();
}

class _PinDotState extends State<_PinDot> with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _scale;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 260),
      value: widget.isFilled ? 1.0 : 0.0,
    );
    _scale = CurvedAnimation(parent: _ctrl, curve: Curves.easeOutBack);
  }

  @override
  void didUpdateWidget(_PinDot old) {
    super.didUpdateWidget(old);
    if (widget.isFilled != old.isFilled) {
      if (widget.isFilled) {
        _ctrl.forward(from: 0.0);
      } else {
        _ctrl.animateTo(
          0.0,
          duration: const Duration(milliseconds: 180),
          curve: Curves.easeIn,
        );
      }
    }
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _scale,
      builder: (_, _) => Container(
        width: 38,
        height: 38,
        margin: const EdgeInsets.symmetric(horizontal: 5),
        alignment: Alignment.center,
        child: Stack(
          alignment: Alignment.center,
          children: [
            Container(
              width: 16,
              height: 16,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: widget.isFilled
                      ? widget.color
                      : const Color(0xFFC8CDD4),
                  width: 2,
                ),
              ),
            ),
            Transform.scale(
              scale: _scale.value,
              child: Container(
                width: 16,
                height: 16,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: widget.color,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
