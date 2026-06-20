import 'dart:io' show Platform;

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import '../core/api_client.dart';

/// Foydaga: FCM push-bildirishnomalarni boshqarish.
/// Login bo'lgandan keyin [register] chaqiriladi.
class PushService {
  static final FlutterLocalNotificationsPlugin _local =
      FlutterLocalNotificationsPlugin();

  /// Firebase'ni ishga tushirish (main da chaqiriladi).
  static Future<void> init() async {
    try {
      await Firebase.initializeApp();

      const androidInit = AndroidInitializationSettings('@mipmap/ic_launcher');
      const iosInit = DarwinInitializationSettings();
      await _local.initialize(
        const InitializationSettings(android: androidInit, iOS: iosInit),
      );

      // Foreground'da kelgan xabarni ko'rsatish
      FirebaseMessaging.onMessage.listen(_showLocal);
    } catch (_) {
      // Firebase sozlanmagan bo'lsa, ilova baribir ishlayveradi
    }
  }

  /// Ruxsat so'rash, tokenni olish va backendga yuborish.
  static Future<void> register() async {
    try {
      final messaging = FirebaseMessaging.instance;
      await messaging.requestPermission();

      final token = await messaging.getToken();
      if (token == null) return;

      await _sendToken(token);

      // Token yangilanganda qayta yuborish
      messaging.onTokenRefresh.listen(_sendToken);
    } catch (_) {}
  }

  static Future<void> _sendToken(String token) async {
    try {
      await ApiClient.instance.dio.post('/device-token', data: {
        'token': token,
        'platform': Platform.isIOS ? 'ios' : 'android',
      });
    } catch (_) {}
  }

  static void _showLocal(RemoteMessage message) {
    final n = message.notification;
    if (n == null) return;

    _local.show(
      n.hashCode,
      n.title,
      n.body,
      const NotificationDetails(
        android: AndroidNotificationDetails(
          'korzinkago_default',
          'Korzinkago bildirishnomalar',
          importance: Importance.high,
          priority: Priority.high,
        ),
        iOS: DarwinNotificationDetails(),
      ),
    );
  }
}
