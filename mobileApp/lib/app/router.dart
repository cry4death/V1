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
import '../features/splash/splash_screen.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  final refresh = _AuthRefreshNotifier(ref);
  ref.onDispose(refresh.dispose);

  return GoRouter(
    initialLocation: '/dashboard',
    refreshListenable: refresh,
    // DEV: авторизация временно отключена — любой маршрут доступен,
    // чтобы работать с бэкендом врачей. Вернуть guard перед релизом.
    redirect: (context, state) => null,
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
