<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPriceTier;
use App\Models\ProductVariant;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['brand', 'category', 'seller', 'images', 'variants', 'priceTiers']);

        if ($search = $request->query('q')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($category = $request->query('category')) {
            $query->whereHas('category', fn ($c) => $c->where('name', $category));
        }
        if ($approval = $request->query('approval')) {
            $query->where('approval_status', $approval);
        }

        $products = $query->orderByDesc('id')->paginate($request->integer('per_page', 20));
        $products->getCollection()->transform(fn (Product $p) => $this->transform($p));

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data = $this->resolveRelations($data);

        $product = Product::create([
            ...$data,
            'product_code' => 'PRD-'.(10000 + Product::count()),
            'sku' => 'SKU'.strtoupper(Str::random(6)),
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
        ]);

        return response()->json($this->transform($product->load(['brand', 'category', 'seller', 'images', 'variants', 'priceTiers'])), 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, isUpdate: true);
        $data = $this->resolveRelations($data);
        $product->update($data);

        return response()->json($this->transform($product->load(['brand', 'category', 'seller', 'images', 'variants', 'priceTiers'])));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }

    public function storeImages(Request $request, Product $product)
    {
        $request->validate([
            'images' => ['required', 'array', 'max:8'],
            'images.*' => ['image', 'max:5120'],
        ]);

        ProductImage::storeUploads($product, $request->file('images', []));

        return response()->json($this->transform($product->load(['brand', 'category', 'seller', 'images', 'variants', 'priceTiers'])), 201);
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('uploads')->delete($image->path);
        $image->delete();

        return response()->json(['message' => 'Image removed']);
    }

    public function storeVariant(Request $request, Product $product)
    {
        $data = $this->validatedVariant($request);
        $this->assertConsistentOptionKeys($product, $data['options']);

        $variant = $product->variants()->create([
            'variant_code' => 'VAR-'.strtoupper(Str::random(8)),
            'sku' => ($data['sku'] ?? null) ?: 'SKU'.strtoupper(Str::random(6)),
            'options' => $data['options'],
            'price' => $data['price'] ?? null,
            'stock' => $data['stock'],
            'status' => $data['status'] ?? 'Active',
        ]);

        return response()->json($this->transform($product->load(['brand', 'category', 'seller', 'images', 'variants', 'priceTiers'])), 201);
    }

    public function updateVariant(Request $request, Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id === $product->id, 404);

        $data = $this->validatedVariant($request, isUpdate: true);
        if (array_key_exists('sku', $data) && $data['sku'] === '') {
            unset($data['sku']);
        }
        $variant->update($data);

        return response()->json($this->transform($product->load(['brand', 'category', 'seller', 'images', 'variants', 'priceTiers'])));
    }

    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id === $product->id, 404);
        $variant->delete();

        return response()->json($this->transform($product->load(['brand', 'category', 'seller', 'images', 'variants', 'priceTiers'])));
    }

    /**
     * A product's variant options only work as a picker if every variant
     * uses the exact same attribute names (e.g. all "Color"+"Size", never
     * one variant keyed "Color" and another keyed by a color name itself)
     * — otherwise the storefront can't match a selection to a variant and
     * the picker is effectively broken. Only enforced on create; editing an
     * existing (possibly already-mismatched) variant stays unrestricted so
     * a mistake here can still be fixed by hand.
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
            'options' => [$required, 'array'],
            'options.*' => ['nullable', 'string', 'max:60'],
            'sku' => ['nullable', 'string', 'max:60'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => [$required, 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        if (array_key_exists('options', $data)) {
            $data['options'] = array_filter($data['options'], fn ($v) => $v !== null && $v !== '');

            if (! $isUpdate) {
                abort_if(empty($data['options']), 422, 'Enter at least one attribute (e.g. Color, Size, Units).');
            }
        }

        return $data;
    }

    private function validated(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'max:255'],
            'category_code' => [$required, 'exists:categories,category_code'],
            'brand_code' => [$required, 'exists:brands,brand_code'],
            'seller_code' => [$required, 'exists:sellers,seller_code'],
            'price' => [$required, 'numeric', 'min:0'],
            'mrp' => [$required, 'numeric', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:30'],
            'stock' => [$required, 'integer', 'min:0'],
            'approval_status' => ['sometimes', Rule::in(['Approved', 'Pending', 'Rejected'])],
            'featured' => ['sometimes', 'boolean'],
            'description' => ['sometimes', 'nullable', 'string'],
            'video_url' => ['sometimes', 'nullable', 'url', 'max:255'],
        ]);
    }

    private function resolveRelations(array $data): array
    {
        if (isset($data['category_code'])) {
            $data['category_id'] = Category::where('category_code', $data['category_code'])->value('id');
            unset($data['category_code']);
        }
        if (isset($data['brand_code'])) {
            $data['brand_id'] = Brand::where('brand_code', $data['brand_code'])->value('id');
            unset($data['brand_code']);
        }
        if (isset($data['seller_code'])) {
            $data['seller_id'] = Seller::where('seller_code', $data['seller_code'])->value('id');
            unset($data['seller_code']);
        }

        return $data;
    }

    private function transform(Product $p): array
    {
        return [
            'id' => $p->product_code,
            'name' => $p->name,
            'category' => $p->category->name,
            'categoryCode' => $p->category->category_code,
            'sellerName' => $p->seller->store_name,
            'sellerCode' => $p->seller->seller_code,
            'brand' => $p->brand->name,
            'brandCode' => $p->brand->brand_code,
            'unit' => $p->unit,
            'description' => $p->description,
            'price' => (float) $p->price,
            'mrp' => (float) $p->mrp,
            'stock' => $p->stock,
            'stockStatus' => $p->stock_status,
            'approval' => $p->approval_status,
            'featured' => (bool) $p->featured,
            'rating' => (float) $p->rating,
            'sold' => $p->sold_count,
            'images' => $p->images->map(fn (ProductImage $img) => ['id' => $img->id, 'url' => $img->url])->all(),
            'videoUrl' => $p->video_url,
            'variants' => $p->variants->map(fn (ProductVariant $v) => [
                'id' => $v->variant_code,
                'sku' => $v->sku,
                'attributes' => $v->options,
                'label' => $v->label,
                'price' => $v->price !== null ? (float) $v->price : null,
                'stock' => $v->stock,
                'status' => $v->status,
            ])->all(),
            'priceTiers' => $p->priceTiers->map(fn (ProductPriceTier $t) => [
                'id' => $t->id,
                'minQty' => $t->min_qty,
                'price' => (float) $t->price,
            ])->all(),
        ];
    }

    public function storePriceTier(Request $request, Product $product)
    {
        $data = $this->validatedPriceTier($request);

        $exists = $product->priceTiers()->where('min_qty', $data['min_qty'])->exists();
        abort_if($exists, 422, 'A price tier for this minimum quantity already exists.');

        $product->priceTiers()->create($data);

        return response()->json($this->transform($product->load(['brand', 'category', 'seller', 'images', 'variants', 'priceTiers'])), 201);
    }

    public function destroyPriceTier(Product $product, ProductPriceTier $tier)
    {
        abort_unless($tier->product_id === $product->id, 404);
        $tier->delete();

        return response()->json($this->transform($product->load(['brand', 'category', 'seller', 'images', 'variants', 'priceTiers'])));
    }

    private function validatedPriceTier(Request $request): array
    {
        return $request->validate([
            'min_qty' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
