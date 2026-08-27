<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPriceTier;
use App\Models\ProductSpec;
use App\Models\ProductVariant;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private const WITH = ['brand', 'category', 'seller', 'images', 'variants.priceTiers', 'priceTiers', 'specs'];

    public function index(Request $request)
    {
        $query = Product::query()->with(self::WITH);

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

        if (empty($data['weight_kg'])) {
            $data['weight_kg'] = 0.5;
        }

        try {
            $product = Product::create([
                ...$data,
                'product_code' => Product::nextProductCode(),
                'sku' => 'SKU'.strtoupper(Str::random(6)),
                'slug' => Str::slug($data['name']).'-'.Str::random(4),
            ]);

            return response()->json($this->transform($product->load(self::WITH)), 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not create product: '.$e->getMessage()], 500);
        }
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, isUpdate: true);
        $data = $this->resolveRelations($data);

        if (array_key_exists('weight_kg', $data) && empty($data['weight_kg'])) {
            unset($data['weight_kg']);
        }

        try {
            $product->update($data);

            return response()->json($this->transform($product->load(self::WITH)));
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not update product: '.$e->getMessage()], 500);
        }
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

        try {
            ProductImage::storeUploads($product, $request->file('images', []));

            return response()->json($this->transform($product->load(self::WITH)), 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not upload photos: '.$e->getMessage()], 500);
        }
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

        $variant = $product->variants()->create([
            'name' => $data['name'],
            'variant_code' => 'VAR-'.strtoupper(Str::random(8)),
            'sku' => ($data['sku'] ?? null) ?: 'SKU'.strtoupper(Str::random(6)),
            'options' => [],
            'price' => $data['price'] ?? null,
            'stock' => $data['stock'],
            'status' => $data['status'] ?? 'Active',
            'image_path' => $request->hasFile('image') ? $request->file('image')->store('variants', 'uploads') : null,
        ]);

        return response()->json($this->transform($product->load(self::WITH)), 201);
    }

    public function updateVariant(Request $request, Product $product, ProductVariant $variant)
    {
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

        return response()->json($this->transform($product->load(self::WITH)));
    }

    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id === $product->id, 404);
        if ($variant->image_path) {
            Storage::disk('uploads')->delete($variant->image_path);
        }
        $variant->delete();

        return response()->json($this->transform($product->load(self::WITH)));
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
            'weight_kg' => ['sometimes', 'nullable', 'numeric', 'min:0.001', 'max:1000'],
            'approval_status' => ['sometimes', Rule::in(['Approved', 'Pending', 'Rejected'])],
            'featured' => ['sometimes', 'boolean'],
            'description' => ['sometimes', 'nullable', 'string'],
            'video_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'apply_bulk_pricing_to_all_variants' => ['sometimes', 'boolean'],
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
            'weightKg' => (float) $p->weight_kg,
            'stockStatus' => $p->stock_status,
            'approval' => $p->approval_status,
            'featured' => (bool) $p->featured,
            'rating' => (float) $p->rating,
            'sold' => $p->sold_count,
            'images' => $p->images->map(fn (ProductImage $img) => ['id' => $img->id, 'url' => $img->url])->all(),
            'videoUrl' => $p->video_url,
            'applyBulkPricingToAllVariants' => (bool) $p->apply_bulk_pricing_to_all_variants,
            'variants' => $p->variants->map(fn (ProductVariant $v) => [
                'id' => $v->variant_code,
                'name' => $v->label,
                'sku' => $v->sku,
                'imageUrl' => $v->image_url,
                'price' => $v->price !== null ? (float) $v->price : null,
                'stock' => $v->stock,
                'status' => $v->status,
                'priceTiers' => $v->priceTiers->map(fn (ProductPriceTier $t) => [
                    'id' => $t->id,
                    'minQty' => $t->min_qty,
                    'price' => (float) $t->price,
                ])->all(),
            ])->all(),
            'priceTiers' => $p->priceTiers->map(fn (ProductPriceTier $t) => [
                'id' => $t->id,
                'minQty' => $t->min_qty,
                'price' => (float) $t->price,
            ])->all(),
            'specs' => $p->specs->sortBy('sort_order')->values()->map(fn (ProductSpec $s) => [
                'id' => $s->id,
                'label' => $s->label,
                'value' => $s->value,
            ])->all(),
        ];
    }

    public function storeSpec(Request $request, Product $product)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'value' => ['required', 'string', 'max:255'],
        ]);

        $nextSort = (int) $product->specs()->max('sort_order');
        $product->specs()->create([...$data, 'sort_order' => $nextSort + 1]);

        return response()->json($this->transform($product->load(self::WITH)), 201);
    }

    public function destroySpec(Product $product, ProductSpec $spec)
    {
        abort_unless($spec->product_id === $product->id, 404);
        $spec->delete();

        return response()->json($this->transform($product->load(self::WITH)));
    }

    public function storePriceTier(Request $request, Product $product)
    {
        $data = $this->validatedPriceTier($request);
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

        return response()->json($this->transform($product->load(self::WITH)), 201);
    }

    public function destroyPriceTier(Product $product, ProductPriceTier $tier)
    {
        abort_unless($tier->product_id === $product->id, 404);
        $tier->delete();

        return response()->json($this->transform($product->load(self::WITH)));
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

    private function validatedPriceTier(Request $request): array
    {
        return $request->validate([
            'min_qty' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'variant_id' => ['sometimes', 'nullable', 'string'],
        ]);
    }
}
