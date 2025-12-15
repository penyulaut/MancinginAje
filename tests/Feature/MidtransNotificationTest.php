<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Orders;

class MidtransNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_settlement_updates_order_to_paid()
    {
        $user = User::factory()->create();

        $order = Orders::create([
            'user_id' => $user->id,
            'total_harga' => 100000,
            'status' => 'pending',
            'customer_name' => 'Test',
            'customer_email' => 't@test.com',
            'customer_phone' => '081234',
            'shipping_address' => 'Addr',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
        ]);

        $payload = [
            'order_id' => $order->id . '-notify',
            'transaction_status' => 'settlement',
            'fraud_status' => null,
            'transaction_id' => 'tx-123'
        ];

        $this->actingAs($user)
             ->postJson(route('payment.notification'), $payload)
             ->assertStatus(200);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid', 'payment_status' => 'paid']);
    }

    public function test_expire_marks_order_cancelled_and_expired()
    {
        $user = User::factory()->create();

        $order = Orders::create([
            'user_id' => $user->id,
            'total_harga' => 50000,
            'status' => 'pending',
            'customer_name' => 'Expire',
            'customer_email' => 'e@test.com',
            'customer_phone' => '0812',
            'shipping_address' => 'Addr',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
        ]);

        $payload = [
            'order_id' => $order->id . '-exp',
            'transaction_status' => 'expire',
            'fraud_status' => null,
            'transaction_id' => 'tx-exp'
        ];

        $this->actingAs($user)
             ->postJson(route('payment.notification'), $payload)
             ->assertStatus(200);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled', 'payment_status' => 'expired']);
    }

    public function test_deny_marks_order_cancelled_and_failed()
    {
        $user = User::factory()->create();

        $order = Orders::create([
            'user_id' => $user->id,
            'total_harga' => 25000,
            'status' => 'pending',
            'customer_name' => 'Deny',
            'customer_email' => 'd@test.com',
            'customer_phone' => '0813',
            'shipping_address' => 'Addr',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
        ]);

        $payload = [
            'order_id' => $order->id . '-deny',
            'transaction_status' => 'deny',
            'fraud_status' => null,
            'transaction_id' => 'tx-deny'
        ];

        $this->actingAs($user)
             ->postJson(route('payment.notification'), $payload)
             ->assertStatus(200);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled', 'payment_status' => 'failed']);
    }
}
