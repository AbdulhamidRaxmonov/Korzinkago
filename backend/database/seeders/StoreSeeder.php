<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        Store::updateOrCreate(['slug' => 'korzinka-chilonzor'], [
            'name' => 'Korzinka Chilonzor',
            'address' => 'Toshkent, Chilonzor tumani, Bunyodkor shoh ko\'chasi',
            'lat' => 41.2756,
            'lng' => 69.2034,
            'phone' => '+998712000000',
            'open_at' => '08:00',
            'close_at' => '23:00',
            'is_active' => true,
        ]);
    }
}
