<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        return response()->json(
            Brand::withCount('products')->orderBy('name')->get()->map(fn ($b) => $this->transform($b))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'featured' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        $brand = Brand::create([
            'brand_code' => 'BRD-'.(200 + Brand::count()),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'featured' => $data['featured'] ?? false,
            'status' => $data['status'] ?? 'Active',
        ]);

        return response()->json($this->transform($brand->loadCount('products')), 201);
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'featured' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        $brand->update($data);

        return response()->json($this->transform($brand->loadCount('products')));
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return response()->json(['message' => 'Brand deleted']);
    }

    private function transform(Brand $b): array
    {
        return [
            'id' => $b->brand_code,
            'name' => $b->name,
            'products' => $b->products_count,
            'featured' => (bool) $b->featured,
            'status' => $b->status,
        ];
    }
}
