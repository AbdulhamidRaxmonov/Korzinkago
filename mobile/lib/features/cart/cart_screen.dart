import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../providers/providers.dart';

class CartScreen extends ConsumerWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final items = ref.watch(cartProvider);
    final notifier = ref.read(cartProvider.notifier);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Savat'),
        automaticallyImplyLeading: false,
        actions: [
          if (items.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.delete_outline),
              onPressed: () => notifier.clear(),
            ),
        ],
      ),
      body: items.isEmpty
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.shopping_cart_outlined, size: 80, color: Colors.grey),
                  SizedBox(height: 16),
                  Text('Savat bo\'sh', style: TextStyle(color: AppColors.textSecondary)),
                ],
              ),
            )
          : ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: items.length,
              separatorBuilder: (_, __) => const Divider(height: 16),
              itemBuilder: (_, i) {
                final item = items[i];
                final p = item.product;
                if (p == null) return const SizedBox.shrink();
                return Row(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: SizedBox(
                        width: 64,
                        height: 64,
                        child: p.image != null
                            ? CachedNetworkImage(imageUrl: p.image!, fit: BoxFit.cover)
                            : Container(
                                color: const Color(0xFFEFEFEF),
                                child: const Icon(Icons.image_outlined, color: Colors.grey),
                              ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(p.name, maxLines: 2, overflow: TextOverflow.ellipsis),
                          const SizedBox(height: 4),
                          Text(formatPrice(item.total),
                              style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.primary)),
                        ],
                      ),
                    ),
                    _qty(notifier, item.id, item.quantity, p.step, p.unit),
                  ],
                );
              },
            ),
      bottomNavigationBar: items.isEmpty
          ? null
          : SafeArea(
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: const BoxDecoration(
                  color: AppColors.surface,
                  boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 8)],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Jami:', style: TextStyle(fontSize: 16)),
                        Text(formatPrice(notifier.total),
                            style: const TextStyle(
                                fontSize: 20, fontWeight: FontWeight.bold)),
                      ],
                    ),
                    const SizedBox(height: 12),
                    ElevatedButton(
                      onPressed: () => context.push('/checkout'),
                      child: const Text('Rasmiylashtirish'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _qty(CartNotifier notifier, int id, double qty, double step, String unit) {
    return Container(
      decoration: BoxDecoration(
        border: Border.all(color: const Color(0xFFE5E7EB)),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        children: [
          IconButton(
            visualDensity: VisualDensity.compact,
            icon: const Icon(Icons.remove, size: 18),
            onPressed: () => notifier.updateQty(id, qty - step),
          ),
          Text(qty % 1 == 0 ? qty.toInt().toString() : qty.toString()),
          IconButton(
            visualDensity: VisualDensity.compact,
            icon: const Icon(Icons.add, size: 18),
            onPressed: () => notifier.updateQty(id, qty + step),
          ),
        ],
      ),
    );
  }
}
