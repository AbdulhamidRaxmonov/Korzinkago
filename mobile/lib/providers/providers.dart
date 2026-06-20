import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/api_client.dart';
import '../models/models.dart';
import '../services/api_services.dart';

/// Autentifikatsiya holati.
class AuthState {
  final bool loading;
  final UserModel? user;
  final bool initialized;

  AuthState({this.loading = false, this.user, this.initialized = false});

  bool get isLoggedIn => user != null;

  AuthState copyWith({bool? loading, UserModel? user, bool? initialized, bool clearUser = false}) {
    return AuthState(
      loading: loading ?? this.loading,
      user: clearUser ? null : (user ?? this.user),
      initialized: initialized ?? this.initialized,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  AuthNotifier() : super(AuthState()) {
    _bootstrap();
  }

  Future<void> _bootstrap() async {
    final token = await ApiClient.instance.getToken();
    if (token == null) {
      state = state.copyWith(initialized: true);
      return;
    }
    try {
      final user = await AuthService.me();
      state = state.copyWith(user: user, initialized: true);
    } catch (_) {
      await ApiClient.instance.clear();
      state = state.copyWith(initialized: true);
    }
  }

  void setUser(UserModel user) => state = state.copyWith(user: user);

  Future<void> logout() async {
    await AuthService.logout();
    state = state.copyWith(clearUser: true);
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) => AuthNotifier());

/// Savat holati.
class CartNotifier extends StateNotifier<List<CartItem>> {
  CartNotifier() : super([]);

  double get total => state.fold(0, (sum, item) => sum + item.total);
  int get count => state.length;

  void _setFrom(Map<String, dynamic> data) {
    state = (data['items'] as List).map((e) => CartItem.fromJson(e)).toList();
  }

  Future<void> load() async {
    _setFrom(await CartService.get());
  }

  Future<void> add(int productId, {double? quantity}) async {
    _setFrom(await CartService.add(productId, quantity: quantity));
  }

  Future<void> updateQty(int itemId, double quantity) async {
    _setFrom(await CartService.update(itemId, quantity));
  }

  Future<void> remove(int itemId) async {
    _setFrom(await CartService.remove(itemId));
  }

  Future<void> clear() async {
    await CartService.clear();
    state = [];
  }

  /// Mahsulot bo'yicha savatdagi miqdorni topish.
  double quantityOf(int productId) {
    return itemOf(productId)?.quantity ?? 0;
  }

  CartItem? itemOf(int productId) {
    for (final e in state) {
      if (e.product?.id == productId) return e;
    }
    return null;
  }
}

final cartProvider = StateNotifierProvider<CartNotifier, List<CartItem>>((ref) => CartNotifier());

/// Bosh sahifa ma'lumotlari.
final homeProvider = FutureProvider.autoDispose((ref) => CatalogService.home());

/// Kategoriyalar.
final categoriesProvider = FutureProvider.autoDispose((ref) => CatalogService.categories());

/// Buyurtmalar ro'yxati.
final ordersProvider = FutureProvider.autoDispose((ref) => OrderService.list());

/// Manzillar.
final addressesProvider = FutureProvider.autoDispose((ref) => AddressService.list());
