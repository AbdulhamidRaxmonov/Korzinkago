<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    public function __construct(protected FcmService $fcm)
    {
    }

    public function index(Request $request): View
    {
        $query = Order::with(['user:id,name,phone', 'courier:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'user', 'courier', 'statusLogs', 'payment']);
        $couriers = User::where('role', 'courier')->get();

        return view('admin.orders.show', compact('order', 'couriers'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', Order::STATUSES)],
        ]);

        $order->update(['status' => $data['status']]);
        $order->logStatus($data['status'], auth()->id(), 'Admin tomonidan o\'zgartirildi');

        $this->fcm->sendToUser($order->user_id, 'Buyurtma holati', "{$order->number}: yangi holat — {$data['status']}", [
            'order_id' => $order->id,
        ]);

        return back()->with('ok', 'Holat yangilandi.');
    }

    public function assignCourier(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'courier_id' => ['required', 'exists:users,id'],
        ]);

        $order->update(['courier_id' => $data['courier_id']]);

        return back()->with('ok', 'Kuryer biriktirildi.');
    }
}
