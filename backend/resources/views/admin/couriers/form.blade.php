@extends('admin.layout')
@section('title', 'Yangi kuryer')

@section('content')
    <div class="bg-white rounded-xl p-6 max-w-lg">
        <form method="POST" action="{{ route('admin.couriers.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1">Ism *</label>
                <input name="name" value="{{ old('name') }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Telefon *</label>
                <input name="phone" value="{{ old('phone') }}" placeholder="998901234567" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Transport turi</label>
                <select name="vehicle_type" class="w-full border rounded-lg px-3 py-2">
                    <option value="bike">Velosiped/Mototsikl</option>
                    <option value="car">Avtomobil</option>
                    <option value="foot">Piyoda</option>
                </select>
            </div>
            <p class="text-xs text-gray-400">Kuryer ilovaga SMS OTP orqali kiradi (parol shart emas).</p>
            <div class="flex gap-2 pt-2">
                <button class="bg-brand text-white px-6 py-2.5 rounded-lg">Saqlash</button>
                <a href="{{ route('admin.couriers.index') }}" class="px-6 py-2.5 rounded-lg bg-gray-100">Bekor qilish</a>
            </div>
        </form>
    </div>
@endsection
