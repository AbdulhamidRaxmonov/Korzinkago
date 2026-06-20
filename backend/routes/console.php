<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Korzinkago - tez va ishonchli yetkazib berish.');
})->purpose('Display an inspiring quote');
