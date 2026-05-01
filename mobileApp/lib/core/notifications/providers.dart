import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../network/dio_client.dart';
import 'firebase_messaging_service.dart';
import 'local_notifications_service.dart';
import 'notification_payload.dart';

/// Долгоживущий singleton FCM-сервиса.
final firebaseMessagingServiceProvider = Provider<FirebaseMessagingService>(
  (ref) {
    final service = FirebaseMessagingService();
    ref.onDispose(service.dispose);
    return service;
  },
);

final localNotificationsServiceProvider = Provider<LocalNotificationsService>(
  (ref) => LocalNotificationsService(),
);

/// Слушает входящие push-сообщения в foreground и показывает
/// локальное уведомление (поведение можно менять под бизнес-логику).
///
/// Активируется через `ref.listen(notificationControllerProvider, ...)`
/// или просто `ref.watch(...)` где это нужно.
final notificationControllerProvider = Provider<_NotificationController>(
  (ref) {
    final fcm = ref.watch(firebaseMessagingServiceProvider);
    final local = ref.watch(localNotificationsServiceProvider);

    final controller = _NotificationController(fcm: fcm, local: local);
    controller.start();
    ref.onDispose(controller.dispose);
    return controller;
  },
);

class _NotificationController {
  _NotificationController({required this.fcm, required this.local});

  final FirebaseMessagingService fcm;
  final LocalNotificationsService local;

  final StreamController<NotificationPayload> _taps =
      StreamController<NotificationPayload>.broadcast();

  StreamSubscription<NotificationPayload>? _fgSub;
  StreamSubscription<NotificationPayload>? _openedSub;

  /// Поток тапов по уведомлениям — на него подписывается роутинг-слой.
  Stream<NotificationPayload> get onTap => _taps.stream;

  void start() {
    _fgSub = fcm.onMessage.listen((payload) async {
      try {
        await local.showFromPayload(payload);
      } catch (e, st) {
        if (kDebugMode) {
          debugPrint('[notifications] local show failed: $e\n$st');
        }
      }
    });

    _openedSub = fcm.onMessageOpenedApp.listen(_taps.add);
  }

  void dispose() {
    _fgSub?.cancel();
    _openedSub?.cancel();
    _taps.close();
  }
}

/// Синхронизация FCM-токена с Laravel backend.
///
/// Отправляет токен при старте, при обновлении (`onTokenRefresh`) и после
/// входа пользователя (вызывается из `AuthController` после успешного логина).
final pushTokenSyncProvider = Provider<PushTokenSync>((ref) {
  final dio = ref.watch(dioProvider);
  final fcm = ref.watch(firebaseMessagingServiceProvider);

  final sync = PushTokenSync(dio: dio, fcm: fcm);
  sync.start();
  ref.onDispose(sync.dispose);
  return sync;
});

class PushTokenSync {
  PushTokenSync({required this.dio, required this.fcm});

  final Dio dio;
  final FirebaseMessagingService fcm;

  StreamSubscription<String>? _refreshSub;

  void start() {
    _refreshSub = fcm.onTokenRefresh.listen(_sendToBackend);
    // первичная отправка
    fcm.getToken().then((token) {
      if (token != null) _sendToBackend(token);
    }).catchError((_) {});
  }

  /// Переотправить токен — вызывается из AuthController после login/register.
  Future<void> resendAfterLogin() async {
    final token = await fcm.getToken();
    if (token != null) await _sendToBackend(token);
  }

  Future<void> _sendToBackend(String token) async {
    try {
      await dio.post(
        '/devices/register',
        data: {
          'fcm_token': token,
          'platform': defaultTargetPlatform.name,
        },
      );
    } catch (e) {
      if (kDebugMode) {
        debugPrint('[push] failed to register token: $e');
      }
    }
  }

  void dispose() {
    _refreshSub?.cancel();
  }
}
