import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/api_client.dart';
import '../../core/theme.dart';
import '../../models/models.dart';
import '../../providers/providers.dart';
import '../../services/api_services.dart';
import '../../widgets/product_card.dart';

class CatalogScreen extends ConsumerStatefulWidget {
  const CatalogScreen({super.key});

  @override
  ConsumerState<CatalogScreen> createState() => _CatalogScreenState();
}

class _CatalogScreenState extends ConsumerState<CatalogScreen> {
  final _searchController = TextEditingController();
  List<Product> _results = [];
  bool _searching = false;

  Future<void> _search(String q) async {
    if (q.trim().isEmpty) {
      setState(() {
        _results = [];
        _searching = false;
      });
      return;
    }
    setState(() => _searching = true);
    try {
      final products = await CatalogService.products(search: q);
      if (mounted) setState(() => _results = products);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(ApiClient.errorMessage(e))));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final categories = ref.watch(categoriesProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Katalog'),
        automaticallyImplyLeading: false,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              onChanged: _search,
              decoration: InputDecoration(
                hintText: 'Mahsulot qidirish...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                          _search('');
                        },
                      )
                    : null,
              ),
            ),
          ),
          Expanded(
            child: _searching
                ? _searchResults()
                : categories.when(
                    loading: () => const Center(child: CircularProgressIndicator()),
                    error: (e, _) => Center(child: Text('Xato: $e')),
                    data: (list) => ListView.separated(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      itemCount: list.length,
                      separatorBuilder: (_, __) => const Divider(height: 1),
                      itemBuilder: (_, i) {
                        final c = list[i];
                        return ListTile(
                          leading: Container(
                            width: 44,
                            height: 44,
                            decoration: BoxDecoration(
                              color: AppColors.primary.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(Icons.category, color: AppColors.primary),
                          ),
                          title: Text(c.name),
                          trailing: const Icon(Icons.chevron_right),
                          onTap: () => context.push('/category/${c.id}?title=${c.name}'),
                        );
                      },
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _searchResults() {
    if (_results.isEmpty) {
      return const Center(child: Text('Hech narsa topilmadi'));
    }
    return GridView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _results.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
        childAspectRatio: 0.62,
      ),
      itemBuilder: (_, i) => ProductCard(product: _results[i]),
    );
  }
}
