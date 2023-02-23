<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Type;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $products = $this->productService->getValidProducts();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'expiration_date' => 'required|date|after:now',
            'type' => [new Enum(Type::class)],
            'price' => 'required|numeric',
            'quantity' => 'required|numeric'
        ]);

        $input = $request->input();
        $product = $this->productService->saveProduct($input);

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json($product, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->deleteProduct($product);

        return response()->json([], 204);
    }
}
