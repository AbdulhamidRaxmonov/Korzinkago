import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:webview_flutter/webview_flutter.dart';

/// Payme to'lov sahifasini WebView orqali ochish.
class PaymeWebView extends StatefulWidget {
  final String url;
  final String title;
  const PaymeWebView({super.key, required this.url, this.title = 'To\'lov'});

  @override
  State<PaymeWebView> createState() => _PaymeWebViewState();
}

class _PaymeWebViewState extends State<PaymeWebView> {
  late final WebViewController _controller;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageFinished: (_) => setState(() => _loading = false),
          onNavigationRequest: (req) {
            // To'lov tugagach return_url ga qaytsa, ekranni yopamiz
            if (req.url.contains('payment-success') ||
                req.url.contains('return')) {
              context.pop(true);
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.url));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.title)),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (_loading) const Center(child: CircularProgressIndicator()),
        ],
      ),
    );
  }
}
