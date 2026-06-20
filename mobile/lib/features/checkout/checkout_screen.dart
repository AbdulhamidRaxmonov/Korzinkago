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

  final _promoController = TextEditingController();
  double _discount = 0;
  String? _promoCode;
  bool _applyingPromo = false;
  String? _promoMessage;

  Future<void> _applyPromo() async {
    final code = _promoController.text.trim();
    if (code.isEmpty) return;
    setState(() => _applyingPromo = true);
    try {
      final res = await OrderService.applyPromo(code);
      setState(() {
        _discount = (res['discount'] as num).toDouble();
        _promoCode = res['code'];
        _promoMessage = res['message'];
      });
    } catch (e) {
      setState(() {
        _discount = 0;
        _promoCode = null;
        _promoMessage = ApiClient.errorMessage(e);
      });
    } finally {
      if (mounted) setState(() => _applyingPromo = false);
    }
  }

  void _clearPromo() {
    setState(() {
      _discount = 0;
      _promoCode = null;
      _promoMessage = null;
      _promoController.clear();
    });
  }

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
        if (_promoCode != null) 'promo_code': _promoCode,
      });

      final orderId = res['order']['id'] as int;
      await ref.read(cartProvider.notifier).load();

      // Onlayn to'lov bo'lsa, checkout sahifasini ochamiz
      if (res['needs_payment'] == true) {
        final method = res['payment_method'] ?? _paymentMethod;
        final url = method == 'click'
            ? await OrderService.clickCheckout(orderId)
            : await OrderService.paymeCheckout(orderId);
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
    final total = (itemsTotal - _discount).clamp(0, double.infinity) + _deliveryFee;

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
          _section('Promokod'),
          _promoField(),
          const SizedBox(height: 16),
          _summaryRow('Mahsulotlar', formatPrice(itemsTotal)),
          if (_discount > 0)
            _summaryRow('Chegirma ($_promoCode)', '- ${formatPrice(_discount)}',
                color: AppColors.primary),
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

  Widget _promoField() {
    final applied = _promoCode != null && _discount > 0;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: TextField(
                controller: _promoController,
                enabled: !applied,
                textCapitalization: TextCapitalization.characters,
                decoration: const InputDecoration(hintText: 'Promokodni kiriting'),
              ),
            ),
            const SizedBox(width: 8),
            applied
                ? IconButton(
                    onPressed: _clearPromo,
                    icon: const Icon(Icons.close, color: AppColors.danger),
                  )
                : SizedBox(
                    height: 54,
                    child: ElevatedButton(
                      onPressed: _applyingPromo ? null : _applyPromo,
                      style: ElevatedButton.styleFrom(
                          minimumSize: const Size(90, 54)),
                      child: _applyingPromo
                          ? const SizedBox(
                              height: 18,
                              width: 18,
                              child: CircularProgressIndicator(
                                  color: Colors.white, strokeWidth: 2))
                          : const Text('Qo\'llash'),
                    ),
                  ),
          ],
        ),
        if (_promoMessage != null)
          Padding(
            padding: const EdgeInsets.only(top: 6, left: 4),
            child: Text(
              _promoMessage!,
              style: TextStyle(
                fontSize: 12,
                color: applied ? AppColors.primary : AppColors.danger,
              ),
            ),
          ),
      ],
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

  Widget _summaryRow(String label, String value, {bool bold = false, Color? color}) {
    final style = TextStyle(
      fontSize: bold ? 18 : 15,
      fontWeight: bold ? FontWeight.bold : FontWeight.normal,
      color: color ?? (bold ? AppColors.textPrimary : AppColors.textSecondary),
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
