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
}

/// Token saqlash uchun kalitlar.
class StorageKeys {
  static const String token = 'auth_token';
  static const String role = 'user_role';
}
