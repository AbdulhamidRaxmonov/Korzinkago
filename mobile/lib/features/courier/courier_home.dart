import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:go_router/go_router.dart';

import '../../core/api_client.dart';
import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../models/models.dart';
import '../../providers/providers.dart';
import '../../services/api_services.dart';

class CourierHome extends ConsumerStatefulWidget {
  const CourierHome({super.key});

  @override
  ConsumerState<CourierHome> createState() => _CourierHomeState();
}

class _CourierHomeState extends ConsumerState<CourierHome>
    with SingleTickerProviderStateMixin {
  late final TabController _tab = TabController(length: 2, vsync: this);
  bool _online = false;
  bool _loading = true;

  List<Order> _available = [];
  List<Order> _active = [];

  Timer? _locationTimer;
  Timer? _refreshTimer;

  @override
  void initState() {
    super.initState();
    _online = ref.read(authProvider).user?.isOnline ?? false;
    _load();
    // Buyurtmalarni va lokatsiyani davriy yangilab turish
    _refreshTimer = Timer.periodic(const Duration(seconds: 20), (_) => _load(silent: true));
    _locationTimer = Timer.periodic(const Duration(seconds: 30), (_) => _pushLocation());
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _locationTimer?.cancel();
    _tab.dispose();
    super.dispose();
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) setState(() => _loading = true);
    try {
      final available = await CourierService.available();
      final mine = await CourierService.myOrders();
      if (mounted) {
        setState(() {
          _available = available;
          _active = mine['active'] as List<Order>;
        });
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _pushLocation() async {
    if (!_online) return;
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.deniedForever) return;
      final pos = await Geolocator.getCurrentPosition();
      await CourierService.updateLocation(pos.latitude, pos.longitude);
    } catch (_) {}
  }

  Future<void> _toggleOnline(bool value) async {
    setState(() => _online = value);
    try {
      await CourierService.toggleOnline(value);
      if (value) _pushLocation();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(ApiClient.errorMessage(e))));
      }
    }
  }

  Future<void> _accept(Order o) async {
    try {
      await CourierService.accept(o.id);
      _load();
      _tab.animateTo(1);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(ApiClient.errorMessage(e))));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authProvider).user;

    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: false,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(user?.name ?? 'Kuryer', style: const TextStyle(fontSize: 16)),
            Text(_online ? 'Onlayn' : 'Oflayn',
                style: TextStyle(
                    fontSize: 12,
                    color: _online ? AppColors.primary : AppColors.textSecondary)),
          ],
        ),
        actions: [
          Switch(
            value: _online,
            activeColor: AppColors.primary,
            onChanged: _toggleOnline,
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await ref.read(authProvider.notifier).logout();
              if (context.mounted) context.go('/phone');
            },
          ),
        ],
        bottom: TabBar(
          controller: _tab,
          labelColor: AppColors.primary,
          indicatorColor: AppColors.primary,
          tabs: [
            Tab(text: 'Yangi (${_available.length})'),
            Tab(text: 'Mening (${_active.length})'),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tab,
              children: [
                _orderList(_available, isAvailable: true),
                _orderList(_active, isAvailable: false),
              ],
            ),
    );
  }

  Widget _orderList(List<Order> orders, {required bool isAvailable}) {
    if (orders.isEmpty) {
      return Center(
        child: Text(isAvailable ? 'Yangi buyurtmalar yo\'q' : 'Faol buyurtmalar yo\'q',
            style: const TextStyle(color: AppColors.textSecondary)),
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: orders.length,
        separatorBuilder: (_, __) => const SizedBox(height: 12),
        itemBuilder: (_, i) {
          final o = orders[i];
          return Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(o.number, style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(formatPrice(o.total),
                          style: const TextStyle(
                              fontWeight: FontWeight.bold, color: AppColors.primary)),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.location_on, size: 16, color: AppColors.danger),
                      const SizedBox(width: 4),
                      Expanded(
                          child: Text(o.deliveryAddress,
                              maxLines: 2, overflow: TextOverflow.ellipsis)),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text('${o.items.length} ta mahsulot • ${o.paymentMethod.toUpperCase()}',
                      style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                  const SizedBox(height: 12),
                  isAvailable
                      ? ElevatedButton(
                          onPressed: () => _accept(o),
                          style: ElevatedButton.styleFrom(
                              minimumSize: const Size.fromHeight(44)),
                          child: const Text('Qabul qilish'),
                        )
                      : OutlinedButton(
                          onPressed: () async {
                            await context.push('/courier/order', extra: o);
                            _load();
                          },
                          style: OutlinedButton.styleFrom(
                            minimumSize: const Size.fromHeight(44),
                            foregroundColor: AppColors.primary,
                            side: const BorderSide(color: AppColors.primary),
                          ),
                          child: const Text('Ochish'),
                        ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
