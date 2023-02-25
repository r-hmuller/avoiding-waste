<?php

namespace Tests\Feature;

use App\Models\Consumption;
use App\Models\Product;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsumptionTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    public function setUp(): void
    {
        parent::setUp();
        $product = new Product([
            'name' => 'Test Product 2',
            'price' => 40.5,
            'expiration_date' => Carbon::now()->addDays(3)->format("Y-m-d"),
            'quantity' => 10,
            'type'  => Type::unit,
        ]);

        $product->save();

        $arrayQuantity = [1, 1];

        foreach ($arrayQuantity as $quantity) {
            $consumption = new Consumption(['quantity' => $quantity]);
            $consumption->product()->associate($product);
            $consumption->save();
        }
        $this->product = $product;
    }

    public function test_get_consumed_quantity(): void
    {
        $this->assertEquals(2, $this->product->quantityConsumed());
    }

}
