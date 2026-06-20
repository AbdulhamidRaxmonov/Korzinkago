import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/theme.dart';
import 'router/app_router.dart';
import 'services/push_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await PushService.init();
  runApp(const ProviderScope(child: KorzinkagoApp()));
}

class KorzinkagoApp extends ConsumerWidget {
  const KorzinkagoApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: 'Korzinkago',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      routerConfig: router,
    );
  }
}
