<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parents = Category::root()->get();

        return view('admin.categories.form', ['category' => new Category, 'parents' => $parents]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('ok', 'Kategoriya qo\'shildi.');
    }

    public function edit(Category $category): View
    {
        $parents = Category::root()->where('id', '!=', $category->id)->get();

        return view('admin.categories.form', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validateData($request));

        return redirect()->route('admin.categories.index')->with('ok', 'Yangilandi.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return back()->with('ok', 'O\'chirildi.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ru' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
