import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

import '../core/api_client.dart';
import '../core/constants.dart';

/// Laravel Reverb (Pusher protokoli) orqali real-time obuna.
/// Buyurtma kanaliga ulanib, status va kuryer lokatsiyasini jonli oladi.
class RealtimeService {
  final PusherChannelsFlutter _pusher = PusherChannelsFlutter.getInstance();
  bool _initialized = false;

  /// [onStatus] — buyurtma statusi o'zgarganda
  /// [onLocation] — kuryer lokatsiyasi yangilanganda (lat, lng)
  Future<void> subscribeToOrder(
    int orderId, {
    required void Function(Map<String, dynamic> data) onStatus,
    required void Function(double lat, double lng) onLocation,
  }) async {
    final token = await ApiClient.instance.getToken();

    if (!_initialized) {
      await _pusher.init(
        apiKey: AppConfig.reverbKey,
        cluster: 'mt1', // Reverb uchun ahamiyatsiz, lekin talab qilinadi
        host: AppConfig.reverbHost,
        wsPort: AppConfig.reverbPort,
        wssPort: AppConfig.reverbPort,
        useTLS: AppConfig.reverbTLS,
        // Private kanal avtorizatsiyasi backend /broadcasting/auth orqali
        onAuthorizer: (channelName, socketId, options) async {
          final res = await ApiClient.instance.dio.post(
            '${AppConfig.apiBaseUrl.replaceAll('/api', '')}/broadcasting/auth',
            data: {'socket_id': socketId, 'channel_name': channelName},
            options: Options(headers: {'Authorization': 'Bearer $token'}),
          );
          return Map<String, dynamic>.from(res.data);
        },
        onEvent: (event) {
          final data = _decode(event.data);
          if (event.eventName == 'order.status') {
            onStatus(data);
          } else if (event.eventName == 'courier.location') {
            onLocation(
              (data['lat'] as num).toDouble(),
              (data['lng'] as num).toDouble(),
            );
          }
        },
      );
      await _pusher.connect();
      _initialized = true;
    }

    await _pusher.subscribe(channelName: 'private-order.$orderId');
  }

  Map<String, dynamic> _decode(dynamic raw) {
    if (raw is String) {
      try {
        return Map<String, dynamic>.from(jsonDecode(raw));
      } catch (_) {
        return {};
      }
    }
    if (raw is Map) return Map<String, dynamic>.from(raw);
    return {};
  }

  Future<void> unsubscribe(int orderId) async {
    try {
      await _pusher.unsubscribe(channelName: 'private-order.$orderId');
    } catch (_) {}
  }

  Future<void> disconnect() async {
    try {
      await _pusher.disconnect();
    } catch (_) {}
    _initialized = false;
  }
}
