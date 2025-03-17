<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminHRController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentFolderController;
use App\Http\Controllers\HR\HRAttendanceController;
use App\Http\Controllers\HR\HREmployeeController;
use App\Http\Controllers\HR\HRLeaveTypeController;
use App\Http\Controllers\HR\HROnboardingController;
use App\Http\Controllers\HR\HRPerformanceReviewController;
use App\Http\Controllers\HR\HRTrainingController;
use App\Http\Controllers\BaseAuthController;
use App\Models\Admin;
use App\Models\User;
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
        Route::controller(BaseAuthController::class)->group(function() {
            Route::post("/login", "login");
            Route::post("/register", "register");
            Route::patch('/verify', "verify");
            Route::post("/verification-notification", "resend_verification")->middleware(["auth:base", "throttle:6,1"]);
        });
    });

    // admin auth
    Route::prefix("admin_auth")->group(function() {
        Route::controller(AdminAuthController::class)->group(function() {
            Route::post("/register", "register");
            Route::post("/login", "login");
            Route::patch("/verify", "verify");
            Route::post("/verification-notification", "rwesend_verification")->middleware(["auth:admin", "throttle:6,1"]);
        });
    });

    // hr routes
    Route::middleware(["auth:base", "valid_user_token"])->prefix("hr")->group(function() {
        // session routes
        Route::prefix("auth")->group(function() {
            Route::controller(BaseAuthController::class)->group(function() {
                Route::post("/logout", "logout");
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
            Route::controller(HRLeaveTypeController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::get("/{leaveType}", "show")->can("updateHR", User::class);
                Route::patch("/{leaveType}", "update")->can("updateHR", User::class);
                Route::delete("/{leaveType}", "destroy")->can("updateHR", User::class);
            });
        });

        // onboarding route
        Route::prefix('onboarding')->group(function() {
            Route::controller(HROnboardingController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::get("/{onboarding}", "show")->can("updateHR", User::class);
                Route::patch("/{onboarding}", "update")->can("updateHR", User::class);
                Route::delete("/{onboarding}", "destroy")->can("updateHR", User::class);
            });
        });

        Route::prefix('attendance')->group(function() {
            Route::controller(HRAttendanceController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::get("/{attendance}", "show")->can("updateHR", User::class);
            });
        });

        Route::prefix('performance_review')->group(function() {
            Route::controller(HRPerformanceReviewController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::get("/{performance_review}", "show")->can("updateHR", User::class);
                Route::patch("/{performance_review}", "update")->can("updateHR", User::class);
                Route::delete("/{performance_review}", "destroy")->can("updateHR", User::class);
            });
        });

        Route::prefix('training')->group(function() {
            Route::controller(HRTrainingController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::get("/{training}", "show")->can("updateHR", User::class);
                Route::patch("/{training}", "update")->can("updateHR", User::class);
                Route::delete("/{training}", "destroy")->can("updateHR", User::class);
            });
        });

        Route::prefix("document")->group(function() {
            Route::controller(DocumentController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::get("/{document}", "show")->can("updateHR", User::class);
                Route::patch("/{document}", "update")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::delete("/{document}", "destroy")->can("updateHR", User::class);
            });
        });

        Route::prefix('document_folder')->group(function() {
            Route::controller(DocumentFolderController::class)->group(function() {
                Route::get("/paths", "get_parent_paths")->can("updateHR", User::class);
                Route::get("/{document_folder}", "show")->can("updateHR", User::class);
                Route::patch("/{document_folder}", "update")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::delete("/{document_folder}", "destroy")->can("updateHR", User::class);
            });
        });
    });

    // employee routes
    Route::middleware(["auth:base", "valid_user_token"])->prefix("employee")->group(function() {
        Route::prefix("auth")->group(function() {
            Route::controller(BaseAuthController::class)->group(function() {
                Route::post("/logout", "logout");
            });
        });
    });

    // admin routes
    Route::middleware(["auth:admin", "valid_admin_token"])->prefix("admin")->group(function() {
        Route::prefix("auth")->group(function() {
            Route::controller(AdminAuthController::class)->group(function() {
                Route::post("/logout", "logout");
            });
        });

        Route::prefix("hr")->group(function() {
            Route::controller(BaseAuthController::class)->group(function() {
                Route::post("/register", "register")->can("update", Admin::class);
            });

            Route::controller(AdminHRController::class)->group(function() {
                Route::get("/", "index")->can("update", Admin::class);
                Route::patch("/update/{hr}", "update")->can("update", Admin::class);
            });
        });
    });
});
