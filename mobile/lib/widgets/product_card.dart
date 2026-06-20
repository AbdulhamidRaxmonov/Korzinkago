import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../core/formatters.dart';
import '../core/theme.dart';
import '../models/models.dart';
import '../providers/providers.dart';

class ProductCard extends ConsumerWidget {
  final Product product;
  const ProductCard({super.key, required this.product});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cart = ref.watch(cartProvider);
    final notifier = ref.read(cartProvider.notifier);
    final qty = notifier.quantityOf(product.id);
    final item = notifier.itemOf(product.id);

    return GestureDetector(
      onTap: () => context.push('/product', extra: product),
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                ClipRRect(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                  child: AspectRatio(
                    aspectRatio: 1.3,
                    child: product.image != null
                        ? CachedNetworkImage(
                            imageUrl: product.image!,
                            fit: BoxFit.cover,
                            errorWidget: (_, __, ___) => _placeholder(),
                          )
                        : _placeholder(),
                  ),
                ),
                if (product.hasDiscount)
                  Positioned(
                    top: 8,
                    left: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.danger,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text('-${product.discountPercent}%',
                          style: const TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.bold)),
                    ),
                  ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
                  ),
                  const SizedBox(height: 6),
                  Text(formatPrice(product.price),
                      style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: AppColors.primary)),
                  if (product.hasDiscount)
                    Text(
                      formatPrice(product.oldPrice!),
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary,
                        decoration: TextDecoration.lineThrough,
                      ),
                    ),
                  const SizedBox(height: 8),
                  qty > 0 && item != null
                      ? _qtyControl(notifier, item, qty)
                      : SizedBox(
                          width: double.infinity,
                          child: OutlinedButton(
                            onPressed: () => notifier.add(product.id),
                            style: OutlinedButton.styleFrom(
                              minimumSize: const Size.fromHeight(36),
                              foregroundColor: AppColors.primary,
                              side: const BorderSide(color: AppColors.primary),
                            ),
                            child: const Text('Savatga'),
                          ),
                        ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _qtyControl(CartNotifier notifier, CartItem item, double qty) {
    return Container(
      height: 36,
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          _btn(Icons.remove, () => notifier.updateQty(item.id, qty - product.step)),
          Text(
            qty % 1 == 0 ? qty.toInt().toString() : qty.toString(),
            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
          ),
          _btn(Icons.add, () => notifier.updateQty(item.id, qty + product.step)),
        ],
      ),
    );
  }

  Widget _btn(IconData icon, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10),
        child: Icon(icon, color: Colors.white, size: 18),
      ),
    );
  }

  Widget _placeholder() => Container(
        color: const Color(0xFFEFEFEF),
        child: const Icon(Icons.image_outlined, color: Colors.grey, size: 40),
      );
}
