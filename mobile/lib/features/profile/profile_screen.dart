import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/theme.dart';
import '../../providers/providers.dart';
import 'addresses_screen.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).user;

    return Scaffold(
      appBar: AppBar(title: const Text('Profil'), automaticallyImplyLeading: false),
      body: ListView(
        children: [
          const SizedBox(height: 16),
          Center(
            child: CircleAvatar(
              radius: 44,
              backgroundColor: AppColors.primary.withOpacity(0.15),
              child: const Icon(Icons.person, size: 44, color: AppColors.primary),
            ),
          ),
          const SizedBox(height: 12),
          Center(
            child: Text(user?.name ?? 'Foydalanuvchi',
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
          ),
          Center(
            child: Text('+${user?.phone ?? ''}',
                style: const TextStyle(color: AppColors.textSecondary)),
          ),
          const SizedBox(height: 24),
          _tile(Icons.location_on_outlined, 'Mening manzillarim', () {
            Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const AddressesScreen()));
          }),
          _tile(Icons.favorite_border, 'Sevimlilar', () => context.push('/favorites')),
          _tile(Icons.receipt_long_outlined, 'Buyurtmalar tarixi',
              () => context.go('/orders')),
          _tile(Icons.headset_mic_outlined, 'Qo\'llab-quvvatlash', () {}),
          _tile(Icons.info_outline, 'Ilova haqida', () {}),
          const Divider(),
          _tile(Icons.logout, 'Chiqish', () async {
            await ref.read(authProvider.notifier).logout();
            if (context.mounted) context.go('/phone');
          }, color: AppColors.danger),
        ],
      ),
    );
  }

  Widget _tile(IconData icon, String title, VoidCallback onTap, {Color? color}) {
    return ListTile(
      leading: Icon(icon, color: color ?? AppColors.textPrimary),
      title: Text(title, style: TextStyle(color: color)),
      trailing: const Icon(Icons.chevron_right),
      onTap: onTap,
    );
  }
}
