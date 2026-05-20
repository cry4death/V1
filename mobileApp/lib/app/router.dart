import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/auth/auth_screen.dart';
import '../features/auth/domain/auth_state.dart';
import '../features/auth/presentation/controllers/auth_controller.dart';
import '../features/dashboard/blog/article_screen.dart';
import '../features/dashboard/blog/blog_screen.dart';
import '../features/dashboard/dashboard_screen.dart';
import '../features/dashboard/promotions/promotion_detail_screen.dart';
import '../features/login/login_face_id.dart';
import '../features/login/login_pin.dart';
import '../features/login/login_screen.dart';
import '../features/onboarding/onboarding_screen.dart';
import '../features/registration/registration_screen.dart';
import '../features/booking/presentation/booking_screen.dart';
import '../features/splash/splash_screen.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  final refresh = _AuthRefreshNotifier(ref);
  ref.onDispose(refresh.dispose);

  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: refresh,
    redirect: (context, state) {
      final auth = ref.read(authControllerProvider);
      final status = auth.status;
      final loc = state.matchedLocation;

      // Пока статус не определён — держим на splash
      if (status == AuthStatus.unknown) {
        return loc == '/splash' ? null : '/splash';
      }

      // Публичные маршруты (аутентификация, онбординг)
      const publicRoutes = ['/splash', '/onboarding', '/auth', '/register',
          '/login', '/login/pin', '/login/faceid'];
      final isPublic = publicRoutes.any((r) => loc.startsWith(r));

      // Не зарегистрирован → онбординг / auth
      if (status == AuthStatus.unregistered) {
        return isPublic ? null : '/auth';
      }

      // Зарегистрирован, но не прошёл PIN/Face ID.
      // /login разрешён: пользователь может нажать «Войти по номеру» на экране PIN.
      // /register разрешён: чтобы PIN/FaceID-setup в конце регистрации не прерывался.
      if (status == AuthStatus.registeredLoggedOut) {
        if (loc == '/login' ||
            loc == '/login/pin' ||
            loc == '/login/faceid' ||
            loc.startsWith('/register')) {
          return null;
        }
        // Любой другой маршрут (в т.ч. /splash) → ведём на нужный экран входа.
        return auth.faceIdEnabled ? '/login/faceid' : '/login/pin';
      }

      // Полностью авторизован — не пускаем обратно на авторизацию
      if (status == AuthStatus.authenticated && isPublic) {
        return '/dashboard';
      }

      return null;
    },
    routes: [
      GoRoute(
        path: '/splash',
        pageBuilder: (context, state) =>
            _buildPage(state, const SplashScreen()),
      ),
      GoRoute(
        path: '/onboarding',
        pageBuilder: (context, state) =>
            _buildPage(state, const OnboardingScreen()),
      ),
      GoRoute(
        path: '/auth',
        pageBuilder: (context, state) => _buildPage(state, const AuthScreen()),
      ),
      GoRoute(
        path: '/register',
        pageBuilder: (context, state) =>
            _buildPage(state, const RegistrationScreen()),
      ),
      GoRoute(
        path: '/login',
        pageBuilder: (context, state) => _buildPage(state, const LoginScreen()),
      ),
      GoRoute(
        path: '/login/pin',
        pageBuilder: (context, state) =>
            _buildPage(state, const LoginPinScreen()),
      ),
      GoRoute(
        path: '/login/faceid',
        pageBuilder: (context, state) =>
            _buildPage(state, const LoginFaceIdScreen()),
      ),
      GoRoute(
        path: '/dashboard',
        pageBuilder: (context, state) =>
            _buildPage(state, const DashboardScreen()),
      ),
      GoRoute(
        path: '/blog',
        pageBuilder: (context, state) =>
            _buildPage(state, const BlogScreen()),
      ),
      GoRoute(
        path: '/blog/:slug',
        pageBuilder: (context, state) => _buildPage(
          state,
          ArticleScreen(slug: state.pathParameters['slug'] ?? ''),
        ),
      ),
      GoRoute(
        path: '/promotion/:slug',
        pageBuilder: (context, state) => _buildPage(
          state,
          PromotionDetailScreen(slug: state.pathParameters['slug'] ?? ''),
        ),
      ),
      GoRoute(
        path: '/booking',
        pageBuilder: (context, state) {
          final doctor = state.uri.queryParameters['doctor'];
          final service = state.uri.queryParameters['service'];
          final aptIdRaw = state.uri.queryParameters['appointmentId'];
          final appointmentId =
              aptIdRaw != null ? int.tryParse(aptIdRaw) : null;
          return _buildPageSlide(
            state,
            BookingScreen(
              doctorSlug: doctor?.isNotEmpty == true ? doctor : null,
              serviceSlug: service?.isNotEmpty == true ? service : null,
              appointmentId: appointmentId,
            ),
          );
        },
      ),
    ],
  );
});

/// Пробрасывает изменения [authControllerProvider] в GoRouter,
/// чтобы тот пересчитывал `redirect`.
class _AuthRefreshNotifier extends ChangeNotifier {
  _AuthRefreshNotifier(this._ref) {
    _sub = _ref.listen<AuthState>(
      authControllerProvider,
      (previous, next) {
        if (previous?.status != next.status) {
          notifyListeners();
        }
      },
    );
  }

  final Ref _ref;
  late final ProviderSubscription<AuthState> _sub;

  @override
  void dispose() {
    _sub.close();
    super.dispose();
  }
}

CustomTransitionPage<void> _buildPage(
  GoRouterState state,
  Widget child,
) {
  return CustomTransitionPage<void>(
    key: state.pageKey,
    child: child,
    transitionsBuilder: (context, animation, secondaryAnimation, child) {
      return FadeTransition(opacity: animation, child: child);
    },
    transitionDuration: const Duration(milliseconds: 280),
  );
}

/// Slide-up transition for full-screen modal flows (e.g. booking wizard).
CustomTransitionPage<void> _buildPageSlide(
  GoRouterState state,
  Widget child,
) {
  return CustomTransitionPage<void>(
    key: state.pageKey,
    child: child,
    transitionsBuilder: (context, animation, secondaryAnimation, child) {
      final curved = CurvedAnimation(
        parent: animation,
        curve: Curves.easeOutCubic,
      );
      return SlideTransition(
        position: Tween<Offset>(
          begin: const Offset(0, 1),
          end: Offset.zero,
        ).animate(curved),
        child: child,
      );
    },
    transitionDuration: const Duration(milliseconds: 320),
  );
}
