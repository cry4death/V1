import 'dart:async';

import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';

import 'notification_payload.dart';

/// Обработчик фоновых сообщений — должен быть top-level функцией.
///
/// Подключается в `main.dart`:
/// ```dart
/// FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
/// ```
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  if (kDebugMode) {
    debugPrint('[FCM:background] ${message.messageId} ${message.data}');
  }
  // Здесь нельзя трогать UI-слой. Максимум — писать в storage/лог.
}

/// Инициализация FCM, запрос permission, получение/refresh токена
/// и подписка на входящие сообщения.
///
/// В UI используется через `firebaseMessagingServiceProvider`.
class FirebaseMessagingService {
  FirebaseMessagingService({FirebaseMessaging? messaging})
      : _messaging = messaging ?? FirebaseMessaging.instance;

  final FirebaseMessaging _messaging;

  final StreamController<NotificationPayload> _incoming =
      StreamController<NotificationPayload>.broadcast();
  final StreamController<NotificationPayload> _opened =
      StreamController<NotificationPayload>.broadcast();
  final StreamController<String> _tokenRefresh =
      StreamController<String>.broadcast();

  /// Сообщения, пришедшие в foreground.
  Stream<NotificationPayload> get onMessage => _incoming.stream;

  /// Сообщения, по которым пользователь тапнул (app в background / terminated).
  Stream<NotificationPayload> get onMessageOpenedApp => _opened.stream;

  /// Новый FCM token — нужно отправить на backend.
  Stream<String> get onTokenRefresh => _tokenRefresh.stream;

  bool _initialized = false;

  Future<void> init() async {
    if (_initialized) return;
    _initialized = true;

    await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    await _messaging.setForegroundNotificationPresentationOptions(
      alert: true,
      badge: true,
      sound: true,
    );

    FirebaseMessaging.onMessage.listen((msg) {
      _incoming.add(NotificationPayload.fromData(_mergeNotification(msg)));
    });

    FirebaseMessaging.onMessageOpenedApp.listen((msg) {
      _opened.add(NotificationPayload.fromData(_mergeNotification(msg)));
    });

    // Сообщение, от которого приложение было запущено из terminated.
    final initial = await _messaging.getInitialMessage();
    if (initial != null) {
      _opened.add(NotificationPayload.fromData(_mergeNotification(initial)));
    }

    _messaging.onTokenRefresh.listen(_tokenRefresh.add);
  }

  Future<String?> getToken() => _messaging.getToken();

  Future<void> deleteToken() => _messaging.deleteToken();

  /// Объединяет data-payload с title/body из FCM notification-поля.
  /// Нужно для foreground-сообщений: там title/body живут в msg.notification,
  /// а не в msg.data.
  Map<String, dynamic> _mergeNotification(RemoteMessage msg) {
    return {
      ...msg.data,
      if (msg.notification?.title != null) 'title': msg.notification!.title!,
      if (msg.notification?.body != null) 'body': msg.notification!.body!,
    };
  }

  Future<void> subscribeToTopic(String topic) =>
      _messaging.subscribeToTopic(topic);

  Future<void> unsubscribeFromTopic(String topic) =>
      _messaging.unsubscribeFromTopic(topic);

  void dispose() {
    _incoming.close();
    _opened.close();
    _tokenRefresh.close();
  }
}
