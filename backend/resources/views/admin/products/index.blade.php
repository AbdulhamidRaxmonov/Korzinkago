@extends('admin.layout')
@section('title', 'Mahsulotlar')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" placeholder="Qidirish..."
                   class="border rounded-lg px-3 py-2 text-sm">
            <button class="bg-gray-800 text-white px-4 rounded-lg text-sm">Qidirish</button>
        </form>
        <a href="{{ route('admin.products.create') }}" class="bg-brand text-white px-4 py-2 rounded-lg text-sm">+ Yangi mahsulot</a>
    </div>

    <div class="bg-white rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr>
                    <th class="px-5 py-3">Nomi</th>
                    <th class="px-5 py-3">Kategoriya</th>
                    <th class="px-5 py-3">Narx</th>
                    <th class="px-5 py-3">Qoldiq</th>
                    <th class="px-5 py-3">Holat</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium">{{ $p->name }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $p->category->name ?? '—' }}</td>
                        <td class="px-5 py-3">{{ number_format($p->price) }} so'm</td>
                        <td class="px-5 py-3">{{ $p->stock }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $p->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $p->is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.products.edit', $p) }}" class="text-blue-600">Tahrirlash</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="inline" onsubmit="return confirm('O\'chirilsinmi?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 ml-2">O'chirish</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">Mahsulotlar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
