<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Services\DeliveryService;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        protected DeliveryService $delivery,
        protected FcmService $fcm,
    ) {
    }

    /**
     * Foydalanuvchining buyurtmalari.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with(['items', 'courier:id,name,phone,current_lat,current_lng'])
            ->latest()
            ->paginate(15);

        return response()->json($orders);
    }

    /**
     * Bitta buyurtma (kuzatuv uchun).
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $order->load(['items', 'statusLogs', 'courier:id,name,phone,current_lat,current_lng', 'store', 'payment']);

        return response()->json($order);
    }

    /**
     * Yetkazib berish narxini oldindan hisoblash (checkout sahifasi uchun).
     */
    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $store = Store::where('is_active', true)->first();
        $itemsTotal = $this->cartTotal($request);

        $distance = $store
            ? $this->delivery->distanceKm($store->lat, $store->lng, $data['lat'], $data['lng'])
            : 0;

        $fee = $this->delivery->calculateFee($distance, $itemsTotal);

        return response()->json([
            'items_total' => $itemsTotal,
            'distance_km' => $distance,
            'delivery_fee' => $fee,
            'total' => $itemsTotal + $fee,
        ]);
    }

    /**
     * Savatdan buyurtma yaratish.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address_id' => ['nullable', 'exists:addresses,id'],
            'delivery_address' => ['required_without:address_id', 'string'],
            'delivery_lat' => ['required_without:address_id', 'numeric'],
            'delivery_lng' => ['required_without:address_id', 'numeric'],
            'entrance' => ['nullable', 'string'],
            'floor' => ['nullable', 'string'],
            'apartment' => ['nullable', 'string'],
            'comment' => ['nullable', 'string', 'max:500'],
            'recipient_phone' => ['nullable', 'string', 'max:20'],
            'payment_method' => ['required', 'in:cash,payme,click,card'],
        ]);

        $user = $request->user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Savat bo\'sh.'], 422);
        }

        // Manzilni aniqlash
        if (! empty($data['address_id'])) {
            $address = $user->addresses()->findOrFail($data['address_id']);
            $deliveryAddress = $address->address;
            $lat = $address->lat;
            $lng = $address->lng;
            $entrance = $address->entrance;
            $floor = $address->floor;
            $apartment = $address->apartment;
        } else {
            $deliveryAddress = $data['delivery_address'];
            $lat = $data['delivery_lat'];
            $lng = $data['delivery_lng'];
            $entrance = $data['entrance'] ?? null;
            $floor = $data['floor'] ?? null;
            $apartment = $data['apartment'] ?? null;
        }

        $store = Store::where('is_active', true)->first();
        $itemsTotal = $cartItems->sum(fn ($i) => $i->product->price * $i->quantity);
        $distance = $store ? $this->delivery->distanceKm($store->lat, $store->lng, $lat, $lng) : 0;
        $deliveryFee = $this->delivery->calculateFee($distance, $itemsTotal);

        $order = DB::transaction(function () use (
            $user, $cartItems, $store, $itemsTotal, $deliveryFee, $distance,
            $deliveryAddress, $lat, $lng, $entrance, $floor, $apartment, $data
        ) {
            $order = Order::create([
                'number' => Order::generateNumber(),
                'user_id' => $user->id,
                'store_id' => $store?->id,
                'status' => 'new',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'delivery_address' => $deliveryAddress,
                'delivery_lat' => $lat,
                'delivery_lng' => $lng,
                'entrance' => $entrance,
                'floor' => $floor,
                'apartment' => $apartment,
                'comment' => $data['comment'] ?? null,
                'recipient_phone' => $data['recipient_phone'] ?? $user->phone,
                'items_total' => $itemsTotal,
                'delivery_fee' => $deliveryFee,
                'discount' => 0,
                'total' => $itemsTotal + $deliveryFee,
                'distance_km' => $distance,
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'unit' => $item->product->unit,
                    'total' => $item->product->price * $item->quantity,
                ]);

                $item->product->increment('sold_count', (int) $item->quantity);
                $item->product->decrement('stock', (int) $item->quantity);
            }

            $order->logStatus('new', $user->id, 'Buyurtma yaratildi');
            $user->cartItems()->delete();

            return $order;
        });

        $order->load('items');

        $this->fcm->sendToUser($user, 'Buyurtma qabul qilindi', "{$order->number} raqamli buyurtmangiz qabul qilindi.", [
            'order_id' => $order->id,
            'type' => 'order_created',
        ]);

        return response()->json([
            'success' => true,
            'order' => $order,
            // Onlayn to'lov bo'lsa, frontend tegishli checkout sahifasiga yo'naltiradi
            'needs_payment' => in_array($data['payment_method'], ['payme', 'click']),
            'payment_method' => $data['payment_method'],
        ], 201);
    }

    /**
     * Buyurtmani bekor qilish (faqat hali yo'lga chiqmagan bo'lsa).
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if (in_array($order->status, ['on_way', 'delivered', 'cancelled'])) {
            return response()->json(['message' => 'Bu buyurtmani bekor qilib bo\'lmaydi.'], 422);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $request->input('reason', 'Mijoz bekor qildi'),
        ]);

        $order->logStatus('cancelled', $request->user()->id, $order->cancel_reason);

        return response()->json(['success' => true, 'order' => $order]);
    }

    protected function cartTotal(Request $request): float
    {
        return $request->user()->cartItems()->with('product')->get()
            ->sum(fn ($i) => $i->product ? $i->product->price * $i->quantity : 0);
    }
}
