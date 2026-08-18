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

    /**
     * Bank/PAN details a seller submits for payouts. Saving these doesn't
     * itself create or activate a Razorpay Route account — an admin still
     * has to trigger that from the admin panel once these are on file, and
     * Razorpay's own KYC review decides whether the account can actually
     * receive transfers.
     */
    public function updatePayout(Request $request)
    {
        $seller = $request->user()->seller;

        $data = $request->validate([
            'pan_number' => ['required', 'string', 'max:10'],
            'bank_account_holder' => ['required', 'string', 'max:150'],
            'bank_account_number' => ['required', 'string', 'max:34'],
            'bank_ifsc' => ['required', 'string', 'max:11'],
        ]);

        $seller->update($data);

        return back()->with('status', 'Payout details saved. Hardware Centers will review and link your account for payouts.');
    }
}
