<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WebLeadController extends Controller
{
    public function contact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string'],
        ]);

        ContactMessage::create([...$data, 'source' => 'contact']);

        return response()->json(['message' => "Thanks! We'll be in touch shortly."]);
    }

    public function supportTicket(Request $request)
    {
        $data = $request->validate([
            'order_no' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string'],
        ]);

        ContactMessage::create([...$data, 'source' => 'support_ticket']);

        return response()->json(['message' => "Ticket submitted. We'll follow up by email shortly."]);
    }

    public function sell(Request $request)
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:150'],
            'owner_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'gst_number' => ['required', 'string', 'max:20'],
            'category_interest' => ['required', 'string', 'max:60'],
            'warehouse_address' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $data['owner_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);
        $user->assignRole('Seller');

        $seller = Seller::create([
            'seller_code' => 'SLR-'.(1000 + Seller::count()),
            'user_id' => $user->id,
            'store_name' => $data['store_name'],
            'owner_name' => $data['owner_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'city' => '',
            'state' => '',
            'warehouse_address' => $data['warehouse_address'] ?? null,
            'category_interest' => $data['category_interest'],
            'gst_number' => $data['gst_number'],
        ]);

        return response()->json([
            'message' => 'Application received! Sign in to the Seller Center any time to track your approval status.',
            'seller_code' => $seller->seller_code,
        ], 201);
    }

    public function newsletter(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        NewsletterSubscriber::firstOrCreate(
            ['email' => Str::lower($data['email'])],
            ['subscribed_at' => now()]
        );

        return response()->json(['message' => "Subscribed! Watch your inbox for our next issue."]);
    }
}
