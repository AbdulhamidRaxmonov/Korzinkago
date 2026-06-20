import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../models/models.dart';
import '../../services/api_services.dart';
import '../../widgets/product_card.dart';

final _categoryProductsProvider =
    FutureProvider.autoDispose.family<List<Product>, int>((ref, categoryId) {
  return CatalogService.products(categoryId: categoryId);
});

class CategoryScreen extends ConsumerWidget {
  final int categoryId;
  final String title;
  const CategoryScreen({super.key, required this.categoryId, required this.title});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final products = ref.watch(_categoryProductsProvider(categoryId));

    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: products.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Xato: $e')),
        data: (list) => list.isEmpty
            ? const Center(child: Text('Mahsulotlar topilmadi'))
            : GridView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: list.length,
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 12,
                  mainAxisSpacing: 12,
                  childAspectRatio: 0.62,
                ),
                itemBuilder: (_, i) => ProductCard(product: list[i]),
              ),
      ),
    );
  }
}
