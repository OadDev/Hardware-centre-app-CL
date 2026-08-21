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
        if ($request->filled('gst_number')) {
            $request->merge(['gst_number' => strtoupper(trim($request->input('gst_number')))]);
        }

        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:12'],
            'gst_number' => ['nullable', 'string', 'regex:/^\d{2}[A-Z]{5}\d{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
            'email' => ['nullable', 'email', 'max:190'],
            'is_default' => ['sometimes', 'boolean'],
        ], [
            'gst_number.regex' => 'Enter a valid 15-character GST number.',
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
            'gst_number' => $data['gst_number'] ?? null,
            'email' => $data['email'] ?? null,
            'is_default' => $data['is_default'] ?? false,
        ]);

        return new AddressResource($address);
    }
}
