import 'package:go_router/go_router.dart';

import 'notification_payload.dart';

/// Определяет, куда вести пользователя по тапу на push-уведомление.
class NotificationRouter {
  NotificationRouter(this._router);

  final GoRouter _router;

  void handleTap(NotificationPayload payload) {
    final screen = payload.screen;
    if (screen != null && screen.isNotEmpty) {
      _router.go(screen);
      return;
    }

    switch (payload.type) {
      case 'news':
        if (payload.entityId != null) _router.go('/news/${payload.entityId}');
        break;
      case 'appointment':
        _router.go('/dashboard');
        break;
      case 'promo':
        _router.go('/dashboard');
        break;
      default:
        _router.go('/dashboard');
    }
  }
}
