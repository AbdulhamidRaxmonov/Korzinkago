@props(['status'])
@php
$map = [
    'new' => ['Yangi', 'bg-blue-100 text-blue-700'],
    'accepted' => ['Qabul qilindi', 'bg-indigo-100 text-indigo-700'],
    'assembling' => ['Yig\'ilmoqda', 'bg-yellow-100 text-yellow-700'],
    'ready' => ['Tayyor', 'bg-cyan-100 text-cyan-700'],
    'on_way' => ['Yo\'lda', 'bg-orange-100 text-orange-700'],
    'delivered' => ['Yetkazildi', 'bg-green-100 text-green-700'],
    'cancelled' => ['Bekor qilindi', 'bg-red-100 text-red-700'],
];
[$label, $color] = $map[$status] ?? [$status, 'bg-gray-100 text-gray-700'];
@endphp
<span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">{{ $label }}</span>
