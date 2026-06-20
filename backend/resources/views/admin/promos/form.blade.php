@extends('admin.layout')
@section('title', $promo->exists ? 'Promokodni tahrirlash' : 'Yangi promokod')

@section('content')
    <div class="bg-white rounded-xl p-6 max-w-2xl">
        <form method="POST" action="{{ $promo->exists ? route('admin.promos.update', $promo) : route('admin.promos.store') }}" class="space-y-4">
            @csrf
            @if ($promo->exists) @method('PUT') @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Kod *</label>
                    <input name="code" value="{{ old('code', $promo->code) }}" placeholder="WELCOME10"
                           class="w-full border rounded-lg px-3 py-2 font-mono uppercase">
                </div>
                <div>
                    <label class="block text-sm mb-1">Turi *</label>
                    <select name="type" class="w-full border rounded-lg px-3 py-2">
                        <option value="percent" @selected(old('type', $promo->type) === 'percent')>Foiz (%)</option>
                        <option value="fixed" @selected(old('type', $promo->type) === 'fixed')>Qat'iy summa (so'm)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1">Qiymat * <span class="text-gray-400 text-xs">(% yoki so'm)</span></label>
                    <input name="value" type="number" step="0.01" value="{{ old('value', $promo->value) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Maks. chegirma <span class="text-gray-400 text-xs">(foiz uchun)</span></label>
                    <input name="max_discount" type="number" value="{{ old('max_discount', $promo->max_discount) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Min. buyurtma summasi</label>
                    <input name="min_order" type="number" value="{{ old('min_order', $promo->min_order ?? 0) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Umumiy limit <span class="text-gray-400 text-xs">(bo'sh = cheksiz)</span></label>
                    <input name="usage_limit" type="number" value="{{ old('usage_limit', $promo->usage_limit) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Foydalanuvchiga limit</label>
                    <input name="per_user_limit" type="number" value="{{ old('per_user_limit', $promo->per_user_limit ?? 1) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div></div>
                <div>
                    <label class="block text-sm mb-1">Boshlanish sanasi</label>
                    <input name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($promo->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Tugash sanasi</label>
                    <input name="expires_at" type="datetime-local" value="{{ old('expires_at', optional($promo->expires_at)->format('Y-m-d\TH:i')) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="flex gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="first_order_only" value="1" @checked(old('first_order_only', $promo->first_order_only)) > Faqat birinchi buyurtma
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promo->is_active ?? true))> Faol
                </label>
            </div>

            <div class="flex gap-2 pt-2">
                <button class="bg-brand text-white px-6 py-2.5 rounded-lg">Saqlash</button>
                <a href="{{ route('admin.promos.index') }}" class="px-6 py-2.5 rounded-lg bg-gray-100">Bekor qilish</a>
            </div>
        </form>
    </div>
@endsection
