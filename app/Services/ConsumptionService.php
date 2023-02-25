<?php

namespace App\Services;

use App\Models\Consumption;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ConsumptionService
{
    public function getProductConsumption(Product $product): Collection
    {
        return $product->consumptions;
    }

    public function store(Product $product, float $quantity): Consumption
    {
        $consumption = new Consumption(['quantity' => $quantity]);
        $consumption->product()->associate($product);
        $consumption->save();

        return $consumption;
    }

    public function update(Product $product, Consumption $consumption, float $quantity): Consumption
    {
        $consumption->quantity = $quantity;
        $consumption->save();

        return $consumption;
    }

    public function destroy(Consumption $consumption): void
    {
        $consumption->delete();
    }
}
