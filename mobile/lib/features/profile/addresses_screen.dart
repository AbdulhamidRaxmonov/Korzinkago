import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme.dart';
import '../../providers/providers.dart';
import '../../services/api_services.dart';
import '../checkout/map_picker_screen.dart';

class AddressesScreen extends ConsumerWidget {
  const AddressesScreen({super.key});

  Future<void> _add(BuildContext context, WidgetRef ref) async {
    final result = await Navigator.of(context).push<Map<String, dynamic>>(
      MaterialPageRoute(builder: (_) => const MapPickerScreen()),
    );
    if (result == null) return;

    final titleController = TextEditingController(text: 'Uy');
    if (!context.mounted) return;

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(ctx).viewInsets.bottom + 16,
          left: 16,
          right: 16,
          top: 16,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(result['address'] ?? '', textAlign: TextAlign.center),
            const SizedBox(height: 12),
            TextField(
              controller: titleController,
              decoration: const InputDecoration(labelText: 'Nomi (Uy, Ish...)'),
            ),
            const SizedBox(height: 12),
            ElevatedButton(
              onPressed: () async {
                await AddressService.create({
                  'title': titleController.text,
                  'address': result['address'],
                  'lat': result['lat'],
                  'lng': result['lng'],
                });
                ref.invalidate(addressesProvider);
                if (ctx.mounted) Navigator.pop(ctx);
              },
              child: const Text('Saqlash'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final addresses = ref.watch(addressesProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Manzillarim')),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.primary,
        onPressed: () => _add(context, ref),
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Qo\'shish', style: TextStyle(color: Colors.white)),
      ),
      body: addresses.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Xato: $e')),
        data: (list) => list.isEmpty
            ? const Center(child: Text('Manzillar yo\'q'))
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: list.length,
                itemBuilder: (_, i) {
                  final a = list[i];
                  return Card(
                    child: ListTile(
                      leading: const Icon(Icons.location_on, color: AppColors.primary),
                      title: Text(a.title),
                      subtitle: Text(a.address),
                      trailing: IconButton(
                        icon: const Icon(Icons.delete_outline),
                        onPressed: () async {
                          await AddressService.delete(a.id);
                          ref.invalidate(addressesProvider);
                        },
                      ),
                    ),
                  );
                },
              ),
      ),
    );
  }
}
