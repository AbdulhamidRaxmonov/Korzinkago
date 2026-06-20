import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:pin_code_fields/pin_code_fields.dart';

import '../../core/api_client.dart';
import '../../core/theme.dart';
import '../../providers/providers.dart';
import '../../services/api_services.dart';

class OtpScreen extends ConsumerStatefulWidget {
  final String phone;
  const OtpScreen({super.key, required this.phone});

  @override
  ConsumerState<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends ConsumerState<OtpScreen> {
  final _codeController = TextEditingController();
  bool _loading = false;

  Future<void> _verify() async {
    if (_codeController.text.length < 6) return;
    setState(() => _loading = true);
    try {
      final user = await AuthService.verifyOtp(widget.phone, _codeController.text);
      ref.read(authProvider.notifier).setUser(user);
      if (!mounted) return;
      context.go(user.isCourier ? '/courier' : '/home');
    } catch (e) {
      _show(ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _resend() async {
    try {
      final res = await AuthService.sendOtp(widget.phone);
      _show(res['code'] != null ? 'Test kod: ${res['code']}' : 'Kod qayta yuborildi');
    } catch (e) {
      _show(ApiClient.errorMessage(e));
    }
  }

  void _show(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Tasdiqlash kodi',
                style: TextStyle(fontSize: 26, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Text(
              '+${widget.phone} raqamiga yuborilgan 6 xonali kodni kiriting',
              style: const TextStyle(color: AppColors.textSecondary),
            ),
            const SizedBox(height: 32),
            PinCodeTextField(
              appContext: context,
              length: 6,
              controller: _codeController,
              keyboardType: TextInputType.number,
              autoFocus: true,
              animationType: AnimationType.fade,
              pinTheme: PinTheme(
                shape: PinCodeFieldShape.box,
                borderRadius: BorderRadius.circular(12),
                fieldHeight: 56,
                fieldWidth: 48,
                activeColor: AppColors.primary,
                selectedColor: AppColors.primary,
                inactiveColor: const Color(0xFFE5E7EB),
              ),
              onCompleted: (_) => _verify(),
              onChanged: (_) {},
            ),
            const SizedBox(height: 16),
            Center(
              child: TextButton(
                onPressed: _resend,
                child: const Text('Kodni qayta yuborish'),
              ),
            ),
            const Spacer(),
            ElevatedButton(
              onPressed: _loading ? null : _verify,
              child: _loading
                  ? const SizedBox(
                      height: 22,
                      width: 22,
                      child: CircularProgressIndicator(
                          color: Colors.white, strokeWidth: 2))
                  : const Text('Tasdiqlash'),
            ),
          ],
        ),
      ),
    );
  }
}
