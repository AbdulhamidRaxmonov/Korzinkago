<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Korzinkago Admin — Kirish</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-sm w-full max-w-sm">
        <div class="text-center mb-6">
            <div class="text-3xl">🛒</div>
            <h1 class="text-xl font-bold mt-2">Korzinkago Admin</h1>
            <p class="text-sm text-gray-400">Boshqaruv paneliga kirish</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1">Telefon</label>
                <input name="phone" value="{{ old('phone') }}" placeholder="998900000000"
                       class="w-full border rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            <div>
                <label class="block text-sm mb-1">Parol</label>
                <input type="password" name="password" placeholder="••••••••"
                       class="w-full border rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            <button class="w-full bg-[#00A046] text-white py-2.5 rounded-lg font-medium hover:bg-green-700">
                Kirish
            </button>
        </form>
        <p class="text-xs text-gray-400 text-center mt-4">Demo: 998900000000 / admin123</p>
    </div>
</body>
</html>
