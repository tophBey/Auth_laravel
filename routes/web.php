<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
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
    return view('welcome');
});


Route::middleware(['guest'])->group(function(){
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerStore'])->name('register.store');
});


Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'check_role:admin,staff'])->group(function() {
    Route::get('/dashboard', [AuthController::class, 'dashboard']);
});

Route::middleware(['auth', 'check_role:customer'])->group(function() {
    Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
});
