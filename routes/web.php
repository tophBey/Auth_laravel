<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VerifyController;
use App\Models\Verification;
use Illuminate\Support\Facades\Auth;
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
    // return view('welcome');
    return redirect()->route('login');
});


Route::middleware(['guest'])->group(function(){
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerStore'])->name('register.store');
});



Route::middleware(['auth'])->group(function() {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


    Route::middleware(['check_role:admin,staff'])->group(function(){
        Route::get('/dashboard', [AuthController::class, 'dashboard']);
    });




    Route::middleware(['check_role:customer'])->group(function(){

        Route::get('/verify', [VerifyController::class, 'index'])->name('verify');
        Route::post('/verify', [VerifyController::class, 'store'])->name('verify.store');
        Route::get('/verify/{uniq_id}', [VerifyController::class, 'show'])->name('verify.show');
        Route::put('/verify/{uniq_id}', [VerifyController::class, 'update'])->name('verify.update');

        Route::middleware(['check_status'])->group(function(){
            Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
        });

    });

});

