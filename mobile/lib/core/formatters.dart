import 'package:intl/intl.dart';

/// Narxni "12 000 so'm" ko'rinishida formatlash.
String formatPrice(num value) {
  final formatter = NumberFormat('#,###', 'ru');
  return "${formatter.format(value).replaceAll(',', ' ')} so'm";
}

/// Sanani formatlash.
String formatDate(String? iso) {
  if (iso == null) return '';
  try {
    final dt = DateTime.parse(iso).toLocal();
    return DateFormat('dd.MM.yyyy HH:mm').format(dt);
  } catch (_) {
    return iso;
  }
}

/// Buyurtma statusini o'zbekchaga o'girish.
String orderStatusLabel(String status) {
  switch (status) {
    case 'new':
      return 'Yangi';
    case 'accepted':
      return 'Qabul qilindi';
    case 'assembling':
      return 'Yig\'ilmoqda';
    case 'ready':
      return 'Tayyor';
    case 'on_way':
      return 'Yo\'lda';
    case 'delivered':
      return 'Yetkazildi';
    case 'cancelled':
      return 'Bekor qilindi';
    default:
      return status;
  }
}


/// To'lov holatini matnga o'girish.
String paymentLabel(String status) {
  switch (status) {
    case 'paid':
      return "To'langan";
    case 'failed':
      return "To'lanmadi";
    case 'refunded':
      return 'Qaytarildi';
    default:
      return 'Kutilmoqda';
  }
}
