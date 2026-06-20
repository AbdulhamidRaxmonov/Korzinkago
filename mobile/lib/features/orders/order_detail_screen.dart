import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../models/models.dart';
import '../../services/api_services.dart';

/// Buyurtma tafsiloti + real-time kuzatuv (xarita).
class OrderDetailScreen extends ConsumerStatefulWidget {
  final int orderId;
  const OrderDetailScreen({super.key, required this.orderId});

  @override
  ConsumerState<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends ConsumerState<OrderDetailScreen> {
  Order? _order;
  bool _loading = true;
  Timer? _timer;
  GoogleMapController? _mapController;

  @override
  void initState() {
    super.initState();
    _load();
    // Har 15 sekundda yangilab turish (kuryer lokatsiyasi uchun)
    _timer = Timer.periodic(const Duration(seconds: 15), (_) => _load(silent: true));
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) setState(() => _loading = true);
    try {
      final order = await OrderService.show(widget.orderId);
      if (mounted) setState(() => _order = order);
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  final _steps = ['new', 'accepted', 'assembling', 'ready', 'on_way', 'delivered'];

  @override
  Widget build(BuildContext context) {
    final o = _order;
    return Scaffold(
      appBar: AppBar(title: Text(o?.number ?? 'Buyurtma')),
      body: _loading || o == null
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  if (o.status != 'cancelled') _statusTimeline(o),
                  if (o.courier?.lat != null) _trackingMap(o),
                  const SizedBox(height: 16),
                  _card('Manzil', [
                    Text(o.deliveryAddress),
                  ]),
                  if (o.courier != null && o.courier!.name != null)
                    _card('Kuryer', [
                      Row(
                        children: [
                          const Icon(Icons.delivery_dining, color: AppColors.primary),
                          const SizedBox(width: 8),
                          Text(o.courier!.name!),
                          const Spacer(),
                          if (o.courier!.phone != null)
                            Text(o.courier!.phone!,
                                style: const TextStyle(color: AppColors.primary)),
                        ],
                      ),
                    ]),
                  _card(
                    'Mahsulotlar',
                    o.items
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
                    _row('Mahsulotlar', formatPrice(o.itemsTotal)),
                    _row('Yetkazib berish',
                        o.deliveryFee == 0 ? 'Bepul' : formatPrice(o.deliveryFee)),
                    const Divider(),
                    _row('Jami', formatPrice(o.total), bold: true),
                    const SizedBox(height: 8),
                    Text(
                        'To\'lov: ${o.paymentMethod.toUpperCase()} • ${paymentLabel(o.paymentStatus)}',
                        style: const TextStyle(color: AppColors.textSecondary)),
                  ]),
                  if (['new', 'accepted', 'assembling'].contains(o.status))
                    OutlinedButton(
                      onPressed: () async {
                        await OrderService.cancel(o.id);
                        _load();
                      },
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.danger,
                        side: const BorderSide(color: AppColors.danger),
                        minimumSize: const Size.fromHeight(48),
                      ),
                      child: const Text('Buyurtmani bekor qilish'),
                    ),
                ],
              ),
            ),
    );
  }

  Widget _statusTimeline(Order o) {
    final currentIndex = _steps.indexOf(o.status);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: List.generate(_steps.length, (i) {
            final done = i <= currentIndex;
            final isLast = i == _steps.length - 1;
            return Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Column(
                  children: [
                    Icon(done ? Icons.check_circle : Icons.circle_outlined,
                        color: done ? AppColors.primary : Colors.grey, size: 22),
                    if (!isLast)
                      Container(
                        width: 2,
                        height: 24,
                        color: done ? AppColors.primary : Colors.grey.shade300,
                      ),
                  ],
                ),
                const SizedBox(width: 12),
                Padding(
                  padding: const EdgeInsets.only(top: 2),
                  child: Text(orderStatusLabel(_steps[i]),
                      style: TextStyle(
                          fontWeight: done ? FontWeight.bold : FontWeight.normal,
                          color: done ? AppColors.textPrimary : AppColors.textSecondary)),
                ),
              ],
            );
          }),
        ),
      ),
    );
  }

  Widget _trackingMap(Order o) {
    final courierPos = LatLng(o.courier!.lat!, o.courier!.lng!);
    final destPos = LatLng(o.deliveryLat, o.deliveryLng);
    return Padding(
      padding: const EdgeInsets.only(top: 16),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: SizedBox(
          height: 220,
          child: GoogleMap(
            initialCameraPosition: CameraPosition(target: courierPos, zoom: 13),
            onMapCreated: (c) => _mapController = c,
            markers: {
              Marker(
                  markerId: const MarkerId('courier'),
                  position: courierPos,
                  infoWindow: const InfoWindow(title: 'Kuryer')),
              Marker(
                  markerId: const MarkerId('dest'),
                  position: destPos,
                  infoWindow: const InfoWindow(title: 'Manzil')),
            },
          ),
        ),
      ),
    );
  }

  Widget _card(String title, List<Widget> children) => Card(
        margin: const EdgeInsets.only(bottom: 16),
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

  Widget _row(String l, String v, {bool bold = false}) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 2),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(l, style: TextStyle(fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
            Text(v, style: TextStyle(fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
          ],
        ),
      );
}
