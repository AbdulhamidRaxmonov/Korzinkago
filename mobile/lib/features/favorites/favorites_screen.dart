import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme.dart';
import '../../providers/providers.dart';
import '../../widgets/product_card.dart';

class FavoritesScreen extends ConsumerWidget {
  const FavoritesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // Sevimlilar to'plamini kuzatib, o'zgarganda ro'yxatni yangilash
    ref.watch(favoritesProvider);
    final products = ref.watch(favoriteProductsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Sevimlilar')),
      body: products.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Xato: $e')),
        data: (list) => list.isEmpty
            ? const Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.favorite_border, size: 80, color: Colors.grey),
                    SizedBox(height: 16),
                    Text('Sevimli mahsulotlar yo\'q',
                        style: TextStyle(color: AppColors.textSecondary)),
                  ],
                ),
              )
            : RefreshIndicator(
                onRefresh: () async => ref.refresh(favoriteProductsProvider.future),
                child: GridView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: list.length,
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                    childAspectRatio: 0.6,
                  ),
                  itemBuilder: (_, i) => ProductCard(product: list[i]),
                ),
              ),
      ),
    );
  }
}
