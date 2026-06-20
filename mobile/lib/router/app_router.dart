import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/auth/phone_screen.dart';
import '../features/auth/otp_screen.dart';
import '../features/auth/splash_screen.dart';
import '../features/shell/main_shell.dart';
import '../features/catalog/category_screen.dart';
import '../features/catalog/product_screen.dart';
import '../features/checkout/checkout_screen.dart';
import '../features/checkout/map_picker_screen.dart';
import '../features/favorites/favorites_screen.dart';
import '../features/orders/order_detail_screen.dart';
import '../features/orders/review_screen.dart';
import '../features/courier/courier_home.dart';
import '../features/courier/courier_order_screen.dart';
import '../models/models.dart';
import '../providers/providers.dart';

final routerProvider = Provider<GoRouter>((ref) {
  final auth = ref.watch(authProvider);

  return GoRouter(
    initialLocation: '/splash',
    redirect: (context, state) {
      if (!auth.initialized) return '/splash';

      final loggingIn = state.matchedLocation == '/phone' ||
          state.matchedLocation == '/otp' ||
          state.matchedLocation == '/splash';

      if (!auth.isLoggedIn) {
        return loggingIn && state.matchedLocation != '/splash' ? null : '/phone';
      }

      // Login bo'lgan
      if (loggingIn) {
        return auth.user!.isCourier ? '/courier' : '/home';
      }
      return null;
    },
    routes: [
      GoRoute(path: '/splash', builder: (_, __) => const SplashScreen()),
      GoRoute(path: '/phone', builder: (_, __) => const PhoneScreen()),
      GoRoute(
        path: '/otp',
        builder: (_, s) => OtpScreen(phone: s.uri.queryParameters['phone'] ?? ''),
      ),

      // User shell (bottom nav)
      GoRoute(path: '/home', builder: (_, __) => const MainShell(tab: 0)),
      GoRoute(path: '/catalog', builder: (_, __) => const MainShell(tab: 1)),
      GoRoute(path: '/cart', builder: (_, __) => const MainShell(tab: 2)),
      GoRoute(path: '/orders', builder: (_, __) => const MainShell(tab: 3)),
      GoRoute(path: '/profile', builder: (_, __) => const MainShell(tab: 4)),

      GoRoute(
        path: '/category/:id',
        builder: (_, s) => CategoryScreen(
          categoryId: int.parse(s.pathParameters['id']!),
          title: s.uri.queryParameters['title'] ?? 'Mahsulotlar',
        ),
      ),
      GoRoute(
        path: '/product',
        builder: (_, s) => ProductScreen(product: s.extra as Product),
      ),
      GoRoute(path: '/checkout', builder: (_, __) => const CheckoutScreen()),
      GoRoute(path: '/map-picker', builder: (_, __) => const MapPickerScreen()),
      GoRoute(
        path: '/order/:id',
        builder: (_, s) => OrderDetailScreen(orderId: int.parse(s.pathParameters['id']!)),
      ),
      GoRoute(path: '/favorites', builder: (_, __) => const FavoritesScreen()),
      GoRoute(
        path: '/review',
        builder: (_, s) => ReviewScreen(order: s.extra as Order),
      ),

      // Courier
      GoRoute(path: '/courier', builder: (_, __) => const CourierHome()),
      GoRoute(
        path: '/courier/order',
        builder: (_, s) => CourierOrderScreen(order: s.extra as Order),
      ),
    ],
  );
});
