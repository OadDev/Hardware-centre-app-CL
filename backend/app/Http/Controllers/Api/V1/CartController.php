<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
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
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ]);

        $product = Product::where('product_code', $data['product_code'])->firstOrFail();
        $qty = $data['quantity'] ?? 1;

        $item = $request->user()->cartItems()->firstOrNew(['product_id' => $product->id]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $qty;
        $item->save();

        return $this->cartResponse($request);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $item = $request->user()->cartItems()->where('product_id', $product->id)->first();

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
        $request->user()->cartItems()->where('product_id', $product->id)->delete();

        return $this->cartResponse($request);
    }

    private function cartResponse(Request $request)
    {
        $items = $request->user()->cartItems()->with(['product.brand', 'product.category', 'product.seller', 'product.specs'])->get();
        $totals = CartCalculator::totals($items);

        return response()->json([
            'items' => $items->map(fn ($item) => [
                'product' => new ProductResource($item->product),
                'quantity' => $item->quantity,
                'line_total' => (float) $item->product->price * $item->quantity,
            ]),
            'totals' => $totals,
        ]);
    }
}
