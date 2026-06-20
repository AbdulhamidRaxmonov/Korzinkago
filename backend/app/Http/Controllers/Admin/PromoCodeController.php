<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(): View
    {
        $promos = PromoCode::latest()->paginate(20);

        return view('admin.promos.index', compact('promos'));
    }

    public function create(): View
    {
        return view('admin.promos.form', ['promo' => new PromoCode]);
    }

    public function store(Request $request): RedirectResponse
    {
        PromoCode::create($this->validateData($request));

        return redirect()->route('admin.promos.index')->with('ok', 'Promokod qo\'shildi.');
    }

    public function edit(PromoCode $promo): View
    {
        return view('admin.promos.form', compact('promo'));
    }

    public function update(Request $request, PromoCode $promo): RedirectResponse
    {
        $promo->update($this->validateData($request, $promo->id));

        return redirect()->route('admin.promos.index')->with('ok', 'Yangilandi.');
    }

    public function destroy(PromoCode $promo): RedirectResponse
    {
        $promo->delete();

        return back()->with('ok', 'O\'chirildi.');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:promo_codes,code'.($id ? ",{$id}" : '')],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['min_order'] = $data['min_order'] ?? 0;
        $data['per_user_limit'] = $data['per_user_limit'] ?? 1;
        $data['first_order_only'] = $request->boolean('first_order_only');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
