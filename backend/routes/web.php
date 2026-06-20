<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'Korzinkago API',
        'status' => 'ok',
        'docs' => '/api',
    ]);
});
