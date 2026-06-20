@extends('admin.layout')
@section('title', $product->exists ? 'Mahsulotni tahrirlash' : 'Yangi mahsulot')

@section('content')
    <div class="bg-white rounded-xl p-6 max-w-2xl">
        <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="space-y-4" enctype="multipart/form-data">
            @csrf
            @if ($product->exists) @method('PUT') @endif

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm mb-1">Nomi *</label>
                    <input name="name" value="{{ old('name', $product->name) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm mb-1">Nomi (RU)</label>
                    <input name="name_ru" value="{{ old('name_ru', $product->name_ru) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Kategoriya *</label>
                    <select name="category_id" class="w-full border rounded-lg px-3 py-2">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1">O'lchov birligi *</label>
                    <input name="unit" value="{{ old('unit', $product->unit ?? 'dona') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Narx *</label>
                    <input name="price" type="number" step="0.01" value="{{ old('price', $product->price) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Eski narx (chegirma)</label>
                    <input name="old_price" type="number" step="0.01" value="{{ old('old_price', $product->old_price) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Qoldiq *</label>
                    <input name="stock" type="number" value="{{ old('stock', $product->stock ?? 0) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Qadam (step)</label>
                    <input name="step" type="number" step="0.001" value="{{ old('step', $product->step ?? 1) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm mb-1">Rasm yuklash</label>
                    @if ($product->exists && $product->image)
                        <img src="{{ $product->image }}" alt="" class="w-24 h-24 object-cover rounded-lg mb-2 border">
                    @endif
                    <input type="file" name="image_file" accept="image/*"
                           class="w-full border rounded-lg px-3 py-2 bg-white">
                    <p class="text-xs text-gray-400 mt-1">JPG/PNG, 4MB gacha. Yuklansa, quyidagi URL e'tiborga olinmaydi.</p>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm mb-1">Yoki rasm URL</label>
                    @php
                        $raw = $product->getRawOriginal('image');
                        $urlValue = ($raw && Str::startsWith($raw, 'http')) ? $raw : '';
                    @endphp
                    <input name="image" value="{{ old('image', $urlValue) }}"
                           placeholder="https://..." class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm mb-1">Tavsif</label>
                    <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2">{{ old('description', $product->description) }}</textarea>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))> Faol
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false))> Tavsiya etilgan
                </label>
            </div>

            <div class="flex gap-2 pt-2">
                <button class="bg-brand text-white px-6 py-2.5 rounded-lg">Saqlash</button>
                <a href="{{ route('admin.products.index') }}" class="px-6 py-2.5 rounded-lg bg-gray-100">Bekor qilish</a>
            </div>
        </form>
    </div>
@endsection
