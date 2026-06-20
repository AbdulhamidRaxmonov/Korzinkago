@extends('admin.layout')
@section('title', 'Hisobotlar')

@section('content')
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Tushum grafigi --}}
        <div class="lg:col-span-2 bg-white rounded-xl p-5">
            <h3 class="font-semibold mb-4">So'nggi 30 kun — tushum (so'm)</h3>
            <canvas id="revenueChart" height="110"></canvas>
        </div>

        {{-- Holatlar doiraviy --}}
        <div class="bg-white rounded-xl p-5">
            <h3 class="font-semibold mb-4">Buyurtma holatlari</h3>
            <canvas id="statusChart"></canvas>
        </div>

        {{-- Buyurtmalar soni --}}
        <div class="lg:col-span-2 bg-white rounded-xl p-5">
            <h3 class="font-semibold mb-4">Kunlik buyurtmalar soni</h3>
            <canvas id="ordersChart" height="110"></canvas>
        </div>

        {{-- To'lov usullari --}}
        <div class="bg-white rounded-xl p-5">
            <h3 class="font-semibold mb-4">To'lov usullari</h3>
            <canvas id="paymentChart"></canvas>
        </div>
    </div>

    {{-- Top mahsulotlar --}}
    <div class="bg-white rounded-xl p-5 mt-6">
        <h3 class="font-semibold mb-4">Eng ko'p sotilgan mahsulotlar</h3>
        <table class="w-full text-sm">
            <thead class="text-gray-500 text-left border-b">
                <tr><th class="py-2">#</th><th class="py-2">Mahsulot</th><th class="py-2">Soni</th><th class="py-2">Tushum</th></tr>
            </thead>
            <tbody>
                @foreach ($topProducts as $i => $p)
                    <tr class="border-b">
                        <td class="py-2">{{ $i + 1 }}</td>
                        <td class="py-2">{{ $p->product_name }}</td>
                        <td class="py-2">{{ rtrim(rtrim((string) $p->qty, '0'), '.') }}</td>
                        <td class="py-2">{{ number_format($p->revenue) }} so'm</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const green = '#00A046';
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: 'Tushum',
                    data: @json($revenueData),
                    borderColor: green,
                    backgroundColor: 'rgba(0,160,70,0.1)',
                    fill: true, tension: 0.3,
                }]
            },
            options: { plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('ordersChart'), {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{ label: 'Buyurtmalar', data: @json($ordersData), backgroundColor: green }]
            },
            options: { plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: @json($statusCounts->keys()),
                datasets: [{ data: @json($statusCounts->values()),
                    backgroundColor: ['#3b82f6','#6366f1','#eab308','#06b6d4','#f97316','#22c55e','#ef4444'] }]
            }
        });

        new Chart(document.getElementById('paymentChart'), {
            type: 'pie',
            data: {
                labels: @json($paymentMethods->keys()),
                datasets: [{ data: @json($paymentMethods->values()),
                    backgroundColor: ['#22c55e','#3b82f6','#f97316','#a855f7'] }]
            }
        });
    </script>
@endsection
