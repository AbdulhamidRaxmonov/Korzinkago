<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        // So'nggi 30 kun bo'yicha tushum
        $from = today()->subDays(29);

        $daily = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as d, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('d')
            ->pluck('revenue', 'd');

        $dailyOrders = Order::where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as orders')
            ->groupBy('d')
            ->pluck('orders', 'd');

        $labels = [];
        $revenueData = [];
        $ordersData = [];
        for ($i = 0; $i < 30; $i++) {
            $date = $from->copy()->addDays($i);
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d.m');
            $revenueData[] = round((float) ($daily[$key] ?? 0));
            $ordersData[] = (int) ($dailyOrders[$key] ?? 0);
        }

        // Holatlar taqsimoti
        $statusCounts = Order::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        // Eng ko'p sotilgan mahsulotlar
        $topProducts = OrderItem::selectRaw('product_name, SUM(quantity) as qty, SUM(total) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // To'lov usullari
        $paymentMethods = Order::selectRaw('payment_method, COUNT(*) as cnt')
            ->groupBy('payment_method')
            ->pluck('cnt', 'payment_method');

        return view('admin.reports', compact(
            'labels', 'revenueData', 'ordersData', 'statusCounts', 'topProducts', 'paymentMethods'
        ));
    }
}
