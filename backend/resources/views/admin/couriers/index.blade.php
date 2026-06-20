@extends('admin.layout')
@section('title', 'Kuryerlar')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.couriers.create') }}" class="bg-brand text-white px-4 py-2 rounded-lg text-sm">+ Yangi kuryer</a>
    </div>

    <div class="bg-white rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr>
                    <th class="px-5 py-3">Ism</th>
                    <th class="px-5 py-3">Telefon</th>
                    <th class="px-5 py-3">Transport</th>
                    <th class="px-5 py-3">Yetkazgan</th>
                    <th class="px-5 py-3">Online</th>
                    <th class="px-5 py-3">Holat</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($couriers as $c)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium">{{ $c->name }}</td>
                        <td class="px-5 py-3">+{{ $c->phone }}</td>
                        <td class="px-5 py-3">{{ $c->vehicle_type ?? '—' }}</td>
                        <td class="px-5 py-3">{{ $c->delivered_count }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $c->is_online ? 'bg-green-100 text-green-700' : 'bg-gray-200' }}">{{ $c->is_online ? 'Online' : 'Offline' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $c->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $c->is_active ? 'Faol' : 'Bloklangan' }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('admin.couriers.toggle', $c) }}" class="inline">
                                @csrf
                                <button class="text-blue-600">{{ $c->is_active ? 'Bloklash' : 'Faollashtirish' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">Kuryerlar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $couriers->links() }}</div>
@endsection
