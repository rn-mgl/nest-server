<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix("api")->group(function() {

    Route::get('/', function () {
        return redirect(env("NEST_URL"));
    });

    Route::get('sanctum/csrf-cookie', function() {
        return response()->json(["token" => csrf_token()]);
    });

    // base auth
    Route::prefix("auth")->group(function() {
        Route::controller(RegisterController::class)->group(function() {
            Route::post("/register", "store");
        });

        Route::controller(SessionController::class)->group(function() {
            Route::post("/login", "store");
        });

        Route::controller(UserAuthController::class)->group(function() {
            Route::patch('/verify', "verify");
            Route::post("/verification-notification", "resend_verification")->middleware(["auth:base", "throttle:6,1"]);
        });
    });

    // admin auth
    Route::prefix("admin_auth")->group(function() {
        Route::controller(AdminSessionController::class)->group(function() {
            Route::post("/login", "store");
        });

        Route::controller(AdminAuthController::class)->group(function() {
            Route::patch("/verify", "verify");
            Route::post("/verification-notification", "resend_verification")->middleware(["auth:admin", "throttle:6,1"]);
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
    Route::middleware(["auth:admin", "valid_admin_token"])->prefix("admin")->group(function() {
        Route::prefix("hr")->group(function() {
            Route::controller(RegisterController::class)->group(function() {
                Route::post("/register", "store");
            });
        });
    });
});
