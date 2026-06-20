import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../core/api_client.dart';
import '../../core/theme.dart';
import '../../models/models.dart';
import '../../services/api_services.dart';

/// Yetkazilgan buyurtmaga baho berish: kuryer + mahsulotlar.
class ReviewScreen extends StatefulWidget {
  final Order order;
  const ReviewScreen({super.key, required this.order});

  @override
  State<ReviewScreen> createState() => _ReviewScreenState();
}

class _ReviewScreenState extends State<ReviewScreen> {
  int _courierRating = 5;
  final _courierComment = TextEditingController();
  final Map<int, int> _productRatings = {};
  bool _submitting = false;

  Future<void> _submit() async {
    setState(() => _submitting = true);
    try {
      final products = _productRatings.entries
          .map((e) => {'product_id': e.key, 'rating': e.value})
          .toList();

      await ReviewService.submit({
        'order_id': widget.order.id,
        'courier_rating': _courierRating,
        'courier_comment': _courierComment.text,
        'products': products,
      });

      if (!mounted) return;
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Bahoyingiz uchun rahmat! 🙏')));
      context.pop(true);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(ApiClient.errorMessage(e))));
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    // Mahsulotlarni order itemdan unique product nomlari bilan ko'rsatamiz
    return Scaffold(
      appBar: AppBar(title: const Text('Baho berish')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (widget.order.courier?.name != null) ...[
            _card(
              'Kuryer: ${widget.order.courier!.name}',
              Column(
                children: [
                  _stars(_courierRating, (v) => setState(() => _courierRating = v)),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _courierComment,
                    maxLines: 2,
                    decoration: const InputDecoration(hintText: 'Izoh (ixtiyoriy)'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),
          ],
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 8),
            child: Text('Mahsulotlarni baholang',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ),
          ...widget.order.items
              .where((it) => it.productId != null)
              .map((it) => _card(
                    it.productName,
                    _stars(_productRatings[it.productId!] ?? 5,
                        (v) => setState(() => _productRatings[it.productId!] = v)),
                  )),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: ElevatedButton(
            onPressed: _submitting ? null : _submit,
            child: _submitting
                ? const SizedBox(
                    height: 22,
                    width: 22,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : const Text('Yuborish'),
          ),
        ),
      ),
    );
  }

  Widget _stars(int value, void Function(int) onChange) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: List.generate(5, (i) {
        return IconButton(
          onPressed: () => onChange(i + 1),
          icon: Icon(
            i < value ? Icons.star : Icons.star_border,
            color: AppColors.accent,
            size: 36,
          ),
        );
      }),
    );
  }

  Widget _card(String title, Widget child) => Card(
        margin: const EdgeInsets.only(bottom: 12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              child,
            ],
          ),
        ),
      );
}
