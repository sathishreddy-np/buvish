<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('email/verify', function (Request $request) {
    $user = User::where('id',$request->id)->first();
    if($user){
        $user->markEmailAsVerified();
    }
    return redirect()->back();
})->name('verification.verify');
