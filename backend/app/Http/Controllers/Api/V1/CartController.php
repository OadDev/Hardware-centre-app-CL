<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\CartCalculator;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        return $this->cartResponse($request);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_code' => ['required', 'string', 'exists:products,product_code'],
            'variant_code' => ['nullable', 'string'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ]);

        $product = Product::where('product_code', $data['product_code'])->firstOrFail();
        $variant = $this->resolveVariant($product, $data['variant_code'] ?? null);
        $qty = $data['quantity'] ?? 1;

        $item = $request->user()->cartItems()->firstOrNew([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
        ]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $qty;
        $item->save();

        return $this->cartResponse($request);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $variant = $this->resolveVariant($product, $request->query('variant'), required: false);
        $item = $request->user()->cartItems()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($item) {
            if ($data['quantity'] <= 0) {
                $item->delete();
            } else {
                $item->update(['quantity' => $data['quantity']]);
            }
        }

        return $this->cartResponse($request);
    }

    public function destroy(Request $request, Product $product)
    {
        $variant = $this->resolveVariant($product, $request->query('variant'), required: false);
        $request->user()->cartItems()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->delete();

        return $this->cartResponse($request);
    }

    private function resolveVariant(Product $product, ?string $variantCode, bool $required = true): ?ProductVariant
    {
        $hasVariants = $product->variants()->where('status', 'Active')->exists();

        if (! $variantCode) {
            abort_if($required && $hasVariants, 422, 'Please select an option before adding this product to your cart.');

            return null;
        }

        $variant = $product->variants()->where('variant_code', $variantCode)->first();
        abort_if(! $variant, 422, 'That option is no longer available.');

        return $variant;
    }

    private function cartResponse(Request $request)
    {
        $items = $request->user()->cartItems()
            ->with(['product.brand', 'product.category', 'product.seller', 'product.specs', 'product.priceTiers', 'productVariant'])
            ->get();
        $totals = CartCalculator::totals($items);

        return response()->json([
            'items' => $items->map(fn ($item) => [
                'product' => new ProductResource($item->product),
                'variant' => $item->productVariant ? [
                    'id' => $item->productVariant->variant_code,
                    'label' => $item->productVariant->label,
                    'price' => $item->productVariant->effective_price,
                    'stock' => $item->productVariant->stock,
                ] : null,
                'quantity' => $item->quantity,
                'line_total' => $item->unit_price * $item->quantity,
            ]),
            'totals' => $totals,
        ]);
    }
}
