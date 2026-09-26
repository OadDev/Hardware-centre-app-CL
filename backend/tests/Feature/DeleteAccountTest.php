<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'customer_code' => 'CUS-'.random_int(10000, 99999),
            'name' => 'Ravi Kumar',
            'email' => 'ravi@example.com',
            'phone' => '9876543210',
            'business_name' => 'Ravi Traders',
            'status' => 'Active',
            'is_guest' => false,
        ]);
    }

    private function address(Customer $customer): int
    {
        return DB::table('addresses')->insertGetId([
            'customer_id' => $customer->id,
            'contact_name' => 'Ravi',
            'line' => '12 MG Road',
            'city' => 'Pune',
            'state' => 'MH',
            'pincode' => '411001',
        ]);
    }

    public function test_customer_without_orders_is_deleted(): void
    {
        $customer = $this->makeCustomer();
        $this->address($customer);
        $token = $customer->createToken('mobile')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/me')->assertOk();

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseCount('addresses', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_customer_with_orders_is_anonymised_and_orders_kept(): void
    {
        $customer = $this->makeCustomer();
        $usedAddress = $this->address($customer);
        $this->address($customer);
        DB::table('orders')->insert([
            'order_no' => 'HC-1',
            'customer_id' => $customer->id,
            'address_id' => $usedAddress,
            'subtotal' => 100,
            'total' => 100,
        ]);
        $token = $customer->createToken('mobile')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/me')->assertOk();

        $customer->refresh();
        $this->assertSame('Deleted Customer', $customer->name);
        $this->assertNull($customer->phone);
        $this->assertNull($customer->business_name);
        $this->assertSame('Blocked', $customer->status);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('addresses', ['id' => $usedAddress]);
        $this->assertDatabaseCount('addresses', 1);
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // The old token no longer works.
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    }
}
