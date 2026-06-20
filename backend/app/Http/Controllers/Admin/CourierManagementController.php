<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourierManagementController extends Controller
{
    public function index(): View
    {
        $couriers = User::where('role', 'courier')
            ->withCount(['deliveries as delivered_count' => fn ($q) => $q->where('status', 'delivered')])
            ->latest()
            ->paginate(20);

        return view('admin.couriers.index', compact('couriers'));
    }

    public function create(): View
    {
        return view('admin.couriers.form', ['courier' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'vehicle_type' => ['nullable', 'in:bike,car,foot'],
        ]);

        $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        $data['role'] = 'courier';
        $data['phone_verified_at'] = now();

        User::create($data);

        return redirect()->route('admin.couriers.index')->with('ok', 'Kuryer qo\'shildi.');
    }

    public function toggleActive(User $courier): RedirectResponse
    {
        $courier->update(['is_active' => ! $courier->is_active]);

        return back()->with('ok', 'Holat o\'zgartirildi.');
    }
}
