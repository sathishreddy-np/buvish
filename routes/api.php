<?php

use App\Http\Controllers\BeverageController;
use App\Http\Controllers\BeverageMachineController;
use App\Http\Controllers\MachineController;
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
