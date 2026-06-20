<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'orders_total' => Order::count(),
            'revenue_today' => Order::whereDate('created_at', today())
                ->where('payment_status', 'paid')->sum('total'),
            'revenue_total' => Order::where('payment_status', 'paid')->sum('total'),
            'users' => User::where('role', 'user')->count(),
            'couriers' => User::where('role', 'courier')->count(),
            'products' => Product::count(),
            'pending' => Order::whereIn('status', ['new', 'accepted', 'assembling', 'ready'])->count(),
        ];

        $recentOrders = Order::with('user:id,name,phone')->latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
