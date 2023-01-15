<?php

use App\Http\Controllers\Api\Beverage\BeverageController;
use App\Http\Controllers\Api\BeverageMachine\BeverageMachineController;
use App\Http\Controllers\Api\Contact\ContactController;
use App\Http\Controllers\Api\Machine\MachineController;
use App\Http\Controllers\Api\Razorpay\RazorpayController;
use App\Http\Controllers\DispenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('machines', MachineController::class);
Route::apiResource('beverages', BeverageController::class);
Route::apiResource('machines.beverages', BeverageMachineController::class);
Route::apiResource('razorpays', RazorpayController::class)->only(['index', 'update']);
Route::apiResource('machines.beverages.razorpays', RazorpayController::class)->only('store');
Route::apiResource('contacts', ContactController::class);
Route::get('rewardsPayment', [RazorpayController::class, 'rewardsPayment']);
Route::apiResource('machines.dispenses', DispenseController::class)->only(['index', 'delete']);
Route::get('dispenseDelete', [DispenseController::class, 'dispenseDelete']);
