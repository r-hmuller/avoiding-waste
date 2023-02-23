<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function getValidProducts(): Collection
    {
        return Product::valid()->get();
    }

    public function saveProduct(array $requestData): Product
    {
        $product = new Product($requestData);
        $product->save();

        return $product;
    }

    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }
}
