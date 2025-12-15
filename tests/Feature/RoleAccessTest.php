<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_access_dashboard()
    {
        $seller = User::factory()->create(['role' => 'seller']);

        $this->actingAs($seller)
             ->get('/beranda/dashboard')
             ->assertStatus(200);
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
