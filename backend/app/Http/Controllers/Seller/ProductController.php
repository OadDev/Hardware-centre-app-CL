<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPriceTier;
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
            'categories' => Category::topLevel()->where('status', 'Active')->orderBy('name')->with([
                'children' => fn ($q) => $q->where('status', 'Active')->orderBy('name'),
            ])->get(),
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
            'weight_kg' => ['sometimes', 'numeric', 'min:0.001', 'max:1000'],
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
            'weight_kg' => ['sometimes', 'numeric', 'min:0.001', 'max:1000'],
            'description' => ['sometimes', 'nullable', 'string'],
            'video_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'apply_bulk_pricing_to_all_variants' => ['sometimes', 'boolean'],
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
            'product' => $product->load(['variants.priceTiers', 'priceTiers']),
        ]);
    }

    public function storePriceTier(Request $request, Product $product)
    {
        $this->authorizeOwnership($request, $product);

        $data = $request->validate([
            'min_qty' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'variant_id' => ['sometimes', 'nullable', 'string'],
        ]);

        $variant = $this->resolveTierVariant($product, $data['variant_id'] ?? null);

        $exists = ProductPriceTier::where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->where('min_qty', $data['min_qty'])
            ->exists();
        abort_if($exists, 422, 'A price tier for this minimum quantity already exists.');

        $product->allPriceTiers()->create([
            'product_variant_id' => $variant?->id,
            'min_qty' => $data['min_qty'],
            'price' => $data['price'],
        ]);

        return back()->with('status', 'Price tier added.');
    }

    public function destroyPriceTier(Request $request, Product $product, ProductPriceTier $tier)
    {
        $this->authorizeOwnership($request, $product);
        abort_unless($tier->product_id === $product->id, 404);
        $tier->delete();

        return back()->with('status', 'Price tier removed.');
    }

    private function resolveTierVariant(Product $product, ?string $variantCode): ?ProductVariant
    {
        if (! $variantCode) {
            return null;
        }

        $variant = $product->variants->firstWhere('variant_code', $variantCode);
        abort_unless($variant, 404, 'Variant not found on this product.');

        return $variant;
    }

    public function storeVariant(Request $request, Product $product)
    {
        $this->authorizeOwnership($request, $product);
        $data = $this->validatedVariant($request);

        $product->variants()->create([
            'name' => $data['name'],
            'variant_code' => 'VAR-'.strtoupper(Str::random(8)),
            'sku' => ($data['sku'] ?? null) ?: 'SKU'.strtoupper(Str::random(6)),
            'options' => [],
            'price' => $data['price'] ?? null,
            'stock' => $data['stock'],
            'image_path' => $request->hasFile('image') ? $request->file('image')->store('variants', 'uploads') : null,
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

        if ($request->hasFile('image')) {
            if ($variant->image_path) {
                Storage::disk('uploads')->delete($variant->image_path);
            }
            $data['image_path'] = $request->file('image')->store('variants', 'uploads');
        } elseif ($request->boolean('remove_image')) {
            if ($variant->image_path) {
                Storage::disk('uploads')->delete($variant->image_path);
            }
            $data['image_path'] = null;
        }

        $variant->update($data);

        return back()->with('status', 'Variant updated.');
    }

    public function destroyVariant(Request $request, Product $product, ProductVariant $variant)
    {
        $this->authorizeOwnership($request, $product);
        abort_unless($variant->product_id === $product->id, 404);
        if ($variant->image_path) {
            Storage::disk('uploads')->delete($variant->image_path);
        }
        $variant->delete();

        return back()->with('status', 'Variant removed.');
    }

    private function validatedVariant(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:60'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => [$required, 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
            'image' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
        ]);
    }

    private function authorizeOwnership(Request $request, Product $product): void
    {
        abort_unless($product->seller_id === $request->user()->seller->id, 403);
    }
}
