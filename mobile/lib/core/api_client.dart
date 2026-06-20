import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import 'constants.dart';

/// Markaziy HTTP klient. Tokenni avtomatik qo'shadi.
class ApiClient {
  ApiClient._internal() {
    _dio = Dio(
      BaseOptions(
        baseUrl: AppConfig.apiBaseUrl,
        connectTimeout: const Duration(seconds: 20),
        receiveTimeout: const Duration(seconds: 20),
        headers: {'Accept': 'application/json'},
      ),
    );

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _storage.read(key: StorageKeys.token);
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onError: (e, handler) {
          handler.next(e);
        },
      ),
    );
  }

  static final ApiClient instance = ApiClient._internal();

  late final Dio _dio;
  final _storage = const FlutterSecureStorage();

  Dio get dio => _dio;

  Future<void> saveToken(String token, String role) async {
    await _storage.write(key: StorageKeys.token, value: token);
    await _storage.write(key: StorageKeys.role, value: role);
  }

  Future<String?> getToken() => _storage.read(key: StorageKeys.token);

  Future<String?> getRole() => _storage.read(key: StorageKeys.role);

  Future<void> clear() async {
    await _storage.deleteAll();
  }

  /// Dio xatosidan o'qiladigan xabar olish.
  static String errorMessage(Object error) {
    if (error is DioException) {
      final data = error.response?.data;
      if (data is Map && data['message'] != null) {
        return data['message'].toString();
      }
      if (data is Map && data['errors'] is Map) {
        final errors = (data['errors'] as Map).values.first;
        if (errors is List && errors.isNotEmpty) return errors.first.toString();
      }
      return error.message ?? 'Tarmoq xatosi';
    }
    return error.toString();
  }
}
