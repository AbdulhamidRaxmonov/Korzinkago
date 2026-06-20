<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Kategoriyalar daraxti (asosiy + bolalar).
     */
    public function categories(): JsonResponse
    {
        $categories = Category::active()
            ->root()
            ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    /**
     * Mahsulotlar ro'yxati (filtr, qidiruv, saralash, sahifalash).
     */
    public function products(Request $request): JsonResponse
    {
        $query = Product::active()->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ru', 'like', "%{$search}%")
                    ->orWhere('barcode', $search);
            });
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->boolean('discount')) {
            $query->whereColumn('old_price', '>', 'price');
        }

        match ($request->string('sort')->value()) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('sold_count'),
            default => $query->orderByDesc('id'),
        };

        return response()->json($query->paginate($request->integer('per_page', 20)));
    }

    /**
     * Bitta mahsulot.
     */
    public function product(Product $product): JsonResponse
    {
        $product->load('category');

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(8)
            ->get();

        return response()->json([
            'product' => $product,
            'related' => $related,
        ]);
    }

    /**
     * Bosh sahifa: featured, chegirmali, kategoriyalar.
     */
    public function home(): JsonResponse
    {
        return response()->json([
            'categories' => Category::active()->root()->orderBy('sort_order')->limit(12)->get(),
            'featured' => Product::active()->featured()->limit(10)->get(),
            'discounts' => Product::active()->whereColumn('old_price', '>', 'price')->limit(10)->get(),
            'popular' => Product::active()->orderByDesc('sold_count')->limit(10)->get(),
        ]);
    }
}
