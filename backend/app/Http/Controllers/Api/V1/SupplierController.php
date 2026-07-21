<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SellerResource;
use App\Models\Seller;

class SupplierController extends Controller
{
    public function __invoke()
    {
        return SellerResource::collection(
            Seller::where('account_status', 'Active')->orderByDesc('rating')->limit(10)->get()
        );
    }
}
