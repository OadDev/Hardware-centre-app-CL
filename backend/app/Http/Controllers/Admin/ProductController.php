<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['brand', 'category', 'seller']);

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

        return response()->json($this->transform($product->load(['brand', 'category', 'seller'])), 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, isUpdate: true);
        $data = $this->resolveRelations($data);
        $product->update($data);

        return response()->json($this->transform($product->load(['brand', 'category', 'seller'])));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
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
            'sellerName' => $p->seller->store_name,
            'price' => (float) $p->price,
            'mrp' => (float) $p->mrp,
            'stock' => $p->stock,
            'stockStatus' => $p->stock_status,
            'approval' => $p->approval_status,
            'featured' => (bool) $p->featured,
            'rating' => (float) $p->rating,
            'sold' => $p->sold_count,
        ];
    }
}
