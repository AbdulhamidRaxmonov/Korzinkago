@extends('admin.layout')
@section('title', 'Buyurtmalar')

@section('content')
    <div class="mb-4 flex gap-2 flex-wrap">
        <a href="{{ route('admin.orders.index') }}"
           class="px-3 py-1.5 rounded-lg text-sm {{ !request('status') ? 'bg-brand text-white' : 'bg-white' }}">Barchasi</a>
        @foreach (\App\Models\Order::STATUSES as $st)
            <a href="{{ route('admin.orders.index', ['status' => $st]) }}"
               class="px-3 py-1.5 rounded-lg text-sm {{ request('status') === $st ? 'bg-brand text-white' : 'bg-white' }}">
                {{ $st }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr>
                    <th class="px-5 py-3">Raqam</th>
                    <th class="px-5 py-3">Mijoz</th>
                    <th class="px-5 py-3">Kuryer</th>
                    <th class="px-5 py-3">Summa</th>
                    <th class="px-5 py-3">To'lov</th>
                    <th class="px-5 py-3">Holat</th>
                    <th class="px-5 py-3">Sana</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-5 py-3"><a href="{{ route('admin.orders.show', $order) }}" class="text-brand font-medium">{{ $order->number }}</a></td>
                        <td class="px-5 py-3">{{ $order->user->name ?? '—' }}<br><span class="text-gray-400 text-xs">{{ $order->user->phone ?? '' }}</span></td>
                        <td class="px-5 py-3">{{ $order->courier->name ?? '—' }}</td>
                        <td class="px-5 py-3">{{ number_format($order->total) }} so'm</td>
                        <td class="px-5 py-3">{{ strtoupper($order->payment_method) }} <span class="text-xs text-gray-400">({{ $order->payment_status }})</span></td>
                        <td class="px-5 py-3"><x-status :status="$order->status" /></td>
                        <td class="px-5 py-3 text-gray-400">{{ $order->created_at->format('d.m H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">Buyurtmalar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
