<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return AddressResource::collection($request->user()->addresses()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:12'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create([
            'label' => $data['label'] ?? 'Home',
            'contact_name' => $data['name'],
            'line' => $data['line'],
            'city' => $data['city'],
            'state' => $data['state'],
            'pincode' => $data['pincode'],
            'is_default' => $data['is_default'] ?? false,
        ]);

        return new AddressResource($address);
    }
}
