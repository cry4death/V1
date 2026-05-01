import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app/app.dart';
import 'core/firebase_config.dart';
import 'core/notifications/firebase_messaging_service.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  if (kFirebaseEnabled) {
    try {
      await Firebase.initializeApp();
      FirebaseMessaging.onBackgroundMessage(
        firebaseMessagingBackgroundHandler,
      );
    } catch (e, st) {
      if (kDebugMode) {
        debugPrint('[main] Firebase init failed: $e\n$st');
      }
    }
  } else if (kDebugMode) {
    debugPrint(
        '[main] Firebase disabled (USE_FIREBASE=false). Set --dart-define=USE_FIREBASE=true when configured.');
  }

  runApp(const ProviderScope(child: App()));
}
