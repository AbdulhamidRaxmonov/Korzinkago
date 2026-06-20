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
php artisan storage:link
php artisan serve
# Real-time WebSocket server (alohida terminalda):
php artisan reverb:start
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
- ✅ Payme va Click orqali onlayn to'lov + naqd to'lov
- ✅ Promokod / chegirma tizimi (foiz yoki qat'iy summa, limitlar, muddat)
- ✅ Mahsulotga rasm yuklash (admin paneldan upload)
- ✅ FCM push-bildirishnomalar (buyurtma holati o'zgarganda)
- ✅ Mahsulot va kuryer reyting/izoh tizimi (1-5 yulduz)
- ✅ Sevimlilar (wishlist)
- ✅ Real-time buyurtma kuzatuvi (Laravel Reverb WebSocket — status + kuryer lokatsiyasi jonli)
- ✅ Kuryer: buyurtmalarni qabul qilish, xaritada yo'nalish, statusni yangilash
- ✅ Admin panel: statistika, hisobotlar (grafiklar), buyurtmalar, mahsulotlar, kategoriyalar, promokodlar, kuryerlar
- ✅ Yetkazib berish narxi masofaga qarab hisoblanadi

## Admin panel
`http://localhost:8000/admin` — demo: `998900000000` / `admin123`

## Test promokodlar (seed)
- `WELCOME10` — 10% chegirma (faqat birinchi buyurtma, min 50 000 so'm, max 20 000)
- `KORZINKA15000` — 15 000 so'm chegirma (min 100 000 so'm)

## API hujjati
`backend/routes/api.php` faylida barcha endpointlar. Asosiylari:
- `POST /api/auth/send-otp`, `POST /api/auth/verify-otp`
- `GET /api/categories`, `GET /api/products`
- `GET/POST /api/cart`
- `POST /api/orders`, `GET /api/orders`
- `POST /api/payme/checkout`, `POST /api/payme/callback`
- `GET /api/courier/orders`, `POST /api/courier/orders/{id}/status`

> Eslatma: API kalitlari (Eskiz, Payme, Google Maps) `.env` orqali sozlanadi.
