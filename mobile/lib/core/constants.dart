/// Ilova konstantalari.
class AppConfig {
  /// Backend API manzili.
  /// Emulyator uchun: Android -> http://10.0.2.2:8000, iOS -> http://localhost:8000
  /// Haqiqiy qurilma uchun kompyuteringiz IP manzilini kiriting.
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api',
  );

  /// Google Maps API kaliti (faqat shu yerda emas, native config'da ham kerak).
  static const String googleMapsApiKey = String.fromEnvironment(
    'GOOGLE_MAPS_API_KEY',
    defaultValue: '',
  );

  /// Toshkent markaziy koordinatasi (default xarita pozitsiyasi).
  static const double defaultLat = 41.2995;
  static const double defaultLng = 69.2401;

  /// Laravel Reverb (WebSocket) sozlamalari.
  static const String reverbKey = String.fromEnvironment(
    'REVERB_APP_KEY',
    defaultValue: 'korzinkago_key',
  );
  static const String reverbHost = String.fromEnvironment(
    'REVERB_HOST',
    defaultValue: '10.0.2.2',
  );
  static const int reverbPort = int.fromEnvironment('REVERB_PORT', defaultValue: 8080);
  static const bool reverbTLS = bool.fromEnvironment('REVERB_TLS', defaultValue: false);
}

/// Token saqlash uchun kalitlar.
class StorageKeys {
  static const String token = 'auth_token';
  static const String role = 'user_role';
}
