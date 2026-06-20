<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        PromoCode::updateOrCreate(['code' => 'WELCOME10'], [
            'type' => 'percent',
            'value' => 10,
            'min_order' => 50000,
            'max_discount' => 20000,
            'per_user_limit' => 1,
            'first_order_only' => true,
            'is_active' => true,
        ]);

        PromoCode::updateOrCreate(['code' => 'KORZINKA15000'], [
            'type' => 'fixed',
            'value' => 15000,
            'min_order' => 100000,
            'usage_limit' => 100,
            'per_user_limit' => 2,
            'is_active' => true,
        ]);
    }
}
