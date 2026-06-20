import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/theme.dart';
import '../../models/models.dart';
import '../../providers/providers.dart';
import '../../widgets/product_card.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final home = ref.watch(homeProvider);

    return Scaffold(
      body: SafeArea(
        child: home.when(
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (e, _) => Center(child: Text('Xato: $e')),
          data: (data) {
            final categories = (data['categories'] as List?)
                    ?.map((e) => Category.fromJson(e))
                    .toList() ??
                [];
            final featured = (data['featured'] as List?)
                    ?.map((e) => Product.fromJson(e))
                    .toList() ??
                [];
            final discounts = (data['discounts'] as List?)
                    ?.map((e) => Product.fromJson(e))
                    .toList() ??
                [];

            return RefreshIndicator(
              onRefresh: () async => ref.refresh(homeProvider),
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _searchBar(context),
                  const SizedBox(height: 20),
                  _sectionTitle('Kategoriyalar'),
                  const SizedBox(height: 12),
                  _categories(context, categories),
                  const SizedBox(height: 20),
                  if (discounts.isNotEmpty) ...[
                    _sectionTitle('Chegirmalar 🔥'),
                    const SizedBox(height: 12),
                    _horizontalProducts(discounts),
                    const SizedBox(height: 20),
                  ],
                  if (featured.isNotEmpty) ...[
                    _sectionTitle('Tavsiya etamiz'),
                    const SizedBox(height: 12),
                    _grid(featured),
                  ],
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _searchBar(BuildContext context) {
    return GestureDetector(
      onTap: () => context.go('/catalog'),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(14),
        ),
        child: const Row(
          children: [
            Icon(Icons.search, color: AppColors.textSecondary),
            SizedBox(width: 10),
            Text('Mahsulot qidirish...',
                style: TextStyle(color: AppColors.textSecondary)),
          ],
        ),
      ),
    );
  }

  Widget _sectionTitle(String text) => Text(text,
      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold));

  Widget _categories(BuildContext context, List<Category> categories) {
    return SizedBox(
      height: 100,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: categories.length,
        separatorBuilder: (_, __) => const SizedBox(width: 12),
        itemBuilder: (_, i) {
          final c = categories[i];
          return GestureDetector(
            onTap: () => context.push('/category/${c.id}?title=${c.name}'),
            child: Column(
              children: [
                Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(Icons.category, color: AppColors.primary),
                ),
                const SizedBox(height: 6),
                SizedBox(
                  width: 70,
                  child: Text(c.name,
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 11)),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _horizontalProducts(List<Product> products) {
    return SizedBox(
      height: 250,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: products.length,
        separatorBuilder: (_, __) => const SizedBox(width: 12),
        itemBuilder: (_, i) =>
            SizedBox(width: 160, child: ProductCard(product: products[i])),
      ),
    );
  }

  Widget _grid(List<Product> products) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: products.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
        childAspectRatio: 0.62,
      ),
      itemBuilder: (_, i) => ProductCard(product: products[i]),
    );
  }
}
