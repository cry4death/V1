import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import 'notification_payload.dart';

/// Показ локальных уведомлений, когда приложение в foreground
/// (iOS + Android сами push не покажут, если мы хотим полный контроль).
class LocalNotificationsService {
  LocalNotificationsService([FlutterLocalNotificationsPlugin? plugin])
      : _plugin = plugin ?? FlutterLocalNotificationsPlugin();

  final FlutterLocalNotificationsPlugin _plugin;

  static const _androidChannel = AndroidNotificationChannel(
    'mayak_default',
    'Уведомления',
    description: 'Основные уведомления клиники',
    importance: Importance.high,
  );

  bool _initialized = false;

  Future<void> init() async {
    if (_initialized) return;
    _initialized = true;

    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const ios = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    await _plugin.initialize(
      const InitializationSettings(android: android, iOS: ios),
    );

    await _plugin
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_androidChannel);
  }

  Future<void> showFromPayload(NotificationPayload p) async {
    const android = AndroidNotificationDetails(
      'mayak_default',
      'Уведомления',
      importance: Importance.high,
      priority: Priority.high,
    );
    const ios = DarwinNotificationDetails();

    await _plugin.show(
      DateTime.now().millisecondsSinceEpoch ~/ 1000,
      p.title,
      p.body,
      const NotificationDetails(android: android, iOS: ios),
      payload: p.raw.toString(),
    );
  }
}
