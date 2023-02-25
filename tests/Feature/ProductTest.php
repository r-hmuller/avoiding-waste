<?php

namespace Tests\Feature;

use App\Models\Consumption;
use App\Models\Product;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test list products, it should return an empty array with status 200
     */

    public function setUp(): void
    {
        parent::setUp();
        $product = new Product([
            'name' => 'Test Product 2',
            'price' => 40.5,
            'expiration_date' => Carbon::now()->addDays(3)->format("Y-m-d"),
            'quantity' => 10.0,
            'type'  => Type::unit,
        ]);

        $product->save();
        $consumption = new Consumption(['quantity' => 1.0]);
        $consumption->product()->associate($product);
        $consumption->save();
    }

    public function test_list_products(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
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
                'quantity' => 5.0,
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
            $json->where('id', $productOnDb->id)
                ->where('name', 'Test Product 2')
                ->where('price', 40.5)
                ->where('quantity', 10)
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
                'quantity' => '5.0',
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
        $productOnDb = Product::orderByDesc('id')->first();
        $response = $this->deleteJson("/api/products/$productOnDb->id");

        $response->assertStatus(204);
    }

    public function test_invalid_consumption_quantity_should_return_422(): void
    {
        $product = Product::first();
        $response = $this->postJson("/api/products/$product->id/consumptions", ['quantity' => 'Not a number']);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'The quantity field must be a number.');
    }

    public function test_missing_consumption_quantity_should_return_422(): void
    {
        $product = Product::first();
        $response = $this->postJson("/api/products/$product->id/consumptions", []);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'The quantity field is required.');
    }

    public function test_consumption_quantity_greater_than_product_quantity_should_return_422(): void
    {
        $product = Product::first();
        $response = $this->postJson("/api/products/$product->id/consumptions", ['quantity' => 10000]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', "The quantity is greater than the product quantity available.");
    }

    public function test_consumption_quantity_correct_should_return_201(): void
    {
        $product = Product::first();
        $quantity = $product->quantity * 0.5;
        $response = $this->postJson("/api/products/$product->id/consumptions", ['quantity' => $quantity]);

        $response->assertStatus(201);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->has('quantity')
            ->etc()
        );
    }

    public function test_get_consumption_by_id_should_return_200_and_has_quantity(): void
    {
        $product = Product::first();
        $consumption = Consumption::first();
        $response = $this->getJson("/api/products/$product->id/consumptions/$consumption->id");

        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->where('id', $consumption->id)
            ->has('quantity')
            ->etc()
        );
    }

    public function test_consumption_cannot_be_accessed_by_wrong_product_and_should_return_404(): void
    {
        $this->postJson('/api/products',
            [
                'name' => 'Test Product 2',
                'price' => 40.5,
                'expiration_date' => Carbon::now()->addDays(3)->format("Y-m-d"),
                'quantity' => '5.0',
                'type'  => Type::unit,
            ]);

        $lastRegisteredProduct = Product::orderByDesc('id')->limit(1)->first();
        $consumption = Consumption::first();

        $response = $this->getJson("/api/products/$lastRegisteredProduct->id/consumptions/$consumption");
        $response->assertStatus(404);
    }

    public function test_update_consumption_greater_than_product_quantity_should_return_422()
    {
        $product = Product::first();
        $consumption = Consumption::first();
        $response = $this->putJson("/api/products/$product->id/consumptions/$consumption->id",
            ['quantity' => $product->quantity + 2]
        );

        $response->assertStatus(422);
        $response->assertJsonPath('message', "The quantity is greater than the product quantity available.");
    }

    public function test_update_consumption_less_than_product_quantity_should_return_200()
    {
        $product = Product::first();
        $consumption = Consumption::first();
        $response = $this->putJson("/api/products/$product->id/consumptions/$consumption->id",
            ['quantity' => $product->quantity - 1]
        );

        $response->dump();
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->where('id', $consumption->id)
            ->etc()
        );
    }
}
