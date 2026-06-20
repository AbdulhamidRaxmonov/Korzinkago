<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Mahsulot izohlari (ochiq).
     */
    public function productReviews(Product $product): JsonResponse
    {
        $reviews = Review::where('type', 'product')
            ->where('product_id', $product->id)
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(15);

        return response()->json([
            'rating' => $product->rating,
            'reviews_count' => $product->reviews_count,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Buyurtma uchun baho qoldirish (mahsulot va/yoki kuryer).
     * Faqat yetkazib berilgan buyurtmaga.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'courier_rating' => ['nullable', 'integer', 'between:1,5'],
            'courier_comment' => ['nullable', 'string', 'max:1000'],
            'products' => ['nullable', 'array'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.rating' => ['required', 'integer', 'between:1,5'],
            'products.*.comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $order = $user->orders()->findOrFail($data['order_id']);

        if ($order->status !== 'delivered') {
            return response()->json(['message' => 'Faqat yetkazilgan buyurtmaga baho berish mumkin.'], 422);
        }

        DB::transaction(function () use ($data, $user, $order) {
            // Kuryer bahosi
            if (! empty($data['courier_rating']) && $order->courier_id) {
                Review::updateOrCreate(
                    ['type' => 'courier', 'courier_id' => $order->courier_id, 'order_id' => $order->id, 'user_id' => $user->id],
                    ['rating' => $data['courier_rating'], 'comment' => $data['courier_comment'] ?? null],
                );
                $this->recalcCourier($order->courier_id);
            }

            // Mahsulot baholari
            foreach ($data['products'] ?? [] as $p) {
                Review::updateOrCreate(
                    ['type' => 'product', 'product_id' => $p['product_id'], 'order_id' => $order->id, 'user_id' => $user->id],
                    ['rating' => $p['rating'], 'comment' => $p['comment'] ?? null],
                );
                $this->recalcProduct($p['product_id']);
            }
        });

        return response()->json(['success' => true, 'message' => 'Bahoyingiz uchun rahmat!']);
    }

    protected function recalcProduct(int $productId): void
    {
        $stats = Review::where('type', 'product')->where('product_id', $productId)
            ->selectRaw('AVG(rating) avg, COUNT(*) cnt')->first();

        Product::where('id', $productId)->update([
            'rating' => round($stats->avg ?? 0, 2),
            'reviews_count' => $stats->cnt ?? 0,
        ]);
    }

    protected function recalcCourier(int $courierId): void
    {
        $stats = Review::where('type', 'courier')->where('courier_id', $courierId)
            ->selectRaw('AVG(rating) avg, COUNT(*) cnt')->first();

        User::where('id', $courierId)->update([
            'rating' => round($stats->avg ?? 0, 2),
            'reviews_count' => $stats->cnt ?? 0,
        ]);
    }
}
