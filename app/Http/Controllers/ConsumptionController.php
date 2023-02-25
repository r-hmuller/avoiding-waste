<?php

namespace App\Http\Controllers;

use App\Models\Consumption;
use App\Models\Product;
use App\Services\ConsumptionService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsumptionController extends Controller
{
    public function __construct(
        protected ConsumptionService $consumptionService
    ){}
    public  function index(Request $request, Product $product): JsonResponse
    {
        $consumptions = $this->consumptionService->getProductConsumption($product);

        return response()->json($consumptions);
    }

    public function show(Request $request, Product $product, Consumption $consumption): JsonResponse
    {
        return response()->json($consumption);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'quantity' => [
                'bail',
                'required',
                'numeric',
                function (string $attribute, mixed $value, Closure $fail) use($product) {
                    if ($value > ($product->quantity - $product->quantityConsumed())) {
                        $fail("The {$attribute} is greater than the product quantity available.");
                    }
                },
            ]
        ]);

        $quantity = $request->get('quantity');
        $consumption = $this->consumptionService->store($product, $quantity);

        return response()->json($consumption, 201);
    }

    public function update(Request $request, Product $product, Consumption $consumption): JsonResponse
    {
        $request->validate([
            'quantity' => [
                'bail',
                'required',
                'numeric',
                function (string $attribute, mixed $value, Closure $fail) use($product) {
                    if ($value > ($product->quantity - $product->quantityConsumed())) {
                        $fail("The {$attribute} is greater than the product quantity available.");
                    }
                },
            ]
        ]);

        $quantity = $request->get('quantity');
        $consumption = $this->consumptionService->update($product, $consumption, $quantity);

        return response()->json($consumption, 200);
    }

    public function destroy(Request $request, Product $product, Consumption $consumption): JsonResponse
    {
        $this->consumptionService->destroy($consumption);

        return response()->json([], 204);
    }
}
