<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Korzinkago Admin — @yield('title', 'Boshqaruv')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: '#00A046' } } } }
    </script>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="w-64 bg-white border-r flex flex-col">
        <div class="p-5 border-b">
            <span class="text-xl font-bold text-brand">🛒 Korzinkago</span>
            <p class="text-xs text-gray-400">Admin panel</p>
        </div>
        <nav class="flex-1 p-3 space-y-1 text-sm">
            @php $nav = [
                ['admin.dashboard', '📊 Boshqaruv paneli'],
                ['admin.orders.index', '📦 Buyurtmalar'],
                ['admin.products.index', '🏷️ Mahsulotlar'],
                ['admin.categories.index', '📁 Kategoriyalar'],
                ['admin.couriers.index', '🛵 Kuryerlar'],
            ]; @endphp
            @foreach ($nav as [$route, $label])
                <a href="{{ route($route) }}"
                   class="block px-4 py-2.5 rounded-lg {{ request()->routeIs(Str::before($route, '.index').'*') || request()->routeIs($route) ? 'bg-brand text-white' : 'hover:bg-gray-100' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('admin.logout') }}" class="p-3 border-t">
            @csrf
            <button class="w-full text-left px-4 py-2.5 rounded-lg text-red-600 hover:bg-red-50 text-sm">
                🚪 Chiqish
            </button>
        </form>
    </aside>

    {{-- Main --}}
    <main class="flex-1 p-8 overflow-auto">
        <h1 class="text-2xl font-bold mb-6">@yield('title')</h1>

        @if (session('ok'))
            <div class="mb-4 px-4 py-3 bg-green-100 text-green-700 rounded-lg">{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
