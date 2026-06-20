@extends('admin.layout')
@section('title', 'Buyurtma '.$order->number)

@section('content')
    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-xl p-5">
                <h3 class="font-semibold mb-3">Mahsulotlar</h3>
                <table class="w-full text-sm">
                    @foreach ($order->items as $item)
                        <tr class="border-b">
                            <td class="py-2">{{ $item->product_name }}</td>
                            <td class="py-2 text-gray-500">{{ rtrim(rtrim((string)$item->quantity, '0'), '.') }} {{ $item->unit }}</td>
                            <td class="py-2 text-right">{{ number_format($item->total) }} so'm</td>
                        </tr>
                    @endforeach
                </table>
                <div class="mt-4 text-sm space-y-1">
                    <div class="flex justify-between"><span>Mahsulotlar</span><span>{{ number_format($order->items_total) }} so'm</span></div>
                    <div class="flex justify-between"><span>Yetkazib berish</span><span>{{ number_format($order->delivery_fee) }} so'm</span></div>
                    <div class="flex justify-between font-bold text-base pt-2 border-t"><span>Jami</span><span>{{ number_format($order->total) }} so'm</span></div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5">
                <h3 class="font-semibold mb-3">Holat tarixi</h3>
                <div class="space-y-2 text-sm">
                    @foreach ($order->statusLogs as $log)
                        <div class="flex justify-between border-b pb-1">
                            <span><x-status :status="$log->status" /> {{ $log->note }}</span>
                            <span class="text-gray-400">{{ $log->created_at->format('d.m H:i') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl p-5 text-sm">
                <h3 class="font-semibold mb-3">Mijoz</h3>
                <p>{{ $order->user->name }}</p>
                <p class="text-gray-500">+{{ $order->user->phone }}</p>
                <hr class="my-3">
                <h3 class="font-semibold mb-2">Manzil</h3>
                <p class="text-gray-600">{{ $order->delivery_address }}</p>
                @if ($order->comment)<p class="text-gray-400 mt-1">Izoh: {{ $order->comment }}</p>@endif
            </div>

            <div class="bg-white rounded-xl p-5">
                <h3 class="font-semibold mb-3">Holatni o'zgartirish</h3>
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex gap-2">
                    @csrf
                    <select name="status" class="flex-1 border rounded-lg px-3 py-2 text-sm">
                        @foreach (\App\Models\Order::STATUSES as $st)
                            <option value="{{ $st }}" @selected($order->status === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                    <button class="bg-brand text-white px-4 rounded-lg text-sm">Saqlash</button>
                </form>

                <h3 class="font-semibold mt-5 mb-3">Kuryer biriktirish</h3>
                <form method="POST" action="{{ route('admin.orders.assign', $order) }}" class="flex gap-2">
                    @csrf
                    <select name="courier_id" class="flex-1 border rounded-lg px-3 py-2 text-sm">
                        @foreach ($couriers as $c)
                            <option value="{{ $c->id }}" @selected($order->courier_id === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button class="bg-gray-800 text-white px-4 rounded-lg text-sm">Biriktirish</button>
                </form>
            </div>
        </div>
    </div>
@endsection
