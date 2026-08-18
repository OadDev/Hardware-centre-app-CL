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
            'product' => $product->load(['variants', 'priceTiers']),
        ]);
    }

    public function storePriceTier(Request $request, Product $product)
    {
        $this->authorizeOwnership($request, $product);

        $data = $request->validate([
            'min_qty' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $exists = $product->priceTiers()->where('min_qty', $data['min_qty'])->exists();
        abort_if($exists, 422, 'A price tier for this minimum quantity already exists.');

        $product->priceTiers()->create($data);

        return back()->with('status', 'Price tier added.');
    }

    public function destroyPriceTier(Request $request, Product $product, ProductPriceTier $tier)
    {
        $this->authorizeOwnership($request, $product);
        abort_unless($tier->product_id === $product->id, 404);
        $tier->delete();

        return back()->with('status', 'Price tier removed.');
    }

    public function storeVariant(Request $request, Product $product)
    {
        $this->authorizeOwnership($request, $product);
        $data = $this->validatedVariant($request);
        $this->assertConsistentOptionKeys($product, $data['options']);

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

    /**
     * See the identical check in Admin\ProductController — a product's
     * variant picker only works if every variant uses the same attribute
     * names. Only enforced on create; editing an existing (possibly
     * already-mismatched) variant stays unrestricted so a mistake here can
     * still be fixed by hand.
     */
    private function assertConsistentOptionKeys(Product $product, array $newOptions): void
    {
        $existingKeys = $product->variants->flatMap(fn (ProductVariant $v) => array_keys($v->options ?? []))->unique();
        if ($existingKeys->isEmpty()) {
            return;
        }

        $newKeys = collect(array_keys($newOptions));
        if ($newKeys->diff($existingKeys)->isEmpty() && $existingKeys->diff($newKeys)->isEmpty()) {
            return;
        }

        abort(422, 'This product\'s other options use: '.$existingKeys->implode(', ').'. Use the same attribute names for every option so customers can select between them.');
    }

    private function validatedVariant(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        $data = $request->validate([
            'attr_keys' => ['nullable', 'array'],
            'attr_keys.*' => ['nullable', 'string', 'max:60'],
            'attr_values' => ['nullable', 'array'],
            'attr_values.*' => ['nullable', 'string', 'max:60'],
            'sku' => ['nullable', 'string', 'max:60'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => [$required, 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        $keys = $data['attr_keys'] ?? [];
        $values = $data['attr_values'] ?? [];
        $options = [];
        foreach ($keys as $i => $key) {
            $key = trim((string) $key);
            $value = trim((string) ($values[$i] ?? ''));
            if ($key !== '' && $value !== '') {
                $options[$key] = $value;
            }
        }

        if (! $isUpdate) {
            abort_if(empty($options), 422, 'Enter at least one attribute (e.g. Color, Size, Units).');
        }

        unset($data['attr_keys'], $data['attr_values']);
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
