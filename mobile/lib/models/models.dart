/// Barcha data modellari.

double _toDouble(dynamic v) => v == null ? 0 : (v is num ? v.toDouble() : double.tryParse(v.toString()) ?? 0);
int _toInt(dynamic v) => v == null ? 0 : (v is num ? v.toInt() : int.tryParse(v.toString()) ?? 0);

class UserModel {
  final int id;
  final String? name;
  final String phone;
  final String role;
  final String? avatar;
  final bool isOnline;

  UserModel({
    required this.id,
    this.name,
    required this.phone,
    required this.role,
    this.avatar,
    this.isOnline = false,
  });

  bool get isCourier => role == 'courier';

  factory UserModel.fromJson(Map<String, dynamic> j) => UserModel(
        id: _toInt(j['id']),
        name: j['name'],
        phone: j['phone'] ?? '',
        role: j['role'] ?? 'user',
        avatar: j['avatar'],
        isOnline: j['is_online'] == true || j['is_online'] == 1,
      );
}

class Category {
  final int id;
  final String name;
  final String? icon;
  final String? image;
  final List<Category> children;

  Category({
    required this.id,
    required this.name,
    this.icon,
    this.image,
    this.children = const [],
  });

  factory Category.fromJson(Map<String, dynamic> j) => Category(
        id: _toInt(j['id']),
        name: j['name'] ?? '',
        icon: j['icon'],
        image: j['image'],
        children: (j['children'] as List?)
                ?.map((e) => Category.fromJson(e))
                .toList() ??
            [],
      );
}

class Product {
  final int id;
  final int categoryId;
  final String name;
  final String? description;
  final String? image;
  final double price;
  final double? oldPrice;
  final String unit;
  final double step;
  final int stock;
  final bool hasDiscount;
  final int discountPercent;
  final double rating;
  final int reviewsCount;

  Product({
    required this.id,
    required this.categoryId,
    required this.name,
    this.description,
    this.image,
    required this.price,
    this.oldPrice,
    this.unit = 'dona',
    this.step = 1,
    this.stock = 0,
    this.hasDiscount = false,
    this.discountPercent = 0,
    this.rating = 0,
    this.reviewsCount = 0,
  });

  bool get inStock => stock > 0;

  factory Product.fromJson(Map<String, dynamic> j) => Product(
        id: _toInt(j['id']),
        categoryId: _toInt(j['category_id']),
        name: j['name'] ?? '',
        description: j['description'],
        image: j['image'],
        price: _toDouble(j['price']),
        oldPrice: j['old_price'] == null ? null : _toDouble(j['old_price']),
        unit: j['unit'] ?? 'dona',
        step: _toDouble(j['step']) == 0 ? 1 : _toDouble(j['step']),
        stock: _toInt(j['stock']),
        hasDiscount: j['has_discount'] == true,
        discountPercent: _toInt(j['discount_percent']),
        rating: _toDouble(j['rating']),
        reviewsCount: _toInt(j['reviews_count']),
      );
}

class CartItem {
  final int id;
  final double quantity;
  final Product? product;

  CartItem({required this.id, required this.quantity, this.product});

  double get total => (product?.price ?? 0) * quantity;

  factory CartItem.fromJson(Map<String, dynamic> j) => CartItem(
        id: _toInt(j['id']),
        quantity: _toDouble(j['quantity']),
        product: j['product'] != null ? Product.fromJson(j['product']) : null,
      );
}

class Address {
  final int id;
  final String title;
  final String address;
  final double lat;
  final double lng;
  final String? entrance;
  final String? floor;
  final String? apartment;
  final bool isDefault;

  Address({
    required this.id,
    required this.title,
    required this.address,
    required this.lat,
    required this.lng,
    this.entrance,
    this.floor,
    this.apartment,
    this.isDefault = false,
  });

  factory Address.fromJson(Map<String, dynamic> j) => Address(
        id: _toInt(j['id']),
        title: j['title'] ?? 'Manzil',
        address: j['address'] ?? '',
        lat: _toDouble(j['lat']),
        lng: _toDouble(j['lng']),
        entrance: j['entrance'],
        floor: j['floor'],
        apartment: j['apartment'],
        isDefault: j['is_default'] == true || j['is_default'] == 1,
      );
}

class OrderItem {
  final int? productId;
  final String productName;
  final double price;
  final double quantity;
  final String unit;
  final double total;

  OrderItem({
    this.productId,
    required this.productName,
    required this.price,
    required this.quantity,
    required this.unit,
    required this.total,
  });

  factory OrderItem.fromJson(Map<String, dynamic> j) => OrderItem(
        productId: j['product_id'] == null ? null : _toInt(j['product_id']),
        productName: j['product_name'] ?? '',
        price: _toDouble(j['price']),
        quantity: _toDouble(j['quantity']),
        unit: j['unit'] ?? 'dona',
        total: _toDouble(j['total']),
      );
}

class CourierInfo {
  final String? name;
  final String? phone;
  final double? lat;
  final double? lng;

  CourierInfo({this.name, this.phone, this.lat, this.lng});

  factory CourierInfo.fromJson(Map<String, dynamic> j) => CourierInfo(
        name: j['name'],
        phone: j['phone'],
        lat: j['current_lat'] == null ? null : _toDouble(j['current_lat']),
        lng: j['current_lng'] == null ? null : _toDouble(j['current_lng']),
      );
}

class Order {
  final int id;
  final String number;
  final String status;
  final String paymentMethod;
  final String paymentStatus;
  final String deliveryAddress;
  final double deliveryLat;
  final double deliveryLng;
  final double itemsTotal;
  final double deliveryFee;
  final double total;
  final String? createdAt;
  final List<OrderItem> items;
  final CourierInfo? courier;

  Order({
    required this.id,
    required this.number,
    required this.status,
    required this.paymentMethod,
    required this.paymentStatus,
    required this.deliveryAddress,
    required this.deliveryLat,
    required this.deliveryLng,
    required this.itemsTotal,
    required this.deliveryFee,
    required this.total,
    this.createdAt,
    this.items = const [],
    this.courier,
  });

  factory Order.fromJson(Map<String, dynamic> j) => Order(
        id: _toInt(j['id']),
        number: j['number'] ?? '',
        status: j['status'] ?? 'new',
        paymentMethod: j['payment_method'] ?? 'cash',
        paymentStatus: j['payment_status'] ?? 'pending',
        deliveryAddress: j['delivery_address'] ?? '',
        deliveryLat: _toDouble(j['delivery_lat']),
        deliveryLng: _toDouble(j['delivery_lng']),
        itemsTotal: _toDouble(j['items_total']),
        deliveryFee: _toDouble(j['delivery_fee']),
        total: _toDouble(j['total']),
        createdAt: j['created_at'],
        items: (j['items'] as List?)?.map((e) => OrderItem.fromJson(e)).toList() ?? [],
        courier: j['courier'] != null ? CourierInfo.fromJson(j['courier']) : null,
      );
}
