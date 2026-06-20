@extends('admin.layout')
@section('title', 'Kategoriyalar')

@section('content')
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.categories.create') }}" class="bg-brand text-white px-4 py-2 rounded-lg text-sm">+ Yangi kategoriya</a>
    </div>

    <div class="bg-white rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr>
                    <th class="px-5 py-3">Nomi</th>
                    <th class="px-5 py-3">Mahsulotlar</th>
                    <th class="px-5 py-3">Tartib</th>
                    <th class="px-5 py-3">Holat</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $cat)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium">{{ $cat->name }}</td>
                        <td class="px-5 py-3">{{ $cat->products_count }}</td>
                        <td class="px-5 py-3">{{ $cat->sort_order }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $cat->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200' }}">{{ $cat->is_active ? 'Faol' : 'Nofaol' }}</span>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.categories.edit', $cat) }}" class="text-blue-600">Tahrirlash</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('O\'chirilsinmi?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 ml-2">O'chirish</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Kategoriyalar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $categories->links() }}</div>
@endsection
