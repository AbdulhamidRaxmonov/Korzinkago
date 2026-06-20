import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme.dart';
import '../../providers/providers.dart';
import '../cart/cart_screen.dart';
import '../catalog/catalog_screen.dart';
import '../home/home_screen.dart';
import '../orders/orders_screen.dart';
import '../profile/profile_screen.dart';

/// Foydalanuvchi uchun pastki navigatsiyali asosiy ekran.
class MainShell extends ConsumerStatefulWidget {
  final int tab;
  const MainShell({super.key, this.tab = 0});

  @override
  ConsumerState<MainShell> createState() => _MainShellState();
}

class _MainShellState extends ConsumerState<MainShell> {
  late int _index = widget.tab;

  final _pages = const [
    HomeScreen(),
    CatalogScreen(),
    CartScreen(),
    OrdersScreen(),
    ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    // Savatni boshlang'ich yuklash
    Future.microtask(() => ref.read(cartProvider.notifier).load());
  }

  @override
  Widget build(BuildContext context) {
    final cartCount = ref.watch(cartProvider).length;

    return Scaffold(
      body: IndexedStack(index: _index, children: _pages),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (i) => setState(() => _index = i),
        backgroundColor: AppColors.surface,
        indicatorColor: AppColors.primary.withOpacity(0.12),
        destinations: [
          const NavigationDestination(
              icon: Icon(Icons.home_outlined),
              selectedIcon: Icon(Icons.home, color: AppColors.primary),
              label: 'Bosh sahifa'),
          const NavigationDestination(
              icon: Icon(Icons.grid_view_outlined),
              selectedIcon: Icon(Icons.grid_view, color: AppColors.primary),
              label: 'Katalog'),
          NavigationDestination(
              icon: Badge(
                isLabelVisible: cartCount > 0,
                label: Text('$cartCount'),
                child: const Icon(Icons.shopping_cart_outlined),
              ),
              selectedIcon: const Icon(Icons.shopping_cart, color: AppColors.primary),
              label: 'Savat'),
          const NavigationDestination(
              icon: Icon(Icons.receipt_long_outlined),
              selectedIcon: Icon(Icons.receipt_long, color: AppColors.primary),
              label: 'Buyurtmalar'),
          const NavigationDestination(
              icon: Icon(Icons.person_outline),
              selectedIcon: Icon(Icons.person, color: AppColors.primary),
              label: 'Profil'),
        ],
      ),
    );
  }
}
