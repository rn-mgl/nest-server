<?php

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::prefix("api")->group(function() {

    Route::get('/', function () {
        return redirect(env("APP_URL"));
    });

    Route::get('sanctum/csrf-cookie', function() {
        return response()->json(["token" => csrf_token()]);
    });

    Route::middleware("guest")->group(function() {
        Route::prefix("auth")->group(function() {
            Route::controller(RegisterController::class)->group(function() {
                Route::post("/register", "store");
            });

            Route::controller(SessionController::class)->group(function() {
                Route::post("/login", "store");
            });
        });
    });

    Route::middleware("auth")->group(function() {

        // email verification routes
        Route::prefix("email")->name("verification.")->group(function() {
            Route::get('/verify', function () {
                return view('auth.verify-email');
            })->name('notice');

            Route::get('/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
                $request->fulfill();

                return redirect('/home');
            })->middleware("signed")->name('verify');

            Route::post('/verification-notification', function (Request $request) {
                $request->user()->sendEmailVerificationNotification();

                return back()->with('message', 'Verification link sent!');
            })->middleware("throttle:6,1")->name('send');
        });

        // session routes
        Route::controller(SessionController::class)->group(function() {
            Route::delete("/auth/logout", "delete");
        });
    });

});
