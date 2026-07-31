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
            Category::with('parent')->withCount('products')->orderBy('sort_order')->get()->map(fn ($c) => $this->transform($c))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'parent_code' => ['nullable', 'string', 'exists:categories,category_code'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        $category = Category::create([
            'parent_id' => $this->resolveParentId($data),
            'category_code' => 'CAT-'.(100 + Category::count()),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'icon' => Str::slug($data['name']),
            'status' => $data['status'] ?? 'Active',
        ]);

        return response()->json($this->transform($category->loadCount('products')->load('parent')), 201);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'parent_code' => ['sometimes', 'nullable', 'string', 'exists:categories,category_code'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        if (array_key_exists('parent_code', $data)) {
            $parentId = $this->resolveParentId($data);
            abort_if($parentId === $category->id, 422, 'A category cannot be its own parent.');
            abort_if($parentId && in_array($parentId, $category->children()->pluck('id')->all(), true), 422, 'A subcategory cannot become the parent of its own parent.');
            $data['parent_id'] = $parentId;
        }
        unset($data['parent_code']);

        $category->update($data);

        return response()->json($this->transform($category->loadCount('products')->load('parent')));
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

        return response()->json($this->transform($category->loadCount('products')->load('parent')));
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

        return response()->json($this->transform($category->loadCount('products')->load('parent')));
    }

    public function destroyIcon(Category $category)
    {
        if ($category->icon_path) {
            Storage::disk('uploads')->delete($category->icon_path);
        }
        $category->update(['icon_path' => null, 'icon_class' => null]);

        return response()->json($this->transform($category->loadCount('products')->load('parent')));
    }

    private function resolveParentId(array $data): ?int
    {
        if (empty($data['parent_code'])) {
            return null;
        }

        return Category::where('category_code', $data['parent_code'])->value('id');
    }

    private function transform(Category $c): array
    {
        return [
            'id' => $c->category_code,
            'name' => $c->name,
            'parentId' => $c->parent?->category_code,
            'parentName' => $c->parent?->name,
            'products' => $c->products_count,
            'status' => $c->status,
            'iconUrl' => $c->icon_url,
            'iconClass' => $c->icon_class,
        ];
    }
}
