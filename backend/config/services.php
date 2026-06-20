<?php

return [
    'eskiz' => [
        'email' => env('ESKIZ_EMAIL'),
        'password' => env('ESKIZ_PASSWORD'),
        'base_url' => env('ESKIZ_BASE_URL', 'https://notify.eskiz.uz/api'),
        'sender' => env('ESKIZ_SENDER', '4546'),
        'fake' => (bool) env('SMS_FAKE', false),
    ],

    'payme' => [
        'merchant_id' => env('PAYME_MERCHANT_ID'),
        'key' => env('PAYME_KEY'),
        'test_key' => env('PAYME_TEST_KEY'),
        'checkout_url' => env('PAYME_CHECKOUT_URL', 'https://checkout.paycom.uz'),
        'account_field' => env('PAYME_ACCOUNT_FIELD', 'order_id'),
    ],

    'click' => [
        'service_id' => env('CLICK_SERVICE_ID'),
        'merchant_id' => env('CLICK_MERCHANT_ID'),
        'secret_key' => env('CLICK_SECRET_KEY'),
        'merchant_user_id' => env('CLICK_MERCHANT_USER_ID'),
        'checkout_url' => env('CLICK_CHECKOUT_URL', 'https://my.click.uz'),
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID'),
        'credentials' => env('FCM_CREDENTIALS_PATH', storage_path('app/firebase-credentials.json')),
        'fake' => (bool) env('FCM_FAKE', false),
    ],

    'delivery' => [
        'base_fee' => (int) env('DELIVERY_BASE_FEE', 8000),
        'per_km' => (int) env('DELIVERY_PER_KM', 2000),
        'free_from' => (int) env('DELIVERY_FREE_FROM', 200000),
    ],
];
