<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authAs(string $roleName): array
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->roles()->attach($role);
        $token = auth()->login($user);
        return ['Authorization' => "Bearer $token"];
    }

    public function test_admin_can_list_products(): void
    {
        Product::create(['name' => 'Prod A', 'amount' => 1000]);

        $response = $this->getJson('/api/products', $this->authAs('ADMIN'));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_create_product(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'New Product',
            'amount' => 2500,
        ], $this->authAs('ADMIN'));

        $response->assertCreated()
            ->assertJsonPath('data.name', 'New Product')
            ->assertJsonPath('data.amount', 2500);
    }

    public function test_admin_can_show_product(): void
    {
        $product = Product::create(['name' => 'Prod', 'amount' => 500]);

        $response = $this->getJson("/api/products/{$product->id}", $this->authAs('ADMIN'));

        $response->assertOk()
            ->assertJsonPath('data.name', 'Prod');
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::create(['name' => 'Old', 'amount' => 500]);

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated',
            'amount' => 900,
        ], $this->authAs('ADMIN'));

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::create(['name' => 'Del', 'amount' => 100]);

        $response = $this->deleteJson("/api/products/{$product->id}", [], $this->authAs('ADMIN'));

        $response->assertOk();
        $this->assertSoftDeleted($product);
    }

    public function test_user_role_cannot_manage_products(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'X',
            'amount' => 100,
        ], $this->authAs('USER'));

        $response->assertForbidden();
    }

    public function test_finance_can_manage_products(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Finance Prod',
            'amount' => 300,
        ], $this->authAs('FINANCE'));

        $response->assertCreated();
    }

    public function test_create_product_validates_required_fields(): void
    {
        $response = $this->postJson('/api/products', [], $this->authAs('ADMIN'));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'amount']);
    }
}
