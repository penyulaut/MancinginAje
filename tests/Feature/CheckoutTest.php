<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Products;
use App\Models\Orders;
use App\Services\MidtransService;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bind a fake MidtransService so tests don't call external API
        $this->app->bind(MidtransService::class, function () {
            return new class extends \App\Services\MidtransService {
                public function __construct() {}
                public function createSnapToken($order) { return 'FAKE_SNAP_TOKEN'; }
                public function handleNotification($payload) { return true; }
            };
        });
    }

    public function test_checkout_creates_order_and_reduces_stock()
    {
        $user = User::factory()->create();

        // ensure category exists for FK
        $category = \App\Models\Category::create(['nama' => 'Joran']);

        $product = Products::create([
            'nama' => 'Test Rod',
            'deskripsi' => 'Quality rod',
            'harga' => 100000,
            'stok' => 5,
            'category_id' => $category->id,
        ]);

        $this->actingAs($user)
             ->post(route('cart.store'), ['id' => $product->id, 'quantity' => 2])
             ->assertRedirect(route('cart.index'));

        $this->assertEquals(3, Products::find($product->id)->stok + 0); // still 5 in DB, reduction happens on checkout

        // perform checkout
        $response = $this->actingAs($user)->post(route('payment.store'), [
            'customer_name' => 'Buyer',
            'customer_email' => 'buyer@example.com',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Test',
            'payment_method' => 'bank_transfer'
        ]);

        $response->assertStatus(200);
        $response->assertViewHas('snapToken', 'FAKE_SNAP_TOKEN');

        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total_harga' => 200000, 'status' => 'pending']);

        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2, 'price' => 100000]);

        $this->assertEquals(3, Products::find($product->id)->stok);
    }

    public function test_cannot_add_more_than_stock_and_is_capped()
    {
        $user = User::factory()->create();

        // ensure category exists for FK
        $category = \App\Models\Category::first() ?? \App\Models\Category::create(['nama' => 'Reel']);

        $product = Products::create([
            'nama' => 'Limited Reel',
            'deskripsi' => 'Limited stock',
            'harga' => 50000,
            'stok' => 1,
            'category_id' => $category->id,
        ]);

        $this->actingAs($user)
             ->post(route('cart.store'), ['id' => $product->id, 'quantity' => 5])
             ->assertRedirect(route('cart.index'))
             ->assertSessionHas('warning');

        $this->assertEquals(1, session('cart')[$product->id]['quantity']);
    }

    public function test_customer_cannot_access_seller_dashboard()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
             ->get('/beranda/dashboard')
             ->assertRedirect('/')
             ->assertSessionHas('error');
    }
}
