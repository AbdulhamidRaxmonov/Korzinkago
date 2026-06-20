import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../providers/providers.dart';

class OrdersScreen extends ConsumerWidget {
  const OrdersScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final orders = ref.watch(ordersProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Buyurtmalar'),
        automaticallyImplyLeading: false,
      ),
      body: orders.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Xato: $e')),
        data: (list) => list.isEmpty
            ? const Center(child: Text('Hali buyurtmalar yo\'q'))
            : RefreshIndicator(
                onRefresh: () async => ref.refresh(ordersProvider),
                child: ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: list.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 12),
                  itemBuilder: (_, i) {
                    final o = list[i];
                    return Card(
                      child: ListTile(
                        onTap: () => context.push('/order/${o.id}'),
                        title: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(o.number,
                                style: const TextStyle(fontWeight: FontWeight.bold)),
                            _statusChip(o.status),
                          ],
                        ),
                        subtitle: Padding(
                          padding: const EdgeInsets.only(top: 6),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('${o.items.length} ta mahsulot'),
                              Text(formatDate(o.createdAt),
                                  style: const TextStyle(fontSize: 12)),
                            ],
                          ),
                        ),
                        trailing: Text(formatPrice(o.total),
                            style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                color: AppColors.primary)),
                      ),
                    );
                  },
                ),
              ),
      ),
    );
  }

  Widget _statusChip(String status) {
    Color color = AppColors.primary;
    if (status == 'cancelled') color = AppColors.danger;
    if (status == 'delivered') color = Colors.green;
    if (status == 'on_way') color = Colors.orange;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(orderStatusLabel(status),
          style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w600)),
    );
  }
}
