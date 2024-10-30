<?php

use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::prefix("api")->group(function() {

    Route::get('/', function () {
        return redirect(env("NEST_URL"));
    });

    Route::get('sanctum/csrf-cookie', function() {
        return response()->json(["token" => csrf_token()]);
    });

    // employee and hr auth
    Route::prefix("auth")->group(function() {
        Route::controller(RegisterController::class)->group(function() {
            Route::post("/register", "store");
        });

        Route::controller(SessionController::class)->group(function() {
            Route::get("/login", "index")->name("login");
            Route::post("/login", "store");
        });
    });

    // admin auth
    Route::prefix("admin_auth")->group(function() {
        Route::controller(AdminSessionController::class)->group(function() {
            Route::post("/login", "store");
        });
    });

    Route::middleware(["auth", "valid_token"])->group(function() {

        // email verification routes
    Route::middleware(["auth:web"])->group(function() {
        Route::prefix("email")->name("verification.")->group(function() {
            Route::get('/verify', function () {
                return redirect(env("NEST_URL") . "/auth/sending?type=verification");
            })->name('notice');

            Route::get('/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
                $request->fulfill();

                Auth::guard("base")->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect(env("NEST_URL") . "/auth/login");
            })->middleware("signed")->name('verify');

            Route::post('/verification-notification', function (Request $request) {
                $request->user()->sendEmailVerificationNotification();

                return response()->json(["message" => "Verification link sent!"]);
            })->middleware("throttle:6,1")->name('send');
        });
        });

    // hr and employee routes
    Route::middleware(["auth:base", "valid_user_token"])->group(function() {
        // session routes
        Route::prefix("auth")->group(function() {
            Route::controller(SessionController::class)->group(function() {
                Route::delete("/logout", "delete");
            });
        });

        // employee route
        Route::prefix("employee")->group(function() {
            Route::get("/dashboard", function() {
                return response()->json(["test" => 123]);
            });
        });

        // hr route
        Route::prefix("hr")->group(function() {

        });
    });

    // admin routes
    // Route::middleware(["auth:admin",])
});
