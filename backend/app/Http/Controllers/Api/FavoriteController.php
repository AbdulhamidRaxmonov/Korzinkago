<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Sevimli mahsulotlar ro'yxati.
     */
    public function index(Request $request): JsonResponse
    {
        $products = $request->user()->favorites()
            ->with('product.category')
            ->latest()
            ->get()
            ->pluck('product')
            ->filter()
            ->values();

        return response()->json($products);
    }

    /**
     * Sevimlilarga qo'shish / olib tashlash (toggle).
     */
    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $existing = Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['favorited' => false]);
        }

        Favorite::create([
            'user_id' => $request->user()->id,
            'product_id' => $data['product_id'],
        ]);

        return response()->json(['favorited' => true]);
    }

    /**
     * Sevimli mahsulot ID'lari (mobil sinxronlash uchun).
     */
    public function ids(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->favorites()->pluck('product_id')
        );
    }
}
