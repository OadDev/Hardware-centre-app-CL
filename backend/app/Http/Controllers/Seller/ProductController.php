<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user()->seller;

        $products = $seller->products()
            ->with(['brand', 'category'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('seller.products', [
            'seller' => $seller,
            'products' => $products,
            'categories' => Category::where('status', 'Active')->orderBy('name')->get(),
            'brands' => Brand::where('status', 'Active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $seller = $request->user()->seller;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_code' => ['required', 'exists:categories,category_code'],
            'brand_code' => ['required', 'exists:brands,brand_code'],
            'price' => ['required', 'numeric', 'min:0'],
            'mrp' => ['required', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:30'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $data['category_id'] = Category::where('category_code', $data['category_code'])->value('id');
        $data['brand_id'] = Brand::where('brand_code', $data['brand_code'])->value('id');
        unset($data['category_code'], $data['brand_code']);

        $seller->products()->create([
            ...$data,
            'product_code' => 'PRD-'.(10000 + Product::count()),
            'sku' => 'SKU'.strtoupper(Str::random(6)),
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'approval_status' => 'Pending',
        ]);

        return back()->with('status', 'Product submitted for approval.');
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeOwnership($request, $product);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'mrp' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $product->update($data);

        return back()->with('status', 'Product updated.');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeOwnership($request, $product);
        $product->delete();

        return back()->with('status', 'Product removed.');
    }

    private function authorizeOwnership(Request $request, Product $product): void
    {
        abort_unless($product->seller_id === $request->user()->seller->id, 403);
    }
}
