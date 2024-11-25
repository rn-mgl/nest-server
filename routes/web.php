<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminHRController;
use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\HREmployeeController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserAuthController;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

    // hr routes
    Route::middleware(["auth:base", "valid_user_token"])->prefix("hr")->group(function() {
        // session routes
        Route::prefix("auth")->group(function() {
            Route::controller(SessionController::class)->group(function() {
                Route::post("/logout", "destroy");
            });
        });

        // employee route
        Route::prefix("employee")->group(function() {
            Route::controller(HREmployeeController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
            });
        });

        // leave route
        Route::prefix("leave_type")->group(function() {
            Route::controller(LeaveTypeController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::get("/{leaveType}", "show")->can("updateHR", User::class);
                Route::patch("/{leaveType}", "update")->can("updateHR", User::class);
                Route::delete("/{leaveType}", "destroy")->can("updateHR", User::class);
            });
        });
    });

    // admin routes
    Route::middleware(["auth:admin", "valid_admin_token"])->prefix("admin")->group(function() {
        Route::prefix("auth")->group(function() {
            Route::controller(AdminSessionController::class)->group(function() {
                Route::post("/logout", "destroy");
            });
        });

        Route::prefix("hr")->group(function() {
            Route::controller(RegisterController::class)->group(function() {
                Route::post("/register", "store")->can("update", Admin::class);
            });

            Route::controller(AdminHRController::class)->group(function() {
                Route::get("/", "index")->can("update", Admin::class);
                Route::patch("/update/{hr}", "update")->can("update", Admin::class);
            });
        });
    });
});
