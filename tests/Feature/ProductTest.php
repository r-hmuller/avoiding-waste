<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * Test list products, it should return an empty array with status 200
     */
    public function test_list_products(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_save_product_missing_expiration_date_field()
    {
        $response = $this->postJson('/api/products', ['name' => 'Test Product', 'price' => 40.5, 'quantity' => 2]);

        $response->assertStatus(422);
    }

    public function test_save_product_missing_quantity_field()
    {
        $response = $this->postJson('/api/products', ['name' => 'Test Product', 'price' => 40.5, 'expiration_date' => Carbon::now()->addDays(5)]);

        $response->assertStatus(422);
    }

    public function test_save_product_wrong_type_quantity_field()
    {
        $response = $this->postJson('/api/products',
            [
                'name' => 'Test Product',
                'price' => 40.5,
                'expiration_date' => Carbon::now()->addDays(5),
                'quantity' => 'Not a number'
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'The quantity field must be a number.');
    }

    public function test_save_product_all_correct_fields_should_return_201()
    {
        $response = $this->postJson('/api/products',
            [
                'name' => 'Test Product',
                'price' => 40.5,
                'expiration_date' => Carbon::now()->addDays(5)->format("Y-m-d"),
                'quantity' => '5',
                'type'  => Type::unit,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('name', 'Test Product');
    }

    public function test_get_product_non_existent_should_return_404()
    {
        $response = $this->getJson('/api/products/9091');

        $response->assertStatus(404);
    }

    public function test_get_product_existent_should_return_200()
    {
        $productOnDb = Product::first();
        $response = $this->getJson("/api/products/$productOnDb->id");

        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('id', 1)
                ->where('name', 'Test Product')
                ->where('price', 40.5)
                ->where('quantity', 5)
                ->where('type', 'unit')
                ->etc()
        );
    }

    public function test_update_product_wrong_type_quantity_field()
    {
        $response = $this->postJson('/api/products',
            [
                'name' => 'Test Product',
                'price' => 40.5,
                'expiration_date' => Carbon::now()->addDays(5),
                'quantity' => 'Not a number'
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'The quantity field must be a number.');
    }

    public function test_update_product_all_correct_fields_should_return_200()
    {
        $response = $this->postJson('/api/products',
            [
                'name' => 'Test Product Updated',
                'price' => 40.5,
                'expiration_date' => Carbon::now()->addDays(5)->format("Y-m-d"),
                'quantity' => '5',
                'type'  => Type::unit,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('name', 'Test Product Updated');
    }


    public function test_delete_non_existent_product_should_return_404()
    {
        $response = $this->deleteJson('/api/products/9999');

        $response->assertStatus(404);
    }

    public function test_delete_existent_product_should_return_204()
    {
        $productOnDb = Product::first();
        $response = $this->deleteJson("/api/products/$productOnDb->id");

        $response->assertStatus(204);
    }

}
