# Korzinkago Clone — Yetkazib berish platformasi 🛒

Uzbekistondagi Korzinka uslubidagi onlayn do'kon va yetkazib berish platformasining to'liq kloni.

## Tarkibi

```
Korzinkago/
├── backend/      # Laravel 11 REST API (MySQL)
└── mobile/       # Flutter ilova (User + Kuryer)
```

## Texnologiyalar

### Backend (Laravel 11 + MySQL)
- **Auth:** SMS OTP orqali (Eskiz.uz SMS provayderi) + Laravel Sanctum tokenlar
- **To'lov:** Payme Merchant API (JSON-RPC) + webhook
- **Xarita:** Google Maps (Geocoding, Distance Matrix) — yetkazib berish narxini hisoblash
- **Rollar:** `user`, `courier`, `admin`
- **Modullar:** Katalog (kategoriya/mahsulot), Savat, Manzillar, Buyurtmalar, Kuryer oqimi, To'lovlar

### Mobile (Flutter)
- **State management:** Riverpod
- **Tarmoq:** Dio
- **Xarita:** google_maps_flutter
- **Bitta ilova, ikki rol:** Foydalanuvchi (xarid) va Kuryer (yetkazib berish)

## Ishga tushirish

### Backend
```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
# .env da MySQL, ESKIZ, PAYME, GOOGLE_MAPS sozlamalarini kiriting
php artisan migrate --seed
php artisan serve
```

### Mobile
```bash
cd mobile
flutter pub get
# lib/core/constants.dart da API_BASE_URL va Google Maps kalitini kiriting
flutter run
```

## Asosiy funksiyalar
- ✅ SMS OTP bilan ro'yxatdan o'tish / kirish
- ✅ Kategoriyalar va mahsulotlar katalogi, qidiruv
- ✅ Savat (qo'shish/o'chirish/miqdor)
- ✅ Manzillar (Google Map orqali tanlash)
- ✅ Buyurtma berish va real-time status
- ✅ Payme orqali onlayn to'lov + naqd to'lov
- ✅ Kuryer: buyurtmalarni qabul qilish, xaritada yo'nalish, statusni yangilash
- ✅ Yetkazib berish narxi masofaga qarab hisoblanadi

## API hujjati
`backend/routes/api.php` faylida barcha endpointlar. Asosiylari:
- `POST /api/auth/send-otp`, `POST /api/auth/verify-otp`
- `GET /api/categories`, `GET /api/products`
- `GET/POST /api/cart`
- `POST /api/orders`, `GET /api/orders`
- `POST /api/payme/checkout`, `POST /api/payme/callback`
- `GET /api/courier/orders`, `POST /api/courier/orders/{id}/status`

> Eslatma: API kalitlari (Eskiz, Payme, Google Maps) `.env` orqali sozlanadi.
