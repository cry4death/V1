import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/notifications/providers.dart';
import '../core/network/dio_client.dart';
import '../features/auth/domain/auth_state.dart';
import '../features/auth/presentation/controllers/auth_controller.dart';
import 'router.dart';

class App extends ConsumerWidget {
  const App({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // Запускаем FCM-сервис и показ foreground-уведомлений через local notifications
    ref.watch(notificationControllerProvider);

    // Синхронизируем FCM-токен сразу при старте (анонимно, до логина)
    ref.watch(pushTokenSyncProvider);

    // После успешного входа (PIN / Face ID / OTP) — отправляем токен снова,
    // теперь уже с действующим Authorization-заголовком
    ref.listen<AuthState>(authControllerProvider, (previous, current) {
      if (current.status == AuthStatus.authenticated &&
          previous?.status != AuthStatus.authenticated) {
        ref.read(pushTokenSyncProvider).resendAfterLogin();
      }
    });

    ref.listen<int>(authSessionExpiredSignalProvider, (previous, current) {
      if (previous != null && current > previous) {
        ref.read(authControllerProvider.notifier).expireSession();
      }
    });

    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
      ),
    );

    final router = ref.watch(appRouterProvider);

    return MaterialApp.router(
      title: 'Маяк Здоровья',
      debugShowCheckedModeBanner: false,
      routerConfig: router,
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: const [
        Locale('ru', 'RU'),
        Locale('en', 'US'),
      ],
      locale: const Locale('ru', 'RU'),
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF4682B4),
        ),
        useMaterial3: true,
        scaffoldBackgroundColor: const Color(0xFFF7F9FC),
        splashColor: Colors.transparent,
        highlightColor: Colors.transparent,
      ),
    );
  }
}
