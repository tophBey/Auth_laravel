<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VerifyController;
use App\Models\Verification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

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

    Route::get('/auth-google-redirect', [AuthController::class, 'google_redirect'])->name('google.redirect');
    Route::get('/auth-google-callback', [AuthController::class, 'google_callback'])->name('google.callback');

    Route::get('/reset-password', [AuthController::class, 'resetPassword'])->name('reset.password'); // 1
    Route::post('/reset-password', [AuthController::class, 'resetPasswordStore'])->name('reset.password.store'); //2
});



Route::middleware(['auth'])->group(function() {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/verify-password-otp', [AuthController::class, 'verifyPasswordOtp'])->name('verify.password.otp');
    Route::post('/verify-password-otp', [AuthController::class, 'verifyPasswordOtpStore'])->name('verify.password.otp.store');
    Route::get('/verify-otp/{uniq_id}', [AuthController::class, 'verifyOtp'])->name('reset.otp.show');
    Route::put('/verify-otp/{uniq_id}', [AuthController::class, 'verifyOtpUpdate'])->name('update.otp');
    Route::get('/reset-password/{uniq_id}', [AuthController::class, 'resetPasswordShow'])->name('reset.password.show');
    Route::put('/reset-password/{uniq_id}', [AuthController::class, 'resetPasswordUpdate'])->name('reset.password.update');

    Route::middleware(['check_role:admin,staff'])->group(function(){
        Route::get('/dashboard', [AuthController::class, 'dashboard']);
    });

    Route::middleware(['check_role:customer'])->group(function(){

        Route::get('/verify', [VerifyController::class, 'index'])->name('verify'); //3
        Route::post('/verify', [VerifyController::class, 'store'])->name('verify.store');
        Route::get('/verify/{uniq_id}', [VerifyController::class, 'show'])->name('verify.show');//4
        Route::put('/verify/{uniq_id}', [VerifyController::class, 'update'])->name('verify.update');

        Route::middleware(['check_status'])->group(function(){
            Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
        });

    });

});

