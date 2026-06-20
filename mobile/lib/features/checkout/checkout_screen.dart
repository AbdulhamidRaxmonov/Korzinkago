import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/api_client.dart';
import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../providers/providers.dart';
import '../../services/api_services.dart';
import 'payme_webview.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  double? _lat;
  double? _lng;
  String _address = '';
  final _commentController = TextEditingController();
  String _paymentMethod = 'cash';

  double _deliveryFee = 0;
  double _distance = 0;
  bool _calculating = false;
  bool _placing = false;

  Future<void> _pickOnMap() async {
    final result = await context.push<Map<String, dynamic>>('/map-picker');
    if (result != null) {
      setState(() {
        _lat = result['lat'];
        _lng = result['lng'];
        _address = result['address'];
      });
      _calculate();
    }
  }

  Future<void> _calculate() async {
    if (_lat == null || _lng == null) return;
    setState(() => _calculating = true);
    try {
      final res = await OrderService.calculate(_lat!, _lng!);
      setState(() {
        _deliveryFee = (res['delivery_fee'] as num).toDouble();
        _distance = (res['distance_km'] as num?)?.toDouble() ?? 0;
      });
    } catch (_) {
    } finally {
      if (mounted) setState(() => _calculating = false);
    }
  }

  Future<void> _placeOrder() async {
    if (_lat == null || _lng == null) {
      _show('Yetkazib berish manzilini tanlang');
      return;
    }
    setState(() => _placing = true);
    try {
      final res = await OrderService.create({
        'delivery_address': _address,
        'delivery_lat': _lat,
        'delivery_lng': _lng,
        'comment': _commentController.text,
        'payment_method': _paymentMethod,
      });

      final orderId = res['order']['id'] as int;
      await ref.read(cartProvider.notifier).load();

      // Payme to'lov bo'lsa, checkout sahifasini ochamiz
      if (res['needs_payment'] == true) {
        final url = await OrderService.paymeCheckout(orderId);
        if (!mounted) return;
        await Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => PaymeWebView(url: url)),
        );
      }

      if (!mounted) return;
      context.go('/order/$orderId');
    } catch (e) {
      _show(ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _placing = false);
    }
  }

  void _show(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  @override
  Widget build(BuildContext context) {
    final notifier = ref.read(cartProvider.notifier);
    final itemsTotal = notifier.total;
    final total = itemsTotal + _deliveryFee;

    return Scaffold(
      appBar: AppBar(title: const Text('Rasmiylashtirish')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _section('Yetkazib berish manzili'),
          Card(
            child: ListTile(
              leading: const Icon(Icons.location_on, color: AppColors.primary),
              title: Text(_address.isEmpty ? 'Manzilni tanlang' : _address),
              subtitle: _distance > 0 ? Text('${_distance.toStringAsFixed(1)} km') : null,
              trailing: const Icon(Icons.chevron_right),
              onTap: _pickOnMap,
            ),
          ),
          const SizedBox(height: 16),
          _section('Izoh (ixtiyoriy)'),
          TextField(
            controller: _commentController,
            maxLines: 2,
            decoration: const InputDecoration(hintText: 'Kuryer uchun izoh...'),
          ),
          const SizedBox(height: 16),
          _section('To\'lov usuli'),
          _paymentTile('cash', 'Naqd pul', Icons.payments_outlined),
          _paymentTile('payme', 'Payme', Icons.account_balance_wallet_outlined),
          _paymentTile('click', 'Click', Icons.credit_card),
          const SizedBox(height: 16),
          _summaryRow('Mahsulotlar', formatPrice(itemsTotal)),
          _summaryRow(
            'Yetkazib berish',
            _calculating
                ? '...'
                : (_deliveryFee == 0 ? 'Bepul' : formatPrice(_deliveryFee)),
          ),
          const Divider(),
          _summaryRow('Jami', formatPrice(total), bold: true),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: ElevatedButton(
            onPressed: _placing ? null : _placeOrder,
            child: _placing
                ? const SizedBox(
                    height: 22,
                    width: 22,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : Text('Buyurtma berish • ${formatPrice(total)}'),
          ),
        ),
      ),
    );
  }

  Widget _section(String t) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Text(t, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
      );

  Widget _paymentTile(String value, String title, IconData icon) {
    return Card(
      child: RadioListTile<String>(
        value: value,
        groupValue: _paymentMethod,
        onChanged: (v) => setState(() => _paymentMethod = v!),
        title: Text(title),
        secondary: Icon(icon),
        activeColor: AppColors.primary,
      ),
    );
  }

  Widget _summaryRow(String label, String value, {bool bold = false}) {
    final style = TextStyle(
      fontSize: bold ? 18 : 15,
      fontWeight: bold ? FontWeight.bold : FontWeight.normal,
      color: bold ? AppColors.textPrimary : AppColors.textSecondary,
    );
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [Text(label, style: style), Text(value, style: style)],
      ),
    );
  }
}
