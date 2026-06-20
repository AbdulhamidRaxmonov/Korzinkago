import '../core/api_client.dart';
import '../models/models.dart';

final _dio = ApiClient.instance.dio;

/// Autentifikatsiya servisi.
class AuthService {
  static Future<Map<String, dynamic>> sendOtp(String phone) async {
    final res = await _dio.post('/auth/send-otp', data: {'phone': phone});
    return Map<String, dynamic>.from(res.data);
  }

  static Future<UserModel> verifyOtp(String phone, String code, {String? name}) async {
    final res = await _dio.post('/auth/verify-otp', data: {
      'phone': phone,
      'code': code,
      if (name != null) 'name': name,
    });
    final user = UserModel.fromJson(res.data['user']);
    await ApiClient.instance.saveToken(res.data['token'], user.role);
    return user;
  }

  static Future<UserModel> me() async {
    final res = await _dio.get('/me');
    return UserModel.fromJson(res.data);
  }

  static Future<void> logout() async {
    try {
      await _dio.post('/logout');
    } catch (_) {}
    await ApiClient.instance.clear();
  }
}

/// Katalog servisi.
class CatalogService {
  static Future<Map<String, dynamic>> home() async {
    final res = await _dio.get('/home');
    return Map<String, dynamic>.from(res.data);
  }

  static Future<List<Category>> categories() async {
    final res = await _dio.get('/categories');
    return (res.data as List).map((e) => Category.fromJson(e)).toList();
  }

  static Future<List<Product>> products({
    int? categoryId,
    String? search,
    String? sort,
    int page = 1,
  }) async {
    final res = await _dio.get('/products', queryParameters: {
      if (categoryId != null) 'category_id': categoryId,
      if (search != null && search.isNotEmpty) 'search': search,
      if (sort != null) 'sort': sort,
      'page': page,
    });
    return (res.data['data'] as List).map((e) => Product.fromJson(e)).toList();
  }
}

/// Savat servisi.
class CartService {
  static Future<Map<String, dynamic>> get() async {
    final res = await _dio.get('/cart');
    return Map<String, dynamic>.from(res.data);
  }

  static Future<Map<String, dynamic>> add(int productId, {double? quantity}) async {
    final res = await _dio.post('/cart', data: {
      'product_id': productId,
      if (quantity != null) 'quantity': quantity,
    });
    return Map<String, dynamic>.from(res.data);
  }

  static Future<Map<String, dynamic>> update(int itemId, double quantity) async {
    final res = await _dio.put('/cart/$itemId', data: {'quantity': quantity});
    return Map<String, dynamic>.from(res.data);
  }

  static Future<Map<String, dynamic>> remove(int itemId) async {
    final res = await _dio.delete('/cart/$itemId');
    return Map<String, dynamic>.from(res.data);
  }

  static Future<void> clear() async {
    await _dio.post('/cart/clear');
  }
}

/// Manzillar servisi.
class AddressService {
  static Future<List<Address>> list() async {
    final res = await _dio.get('/addresses');
    return (res.data as List).map((e) => Address.fromJson(e)).toList();
  }

  static Future<Address> create(Map<String, dynamic> data) async {
    final res = await _dio.post('/addresses', data: data);
    return Address.fromJson(res.data);
  }

  static Future<void> delete(int id) async {
    await _dio.delete('/addresses/$id');
  }

  static Future<String> reverseGeocode(double lat, double lng) async {
    final res = await _dio.post('/map/reverse', data: {'lat': lat, 'lng': lng});
    return res.data['address'] ?? '';
  }
}

/// Buyurtmalar servisi.
class OrderService {
  static Future<Map<String, dynamic>> calculate(double lat, double lng) async {
    final res = await _dio.post('/orders/calculate', data: {'lat': lat, 'lng': lng});
    return Map<String, dynamic>.from(res.data);
  }

  static Future<Map<String, dynamic>> create(Map<String, dynamic> data) async {
    final res = await _dio.post('/orders', data: data);
    return Map<String, dynamic>.from(res.data);
  }

  static Future<List<Order>> list() async {
    final res = await _dio.get('/orders');
    return (res.data['data'] as List).map((e) => Order.fromJson(e)).toList();
  }

  static Future<Order> show(int id) async {
    final res = await _dio.get('/orders/$id');
    return Order.fromJson(res.data);
  }

  static Future<void> cancel(int id, {String? reason}) async {
    await _dio.post('/orders/$id/cancel', data: {'reason': reason});
  }

  static Future<String> paymeCheckout(int orderId) async {
    final res = await _dio.post('/payme/checkout', data: {'order_id': orderId});
    return res.data['checkout_url'];
  }
}

/// Kuryer servisi.
class CourierService {
  static Future<bool> toggleOnline(bool online) async {
    final res = await _dio.post('/courier/online', data: {'is_online': online});
    return res.data['is_online'] == true;
  }

  static Future<void> updateLocation(double lat, double lng) async {
    await _dio.post('/courier/location', data: {'lat': lat, 'lng': lng});
  }

  static Future<List<Order>> available() async {
    final res = await _dio.get('/courier/available');
    return (res.data as List).map((e) => Order.fromJson(e)).toList();
  }

  static Future<Map<String, dynamic>> myOrders() async {
    final res = await _dio.get('/courier/orders');
    return {
      'active': (res.data['active'] as List).map((e) => Order.fromJson(e)).toList(),
      'history': (res.data['history'] as List).map((e) => Order.fromJson(e)).toList(),
    };
  }

  static Future<void> accept(int orderId) async {
    await _dio.post('/courier/orders/$orderId/accept');
  }

  static Future<void> updateStatus(int orderId, String status) async {
    await _dio.post('/courier/orders/$orderId/status', data: {'status': status});
  }
}
