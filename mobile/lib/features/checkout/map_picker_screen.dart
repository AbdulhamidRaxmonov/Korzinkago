import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:go_router/go_router.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

import '../../core/constants.dart';
import '../../core/theme.dart';
import '../../services/api_services.dart';

/// Xaritadan manzil tanlash. Tanlangan natija {lat, lng, address} GoRouter pop bilan qaytadi.
class MapPickerScreen extends StatefulWidget {
  const MapPickerScreen({super.key});

  @override
  State<MapPickerScreen> createState() => _MapPickerScreenState();
}

class _MapPickerScreenState extends State<MapPickerScreen> {
  GoogleMapController? _controller;
  LatLng _center = const LatLng(AppConfig.defaultLat, AppConfig.defaultLng);
  String _address = '';
  bool _loadingAddress = false;

  @override
  void initState() {
    super.initState();
    _determinePosition();
  }

  Future<void> _determinePosition() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.deniedForever ||
          permission == LocationPermission.denied) {
        return;
      }
      final pos = await Geolocator.getCurrentPosition();
      setState(() => _center = LatLng(pos.latitude, pos.longitude));
      _controller?.animateCamera(CameraUpdate.newLatLng(_center));
      _resolveAddress();
    } catch (_) {}
  }

  Future<void> _resolveAddress() async {
    setState(() => _loadingAddress = true);
    try {
      final addr = await AddressService.reverseGeocode(_center.latitude, _center.longitude);
      if (mounted) setState(() => _address = addr);
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loadingAddress = false);
    }
  }

  void _confirm() {
    context.pop({
      'lat': _center.latitude,
      'lng': _center.longitude,
      'address': _address.isEmpty
          ? '${_center.latitude.toStringAsFixed(5)}, ${_center.longitude.toStringAsFixed(5)}'
          : _address,
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Manzilni tanlang')),
      body: Stack(
        alignment: Alignment.center,
        children: [
          GoogleMap(
            initialCameraPosition: CameraPosition(target: _center, zoom: 15),
            onMapCreated: (c) => _controller = c,
            myLocationEnabled: true,
            myLocationButtonEnabled: true,
            onCameraMove: (pos) => _center = pos.target,
            onCameraIdle: _resolveAddress,
          ),
          // Markaziy marker
          const Padding(
            padding: EdgeInsets.only(bottom: 40),
            child: Icon(Icons.location_on, color: AppColors.danger, size: 48),
          ),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.place_outlined, color: AppColors.primary),
                    const SizedBox(width: 8),
                    Expanded(
                      child: _loadingAddress
                          ? const Text('Manzil aniqlanmoqda...')
                          : Text(_address.isEmpty ? 'Xaritani suring' : _address,
                              maxLines: 2, overflow: TextOverflow.ellipsis),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              ElevatedButton(onPressed: _confirm, child: const Text('Tasdiqlash')),
            ],
          ),
        ),
      ),
    );
  }
}
