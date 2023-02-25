<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use \App\Http\Controllers\ConsumptionController;
use \App\Http\Controllers\ProductController;
use App\Models\Consumption;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('products', ProductController::class, ['except' => ['create', 'edit']]);
Route::resource('products.consumptions', ConsumptionController::class, ['except' => ['create', 'edit']]);

Route::bind('consumption', function ($consumption, $route) {
    return Consumption::where('product_id', $route->parameter('product'))->findOrFail($consumption);
});
