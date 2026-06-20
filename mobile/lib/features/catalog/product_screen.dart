import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../models/models.dart';
import '../../providers/providers.dart';

class ProductScreen extends ConsumerWidget {
  final Product product;
  const ProductScreen({super.key, required this.product});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    ref.watch(cartProvider);
    final notifier = ref.read(cartProvider.notifier);
    final qty = notifier.quantityOf(product.id);
    final item = notifier.itemOf(product.id);

    return Scaffold(
      appBar: AppBar(title: Text(product.name, overflow: TextOverflow.ellipsis)),
      body: ListView(
        children: [
          AspectRatio(
            aspectRatio: 1,
            child: product.image != null
                ? CachedNetworkImage(imageUrl: product.image!, fit: BoxFit.cover)
                : Container(
                    color: const Color(0xFFEFEFEF),
                    child: const Icon(Icons.image_outlined, size: 80, color: Colors.grey),
                  ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(product.name,
                    style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Text(formatPrice(product.price),
                        style: const TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                            color: AppColors.primary)),
                    const SizedBox(width: 10),
                    if (product.hasDiscount)
                      Text(formatPrice(product.oldPrice!),
                          style: const TextStyle(
                            color: AppColors.textSecondary,
                            decoration: TextDecoration.lineThrough,
                          )),
                  ],
                ),
                const SizedBox(height: 4),
                Text('1 ${product.unit}',
                    style: const TextStyle(color: AppColors.textSecondary)),
                const SizedBox(height: 16),
                if (product.description != null) ...[
                  const Text('Tavsif',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 6),
                  Text(product.description!,
                      style: const TextStyle(color: AppColors.textSecondary, height: 1.5)),
                ],
              ],
            ),
          ),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: qty > 0 && item != null
              ? Row(
                  children: [
                    _circleBtn(Icons.remove,
                        () => notifier.updateQty(item.id, qty - product.step)),
                    Expanded(
                      child: Center(
                        child: Text(
                          '${qty % 1 == 0 ? qty.toInt() : qty} ${product.unit}',
                          style: const TextStyle(
                              fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                    _circleBtn(Icons.add,
                        () => notifier.updateQty(item.id, qty + product.step)),
                  ],
                )
              : ElevatedButton(
                  onPressed: () => notifier.add(product.id),
                  child: const Text('Savatga qo\'shish'),
                ),
        ),
      ),
    );
  }

  Widget _circleBtn(IconData icon, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(30),
      child: Container(
        width: 54,
        height: 54,
        decoration: BoxDecoration(
          color: AppColors.primary,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Icon(icon, color: Colors.white),
      ),
    );
  }
}
