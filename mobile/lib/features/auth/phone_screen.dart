import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/api_client.dart';
import '../../core/theme.dart';
import '../../services/api_services.dart';

class PhoneScreen extends ConsumerStatefulWidget {
  const PhoneScreen({super.key});

  @override
  ConsumerState<PhoneScreen> createState() => _PhoneScreenState();
}

class _PhoneScreenState extends ConsumerState<PhoneScreen> {
  final _controller = TextEditingController();
  bool _loading = false;

  String get _phone => '998${_controller.text.replaceAll(RegExp(r'\D'), '')}';

  Future<void> _submit() async {
    if (_controller.text.replaceAll(RegExp(r'\D'), '').length < 9) {
      _show('Telefon raqamni to\'liq kiriting');
      return;
    }
    setState(() => _loading = true);
    try {
      final res = await AuthService.sendOtp(_phone);
      if (!mounted) return;
      // Test rejimida kod javobda keladi
      if (res['code'] != null) {
        _show('Test kod: ${res['code']}');
      }
      context.push('/otp?phone=$_phone');
    } catch (e) {
      _show(ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _show(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 40),
              const Icon(Icons.shopping_basket_rounded,
                  size: 64, color: AppColors.primary),
              const SizedBox(height: 24),
              const Text('Xush kelibsiz!',
                  style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              const Text(
                'Davom etish uchun telefon raqamingizni kiriting',
                style: TextStyle(color: AppColors.textSecondary, fontSize: 15),
              ),
              const SizedBox(height: 32),
              TextField(
                controller: _controller,
                keyboardType: TextInputType.phone,
                maxLength: 12,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                decoration: const InputDecoration(
                  prefixText: '+998 ',
                  prefixStyle: TextStyle(
                      fontSize: 18,
                      color: AppColors.textPrimary,
                      fontWeight: FontWeight.w600),
                  hintText: '90 123 45 67',
                  counterText: '',
                ),
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
              ),
              const Spacer(),
              ElevatedButton(
                onPressed: _loading ? null : _submit,
                child: _loading
                    ? const SizedBox(
                        height: 22,
                        width: 22,
                        child: CircularProgressIndicator(
                            color: Colors.white, strokeWidth: 2),
                      )
                    : const Text('Kodni olish'),
              ),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }
}
