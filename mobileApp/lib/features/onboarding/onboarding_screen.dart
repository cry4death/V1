import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:smooth_page_indicator/smooth_page_indicator.dart';
import '../../core/constants/app_colors.dart';

class _Slide {
  final String imageUrl;
  final String title;
  final String description;

  const _Slide({
    required this.imageUrl,
    required this.title,
    required this.description,
  });
}

const _slides = [
  _Slide(
    imageUrl:
        'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=600',
    title: 'Врачи с опытом,\nкоторым доверяют',
    description:
        'Более 60 дипломированных специалистов по всем направлениям медицины с опытом работы от 10 лет.',
  ),
  _Slide(
    imageUrl:
        'https://images.unsplash.com/photo-1576091160550-2173dba999ef?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=600',
    title: 'Удобная запись\nбез очередей 24/7',
    description:
        'Онлайн-запись к любому врачу за 2 минуты, напоминания перед приёмом и история всех ваших визитов.',
  ),
  _Slide(
    imageUrl:
        'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=600',
    title: 'Консультация онлайн\nне выходя из дома',
    description:
        'Видеоприём, чат с врачом и электронные рецепты — получите помощь в любое удобное время.',
  ),
];

const _autoAdvanceMs = 10000;

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen>
    with SingleTickerProviderStateMixin {
  final PageController _pageController = PageController();
  int _currentPage = 0;
  late AnimationController _progressController;

  @override
  void initState() {
    super.initState();
    _progressController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: _autoAdvanceMs),
    )..addStatusListener((status) {
        if (status == AnimationStatus.completed) {
          _goNext();
        }
      });
    _progressController.forward();
  }

  void _goNext() {
    if (_currentPage < _slides.length - 1) {
      _pageController.nextPage(
        duration: const Duration(milliseconds: 380),
        curve: Curves.easeInOut,
      );
    } else {
      context.go('/auth');
    }
  }

  void _skip() => context.go('/auth');

  void _onPageChanged(int page) {
    setState(() => _currentPage = page);
    _progressController.reset();
    _progressController.forward();
  }

  @override
  void dispose() {
    _pageController.dispose();
    _progressController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Stack(
          children: [
            Column(
              children: [
                // Image area
                SizedBox(
                  height: 440,
                  child: Stack(
                    children: [
                      PageView.builder(
                        controller: _pageController,
                        onPageChanged: _onPageChanged,
                        itemCount: _slides.length,
                        itemBuilder: (context, index) {
                          return Image.network(
                            _slides[index].imageUrl,
                            fit: BoxFit.cover,
                            alignment: const Alignment(0, -0.5),
                            errorBuilder: (context, error, stackTrace) =>
                                Container(
                              color: const Color(0xFFE8F4FD),
                              child: const Center(
                                child: Icon(
                                  Icons.local_hospital_outlined,
                                  size: 80,
                                  color: AppColors.primary,
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                      // Bottom fade gradient
                      Positioned(
                        bottom: 0,
                        left: 0,
                        right: 0,
                        height: 160,
                        child: Container(
                          decoration: const BoxDecoration(
                            gradient: LinearGradient(
                              begin: Alignment.topCenter,
                              end: Alignment.bottomCenter,
                              colors: [
                                Colors.transparent,
                                Color(0xB3FFFFFF),
                                Colors.white,
                              ],
                              stops: [0.0, 0.55, 1.0],
                            ),
                          ),
                        ),
                      ),
                      // Progress bar
                      Positioned(
                        bottom: 0,
                        left: 0,
                        right: 0,
                        height: 2,
                        child: Container(color: const Color(0x264682B4)),
                      ),
                      Positioned(
                        bottom: 0,
                        left: 0,
                        right: 0,
                        height: 2,
                        child: AnimatedBuilder(
                          animation: _progressController,
                          builder: (context, _) {
                            return FractionallySizedBox(
                              alignment: Alignment.centerLeft,
                              widthFactor: _progressController.value,
                              child: Container(
                                decoration: const BoxDecoration(
                                  gradient: LinearGradient(
                                    colors: [
                                      AppColors.primary,
                                      AppColors.primaryDark,
                                    ],
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                    ],
                  ),
                ),

                // Text content
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(24, 10, 24, 0),
                    child: AnimatedSwitcher(
                      duration: const Duration(milliseconds: 300),
                      switchInCurve: Curves.easeOut,
                      child: _SlideContent(
                        key: ValueKey(_currentPage),
                        slide: _slides[_currentPage],
                      ),
                    ),
                  ),
                ),

                // Navigation row
                Padding(
                  padding: const EdgeInsets.fromLTRB(24, 0, 24, 12),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      SmoothPageIndicator(
                        controller: _pageController,
                        count: _slides.length,
                        effect: const ExpandingDotsEffect(
                          activeDotColor: AppColors.primary,
                          dotColor: Color(0xFF4682B4),
                          dotHeight: 8,
                          dotWidth: 8,
                          expansionFactor: 3.5,
                          spacing: 8,
                        ),
                        onDotClicked: (index) {
                          _pageController.animateToPage(
                            index,
                            duration: const Duration(milliseconds: 300),
                            curve: Curves.easeInOut,
                          );
                        },
                      ),
                      _ArrowButton(onTap: _goNext),
                    ],
                  ),
                ),

              ],
            ),

            // Skip button
            Positioned(
              top: 48,
              right: 20,
              child: GestureDetector(
                onTap: _skip,
                child: Text(
                  'Пропустить',
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    color: AppColors.textHint,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SlideContent extends StatelessWidget {
  final _Slide slide;

  const _SlideContent({super.key, required this.slide});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          slide.title,
          style: GoogleFonts.inter(
            fontSize: 22,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF101623),
            height: 1.33,
          ),
        )
            .animate()
            .fadeIn(duration: 300.ms)
            .moveY(begin: 14, end: 0, duration: 300.ms),
        const SizedBox(height: 8),
        Text(
          slide.description,
          style: GoogleFonts.inter(
            fontSize: 14,
            color: const Color(0xFF5F6368),
            height: 1.6,
          ),
        )
            .animate()
            .fadeIn(delay: 50.ms, duration: 300.ms)
            .moveY(begin: 14, end: 0, duration: 300.ms),
      ],
    );
  }
}

class _ArrowButton extends StatelessWidget {
  final VoidCallback onTap;

  const _ArrowButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 58,
        height: 58,
        decoration: BoxDecoration(
          gradient: AppColors.primaryGradient,
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
              color: AppColors.primary.withAlpha(97),
              blurRadius: 24,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: const Icon(Icons.arrow_forward, color: Colors.white, size: 22),
      ),
    );
  }
}
