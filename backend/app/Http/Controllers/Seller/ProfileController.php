<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('seller.profile', ['seller' => $request->user()->seller]);
    }

    public function update(Request $request)
    {
        $seller = $request->user()->seller;

        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:150'],
            'owner_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'warehouse_address' => ['nullable', 'string'],
        ]);

        $seller->update($data);

        return back()->with('status', 'Profile updated.');
    }
}
