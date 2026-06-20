import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../core/api_client.dart';
import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../models/models.dart';
import '../../services/api_services.dart';

/// Kuryer uchun buyurtma tafsiloti: xarita, mijoz, statusni yangilash.
class CourierOrderScreen extends StatefulWidget {
  final Order order;
  const CourierOrderScreen({super.key, required this.order});

  @override
  State<CourierOrderScreen> createState() => _CourierOrderScreenState();
}

class _CourierOrderScreenState extends State<CourierOrderScreen> {
  late Order _order = widget.order;
  bool _updating = false;

  Future<void> _updateStatus(String status) async {
    setState(() => _updating = true);
    try {
      await CourierService.updateStatus(_order.id, status);
      if (status == 'delivered') {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Buyurtma yetkazildi! ✅')));
          context.pop();
        }
        return;
      }
      // Yangilangan ma'lumotni qayta yuklash
      final updated = await OrderService.show(_order.id);
      if (mounted) setState(() => _order = updated);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(ApiClient.errorMessage(e))));
      }
    } finally {
      if (mounted) setState(() => _updating = false);
    }
  }

  Future<void> _openNavigation() async {
    final uri = Uri.parse(
        'google.navigation:q=${_order.deliveryLat},${_order.deliveryLng}&mode=d');
    final fallback = Uri.parse(
        'https://www.google.com/maps/dir/?api=1&destination=${_order.deliveryLat},${_order.deliveryLng}');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    } else {
      await launchUrl(fallback, mode: LaunchMode.externalApplication);
    }
  }

  Future<void> _call() async {
    final phone = _order.courier?.phone ?? _order.deliveryAddress;
    final uri = Uri.parse('tel:$phone');
    if (await canLaunchUrl(uri)) await launchUrl(uri);
  }

  @override
  Widget build(BuildContext context) {
    final destPos = LatLng(_order.deliveryLat, _order.deliveryLng);

    return Scaffold(
      appBar: AppBar(title: Text(_order.number)),
      body: Column(
        children: [
          SizedBox(
            height: 240,
            child: GoogleMap(
              initialCameraPosition: CameraPosition(target: destPos, zoom: 15),
              markers: {
                Marker(
                    markerId: const MarkerId('dest'),
                    position: destPos,
                    infoWindow: InfoWindow(title: _order.deliveryAddress)),
              },
            ),
          ),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _openNavigation,
                        icon: const Icon(Icons.navigation),
                        label: const Text('Navigatsiya'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _call,
                        icon: const Icon(Icons.phone),
                        label: const Text('Qo\'ng\'iroq'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                _card('Yetkazib berish manzili', [Text(_order.deliveryAddress)]),
                _card(
                  'Mahsulotlar (${_order.items.length})',
                  _order.items
                      .map((it) => Padding(
                            padding: const EdgeInsets.symmetric(vertical: 4),
                            child: Row(
                              children: [
                                Expanded(
                                    child: Text(
                                        '${it.productName} x ${it.quantity % 1 == 0 ? it.quantity.toInt() : it.quantity}')),
                                Text(formatPrice(it.total)),
                              ],
                            ),
                          ))
                      .toList(),
                ),
                _card('To\'lov', [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                          '${_order.paymentMethod.toUpperCase()} • ${_paidLabel()}'),
                      Text(formatPrice(_order.total),
                          style: const TextStyle(
                              fontWeight: FontWeight.bold, color: AppColors.primary)),
                    ],
                  ),
                ]),
              ],
            ),
          ),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: ElevatedButton(
            onPressed: _updating ? null : () => _updateStatus('delivered'),
            child: _updating
                ? const SizedBox(
                    height: 22,
                    width: 22,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : const Text('Yetkazib berildi deb belgilash'),
          ),
        ),
      ),
    );
  }

  String _paidLabel() {
    return _order.paymentStatus == 'paid' ? "To'langan" : 'Naqd olinadi';
  }

  Widget _card(String title, List<Widget> children) => Card(        margin: const EdgeInsets.only(bottom: 16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 8),
              ...children,
            ],
          ),
        ),
      );
}
