<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Savatni ko'rish (jami summa bilan).
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->cartPayload($request));
    }

    /**
     * Savatga mahsulot qo'shish yoki miqdorni belgilash.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.1'],
        ]);

        $product = Product::active()->findOrFail($data['product_id']);
        $quantity = $data['quantity'] ?? $product->step;

        CartItem::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            ['quantity' => $quantity],
        );

        return response()->json($this->cartPayload($request));
    }

    /**
     * Miqdorni o'zgartirish (+/-).
     */
    public function update(Request $request, CartItem $item): JsonResponse
    {
        abort_if($item->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0'],
        ]);

        if ($data['quantity'] <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $data['quantity']]);
        }

        return response()->json($this->cartPayload($request));
    }

    /**
     * Savatdan o'chirish.
     */
    public function destroy(Request $request, CartItem $item): JsonResponse
    {
        abort_if($item->user_id !== $request->user()->id, 403);
        $item->delete();

        return response()->json($this->cartPayload($request));
    }

    /**
     * Savatni tozalash.
     */
    public function clear(Request $request): JsonResponse
    {
        $request->user()->cartItems()->delete();

        return response()->json($this->cartPayload($request));
    }

    /**
     * Savat tarkibi + summalar.
     */
    protected function cartPayload(Request $request): array
    {
        $items = $request->user()->cartItems()->with('product')->get();

        $itemsTotal = $items->sum(fn ($i) => $i->product ? $i->product->price * $i->quantity : 0);

        return [
            'items' => $items,
            'count' => $items->count(),
            'items_total' => round($itemsTotal, 2),
        ];
    }
}
