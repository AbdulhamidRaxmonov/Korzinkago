@extends('admin.layout')
@section('title', 'Promokodlar')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.promos.create') }}" class="bg-brand text-white px-4 py-2 rounded-lg text-sm">+ Yangi promokod</a>
    </div>

    <div class="bg-white rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr>
                    <th class="px-5 py-3">Kod</th>
                    <th class="px-5 py-3">Chegirma</th>
                    <th class="px-5 py-3">Min. buyurtma</th>
                    <th class="px-5 py-3">Ishlatildi</th>
                    <th class="px-5 py-3">Muddat</th>
                    <th class="px-5 py-3">Holat</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($promos as $promo)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono font-bold">{{ $promo->code }}</td>
                        <td class="px-5 py-3">
                            {{ $promo->type === 'percent' ? $promo->value.'%' : number_format($promo->value).' so\'m' }}
                            @if ($promo->type === 'percent' && $promo->max_discount)
                                <span class="text-xs text-gray-400">(max {{ number_format($promo->max_discount) }})</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">{{ number_format($promo->min_order) }} so'm</td>
                        <td class="px-5 py-3">{{ $promo->used_count }}{{ $promo->usage_limit ? '/'.$promo->usage_limit : '' }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            {{ $promo->expires_at ? $promo->expires_at->format('d.m.Y') : '∞' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $promo->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200' }}">{{ $promo->is_active ? 'Faol' : 'Nofaol' }}</span>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.promos.edit', $promo) }}" class="text-blue-600">Tahrirlash</a>
                            <form method="POST" action="{{ route('admin.promos.destroy', $promo) }}" class="inline" onsubmit="return confirm('O\'chirilsinmi?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 ml-2">O'chirish</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">Promokodlar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $promos->links() }}</div>
@endsection
