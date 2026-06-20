@extends('admin.layout')
@section('title', $category->exists ? 'Kategoriyani tahrirlash' : 'Yangi kategoriya')

@section('content')
    <div class="bg-white rounded-xl p-6 max-w-lg">
        <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="space-y-4">
            @csrf
            @if ($category->exists) @method('PUT') @endif

            <div>
                <label class="block text-sm mb-1">Nomi *</label>
                <input name="name" value="{{ old('name', $category->name) }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Nomi (RU)</label>
                <input name="name_ru" value="{{ old('name_ru', $category->name_ru) }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Asosiy kategoriya</label>
                <select name="parent_id" class="w-full border rounded-lg px-3 py-2">
                    <option value="">— Yo'q (asosiy) —</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Tartib raqami</label>
                <input name="sort_order" type="number" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))> Faol
            </label>

            <div class="flex gap-2 pt-2">
                <button class="bg-brand text-white px-6 py-2.5 rounded-lg">Saqlash</button>
                <a href="{{ route('admin.categories.index') }}" class="px-6 py-2.5 rounded-lg bg-gray-100">Bekor qilish</a>
            </div>
        </form>
    </div>
@endsection
