<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user()->seller;

        $products = $seller->products()
            ->with(['brand', 'category', 'images'])
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
            'video_url' => ['nullable', 'url', 'max:255'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $data['category_id'] = Category::where('category_code', $data['category_code'])->value('id');
        $data['brand_id'] = Brand::where('brand_code', $data['brand_code'])->value('id');
        unset($data['category_code'], $data['brand_code']);
        $images = $data['images'] ?? [];
        unset($data['images']);

        $product = $seller->products()->create([
            ...$data,
            'product_code' => 'PRD-'.(10000 + Product::count()),
            'sku' => 'SKU'.strtoupper(Str::random(6)),
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'approval_status' => 'Pending',
        ]);

        ProductImage::storeUploads($product, $images);

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
            'video_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'images' => ['sometimes', 'nullable', 'array', 'max:8'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $images = $data['images'] ?? [];
        unset($data['images']);

        $product->update($data);
        ProductImage::storeUploads($product, $images);

        return back()->with('status', 'Product updated.');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeOwnership($request, $product);
        $product->delete();

        return back()->with('status', 'Product removed.');
    }

    public function destroyImage(Request $request, Product $product, ProductImage $image)
    {
        $this->authorizeOwnership($request, $product);
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('uploads')->delete($image->path);
        $image->delete();

        return back()->with('status', 'Image removed.');
    }

    private function authorizeOwnership(Request $request, Product $product): void
    {
        abort_unless($product->seller_id === $request->user()->seller->id, 403);
    }
}
