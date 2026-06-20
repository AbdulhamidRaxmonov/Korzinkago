@extends('admin.layout')
@section('title', 'Boshqaruv paneli')

@section('content')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @php $cards = [
            ['Bugungi buyurtmalar', $stats['orders_today'], 'bg-blue-50 text-blue-600'],
            ['Jami buyurtmalar', $stats['orders_total'], 'bg-purple-50 text-purple-600'],
            ['Bugungi tushum', number_format($stats['revenue_today']).' so\'m', 'bg-green-50 text-green-600'],
            ['Jami tushum', number_format($stats['revenue_total']).' so\'m', 'bg-emerald-50 text-emerald-600'],
            ['Foydalanuvchilar', $stats['users'], 'bg-orange-50 text-orange-600'],
            ['Kuryerlar', $stats['couriers'], 'bg-pink-50 text-pink-600'],
            ['Mahsulotlar', $stats['products'], 'bg-cyan-50 text-cyan-600'],
            ['Kutilayotgan', $stats['pending'], 'bg-red-50 text-red-600'],
        ]; @endphp
        @foreach ($cards as [$label, $value, $color])
            <div class="bg-white p-5 rounded-xl">
                <div class="inline-block px-2 py-1 rounded text-xs {{ $color }}">{{ $label }}</div>
                <div class="text-2xl font-bold mt-2">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b font-semibold">So'nggi buyurtmalar</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr>
                    <th class="px-5 py-3">Raqam</th>
                    <th class="px-5 py-3">Mijoz</th>
                    <th class="px-5 py-3">Summa</th>
                    <th class="px-5 py-3">Holat</th>
                    <th class="px-5 py-3">Sana</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentOrders as $order)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-brand font-medium">{{ $order->number }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $order->user->name ?? '—' }}</td>
                        <td class="px-5 py-3">{{ number_format($order->total) }} so'm</td>
                        <td class="px-5 py-3"><x-status :status="$order->status" /></td>
                        <td class="px-5 py-3 text-gray-400">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
