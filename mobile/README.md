# Korzinkago — Mobil ilova (Flutter)

Foydalanuvchi (xarid) va Kuryer (yetkazib berish) — bitta ilovada, rolga qarab oqim ajratiladi.

## Ishga tushirish

> Bu repozitoriyada faqat `lib/`, `pubspec.yaml` va konfiguratsiya fayllari mavjud.
> Platforma papkalari (`android/`, `ios/`) ni quyidagi buyruq bilan yarating:

```bash
cd mobile
flutter create .          # android/ va ios/ papkalarini yaratadi
flutter pub get
flutter run
```

API manzilini ko'rsatib ishga tushirish:
```bash
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api \
            --dart-define=GOOGLE_MAPS_API_KEY=SIZNING_KALITINGIZ
```

## Native sozlamalar (flutter create . dan keyin)

### Android — `android/app/src/main/AndroidManifest.xml`

`<application>` tegidan oldin ruxsatlarni qo'shing:
```xml
<uses-permission android:name="android.permission.INTERNET"/>
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION"/>
```

`<application>` ichiga Google Maps kalitini qo'shing:
```xml
<meta-data
    android:name="com.google.android.geo.API_KEY"
    android:value="SIZNING_GOOGLE_MAPS_KALITINGIZ"/>
```

`android/app/build.gradle` da `minSdkVersion 21` (yoki yuqori) bo'lishi kerak.

### iOS — `ios/Runner/Info.plist`

```xml
<key>NSLocationWhenInUseUsageDescription</key>
<string>Yetkazib berish manzilini aniqlash uchun joylashuv kerak</string>
```

`ios/Runner/AppDelegate.swift` ga:
```swift
import GoogleMaps
// application(_:didFinishLaunchingWithOptions:) ichida:
GMSServices.provideAPIKey("SIZNING_GOOGLE_MAPS_KALITINGIZ")
```

## Tuzilma

```
lib/
├── main.dart                 # Kirish nuqtasi (ProviderScope)
├── core/                     # constants, theme, api_client, formatters
├── models/                   # data modellari
├── services/                 # API servislari (Dio)
├── providers/                # Riverpod state
├── router/                   # go_router (rolga qarab yo'naltirish)
├── widgets/                  # ProductCard va boshqalar
└── features/
    ├── auth/                 # splash, telefon, OTP
    ├── shell/                # pastki navigatsiya
    ├── home/                 # bosh sahifa
    ├── catalog/              # katalog, kategoriya, mahsulot
    ├── cart/                 # savat
    ├── checkout/             # rasmiylashtirish, xarita, Payme
    ├── orders/               # buyurtmalar va kuzatuv
    ├── profile/              # profil, manzillar
    └── courier/              # kuryer bosh ekrani va buyurtma
```

## Test akkauntlar (backend seed)
- **Mijoz:** +998 90 123 45 67
- **Kuryer:** +998 90 765 43 21

> SMS_FAKE=true bo'lganda OTP kod API javobida (va konsolda) ko'rsatiladi.


## Push-bildirishnomalar (Firebase FCM)

1. [Firebase Console](https://console.firebase.google.com) da loyiha yarating.
2. Android uchun `google-services.json` ni `android/app/` ga, iOS uchun `GoogleService-Info.plist` ni `ios/Runner/` ga joylang.
3. `flutterfire configure` ishga tushiring yoki `firebase_core` hujjatiga amal qiling.
4. Android `android/build.gradle` va `android/app/build.gradle` ga Google Services plagin qo'shing.
5. Backend tomonda Firebase service account JSON kalitini `backend/storage/app/firebase-credentials.json` ga joylang va `.env` da `FCM_PROJECT_ID`, `FCM_FAKE=false` qiling.

Ilovaga kirilganda token avtomatik backendga (`POST /api/device-token`) yuboriladi. Buyurtma holati o'zgarganda foydalanuvchiga push keladi.
