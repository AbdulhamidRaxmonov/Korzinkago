<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(5);
        $data['image'] = $this->resolveImage($request, null) ?? $data['image'] ?? null;

        Product::create($data);

        return redirect()->route('admin.products.index')->with('ok', 'Mahsulot qo\'shildi.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateData($request);

        $uploaded = $this->resolveImage($request, $product);
        if ($uploaded !== null) {
            $data['image'] = $uploaded;
        } elseif (empty($data['image'])) {
            // Yangi rasm yoki URL berilmagan bo'lsa, mavjudini saqlab qolamiz
            unset($data['image']);
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('ok', 'Yangilandi.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Yuklangan rasmni o'chirish (URL bo'lsa tegmaymiz)
        $raw = $product->getRawOriginal('image');
        if ($raw && ! Str::startsWith($raw, 'http')) {
            Storage::disk('public')->delete($raw);
        }

        $product->delete();

        return back()->with('ok', 'O\'chirildi.');
    }

    /**
     * Rasm faylini yuklash. Yuklangan bo'lsa saqlangan yo'lni qaytaradi.
     */
    protected function resolveImage(Request $request, ?Product $product): ?string
    {
        if (! $request->hasFile('image_file')) {
            return null;
        }

        // Eski yuklangan rasmni o'chirish
        if ($product) {
            $raw = $product->getRawOriginal('image');
            if ($raw && ! Str::startsWith($raw, 'http')) {
                Storage::disk('public')->delete($raw);
            }
        }

        return $request->file('image_file')->store('products', 'public');
    }

    protected function validateData(Request $request): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ru' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'url'],
            'image_file' => ['nullable', 'image', 'max:4096'], // 4MB gacha
            'price' => ['required', 'numeric', 'min:0'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'step' => ['nullable', 'numeric', 'min:0.001'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        unset($validated['image_file']);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        return $validated;
    }
}
