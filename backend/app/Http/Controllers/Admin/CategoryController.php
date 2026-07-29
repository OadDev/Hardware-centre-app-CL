<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            Category::withCount('products')->orderBy('sort_order')->get()->map(fn ($c) => $this->transform($c))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        $category = Category::create([
            'category_code' => 'CAT-'.(100 + Category::count()),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'icon' => Str::slug($data['name']),
            'status' => $data['status'] ?? 'Active',
        ]);

        return response()->json($this->transform($category->loadCount('products')), 201);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        $category->update($data);

        return response()->json($this->transform($category->loadCount('products')));
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }

    public function uploadIcon(Request $request, Category $category)
    {
        $request->validate([
            'icon' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($category->icon_path) {
            Storage::disk('uploads')->delete($category->icon_path);
        }

        $category->update([
            'icon_path' => $request->file('icon')->store('categories', 'uploads'),
            'icon_class' => null,
        ]);

        return response()->json($this->transform($category->loadCount('products')));
    }

    public function setIconClass(Request $request, Category $category)
    {
        $data = $request->validate([
            'icon_class' => ['required', 'string', 'max:60', 'regex:/^[a-zA-Z0-9\- ]+$/'],
        ]);

        if ($category->icon_path) {
            Storage::disk('uploads')->delete($category->icon_path);
        }

        $category->update([
            'icon_path' => null,
            'icon_class' => $data['icon_class'],
        ]);

        return response()->json($this->transform($category->loadCount('products')));
    }

    public function destroyIcon(Category $category)
    {
        if ($category->icon_path) {
            Storage::disk('uploads')->delete($category->icon_path);
        }
        $category->update(['icon_path' => null, 'icon_class' => null]);

        return response()->json($this->transform($category->loadCount('products')));
    }

    private function transform(Category $c): array
    {
        return [
            'id' => $c->category_code,
            'name' => $c->name,
            'products' => $c->products_count,
            'status' => $c->status,
            'iconUrl' => $c->icon_url,
            'iconClass' => $c->icon_class,
        ];
    }
}
