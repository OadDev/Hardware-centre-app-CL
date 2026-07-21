<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        return response()->json(Coupon::orderByDesc('id')->get());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $coupon = Coupon::create($data);

        return response()->json($coupon, 201);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validated($request, $coupon->id);
        $coupon->update($data);

        return response()->json($coupon);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted']);
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('coupons', 'code')->ignore($id)],
            'type' => ['required', Rule::in(['Percentage', 'Flat'])],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order' => ['required', 'numeric', 'min:0'],
            'usage_limit' => ['required', 'integer', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive', 'Expired'])],
        ]);
    }
}
