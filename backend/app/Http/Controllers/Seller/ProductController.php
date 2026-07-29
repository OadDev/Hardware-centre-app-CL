<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user()->seller;

        $products = $seller->products()
            ->with(['brand', 'category', 'images', 'variants'])
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

    public function variants(Request $request, Product $product)
    {
        $this->authorizeOwnership($request, $product);

        return view('seller.product-variants', [
            'seller' => $request->user()->seller,
            'product' => $product->load('variants'),
        ]);
    }

    public function storeVariant(Request $request, Product $product)
    {
        $this->authorizeOwnership($request, $product);
        $data = $this->validatedVariant($request);

        $product->variants()->create([
            'variant_code' => 'VAR-'.strtoupper(Str::random(8)),
            'sku' => ($data['sku'] ?? null) ?: 'SKU'.strtoupper(Str::random(6)),
            'options' => $data['options'],
            'price' => $data['price'] ?? null,
            'stock' => $data['stock'],
        ]);

        return back()->with('status', 'Variant added.');
    }

    public function updateVariant(Request $request, Product $product, ProductVariant $variant)
    {
        $this->authorizeOwnership($request, $product);
        abort_unless($variant->product_id === $product->id, 404);

        $data = $this->validatedVariant($request, isUpdate: true);
        if (array_key_exists('sku', $data) && $data['sku'] === '') {
            unset($data['sku']);
        }
        $variant->update($data);

        return back()->with('status', 'Variant updated.');
    }

    public function destroyVariant(Request $request, Product $product, ProductVariant $variant)
    {
        $this->authorizeOwnership($request, $product);
        abort_unless($variant->product_id === $product->id, 404);
        $variant->delete();

        return back()->with('status', 'Variant removed.');
    }

    private function validatedVariant(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        $data = $request->validate([
            'color' => ['nullable', 'string', 'max:60'],
            'size' => ['nullable', 'string', 'max:60'],
            'sku' => ['nullable', 'string', 'max:60'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => [$required, 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        $options = array_filter([
            'Color' => $data['color'] ?? null,
            'Size' => $data['size'] ?? null,
        ]);

        if (! $isUpdate) {
            abort_if(empty($options), 422, 'Enter at least a color or size for the variant.');
        }

        unset($data['color'], $data['size']);
        if (! $isUpdate || ! empty($options)) {
            $data['options'] = $options;
        }

        return $data;
    }

    private function authorizeOwnership(Request $request, Product $product): void
    {
        abort_unless($product->seller_id === $request->user()->seller->id, 403);
    }
}
