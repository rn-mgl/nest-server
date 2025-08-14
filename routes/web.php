<?php

use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminHRController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\Employee\EmployeeLeaveBalanceController;
use App\Http\Controllers\Employee\EmployeeLeaveRequestController;
use App\Http\Controllers\HR\HRAttendanceController;
use App\Http\Controllers\HR\HREmployeeController;
use App\Http\Controllers\HR\HREmployeeLeaveRequestController;
use App\Http\Controllers\HR\HRLeaveTypeController;
use App\Http\Controllers\HR\HROnboardingController;
use App\Http\Controllers\HR\HRPerformanceReviewController;
use App\Http\Controllers\HR\HRTrainingController;
use App\Http\Controllers\BaseAuthController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeOnboardingController;
use App\Http\Controllers\Employee\EmployeeOnboardingPolicyAcknowledgementController;
use App\Http\Controllers\Employee\EmployeeOnboardingRequiredDocumentsController;
use App\Http\Controllers\Employee\EmployeePerformanceReviewController;
use App\Http\Controllers\Employee\EmployeePerformanceReviewResponseController;
use App\Http\Controllers\Employee\EmployeeTrainingController;
use App\Http\Controllers\Employee\EmployeeTrainingReviewResponseController;
use App\Http\Controllers\HR\HRController;
use App\Http\Controllers\HR\HRDashboardController;
use App\Http\Controllers\HR\HREmployeeOnboardingController;
use App\Http\Controllers\HR\HREmployeePerformanceReviewController;
use App\Http\Controllers\HR\HREmployeeTrainingController;
use App\Http\Controllers\HR\HREmployeeLeaveBalanceController;
use App\Http\Controllers\HR\HRLeaveBalanceController;
use App\Http\Controllers\HR\HRLeaveRequestController;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::prefix("api")->group(function() {

    Route::get('/', function () {
        return redirect(env("NEST_URL"));
    });

    Route::get('csrf-cookie', fn() => response()->json(["token" => csrf_token()]));

    // auth
    Route::controller(BaseAuthController::class)->prefix("auth")->group(function() {
        Route::post("/login", "login");
        Route::post("/register", "register");
        Route::patch('/verify', "verify");
        Route::post("/verification-notification", "resend_verification")->middleware(["auth", "throttle:6,1"]);
        Route::post("/forgot-password", "forgot_password");
        Route::patch("/reset-password", "reset_password");
    });

    // hr routes
    Route::middleware(["auth", "user_token:hr"])->prefix("hr")->group(function() {

        // dashboard route
        Route::controller(HRDashboardController::class)
            ->prefix("/dashboard")
            ->group(function() {
                Route::get("/", "index");
            });

        // session routes
        Route::prefix("auth")->group(function() {
            Route::controller(BaseAuthController::class)->group(function() {
                Route::post("/logout", "logout");
                Route::patch("/change-password", "change_password");
            });
        });

        // employee route
        Route::prefix("employee")->group(function() {
            Route::controller(HREmployeeController::class)->group(function() {
                Route::get("/", "index");
                Route::get("/{employee}", "show");
            });
        });

        // leave route
        Route::prefix("leave_type")->group(function() {
            Route::controller(HRLeaveTypeController::class)->group(function() {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{leaveType}", "show");
                Route::patch("/{leaveType}", "update");
                Route::delete("/{leaveType}", "destroy");
            });
        });

        // leave balance route
        Route::prefix("leave_balance")->group(function () {
            Route::controller(HRLeaveBalanceController::class)->group(function() {
                Route::get("/", "index");
            });
        });

        // employee and hr leave balance route
        Route::prefix("employee_leave_balance")->group(function() {
            Route::controller(HREmployeeLeaveBalanceController::class)->group(function() {
                Route::get("/", "index");
                Route::post("/", "store");
            });
        });

        // leave request
        Route::prefix("leave_request")->group(function() {
            Route::controller(HRLeaveRequestController::class)->group(function() {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{leave_request}", "show");
                Route::patch("/{leave_request}", "update");
                Route::delete("/{leave_request}", "destroy");
            });
        });

        // employee leave request route
        Route::prefix("employee_leave_request")->group(function() {
            Route::controller(HREmployeeLeaveRequestController::class)->group(function() {
                Route::patch("/{leave_request}", "update");
            });
        });

        // onboarding route
        Route::prefix('onboarding')->group(function() {
            Route::controller(HROnboardingController::class)->group(function() {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{onboarding}", "show");
                Route::patch("/{onboarding}", "update");
                Route::delete("/{onboarding}", "destroy");
            });
        });

        // employee onboarding route
        Route::prefix("employee_onboarding")->group(function() {
            Route::controller(HREmployeeOnboardingController::class)->group(function() {
                Route::get("/", "index");
                Route::post("/", "store");
            });
        });

        // attendance route
        Route::prefix('attendance')->group(function() {
            Route::controller(HRAttendanceController::class)->group(function() {
                Route::get("/", "index");
                Route::get("/{attendance}", "show");
            });
        });

        // performance review route
        Route::prefix('performance_review')->group(function() {
            Route::controller(HRPerformanceReviewController::class)->group(function() {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{performance_review}", "show");
                Route::patch("/{performance_review}", "update");
                Route::delete("/{performance_review}", "destroy");
            });
        });

        // employee performance review route
        Route::prefix("employee_performance_review")->group(function() {
            Route::controller(HREmployeePerformanceReviewController::class)->group(function() {
                Route::get("/", "index");
                Route::post("/", "store");
            });
        });

        // training route
        Route::prefix('training')->group(function() {
            Route::controller(HRTrainingController::class)->group(function() {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{training}", "show");
                Route::patch("/{training}", "update");
                Route::delete("/{training}", "destroy");
            });
        });

        // employee training route
        Route::prefix('employee_training')->group(function() {
            Route::controller(HREmployeeTrainingController::class)->group(function() {
                Route::get("/", "index");
                Route::post("/", "store");
            });
        });

        // document route
        Route::prefix("document")->group(function() {
            Route::controller(DocumentController::class)->group(function() {
                Route::get("/", "index");
                Route::get("/{document}", "show");
                Route::patch("/{document}", "update");
                Route::post("/", "store");
                Route::delete("/{document}", "destroy");
            });
        });

        // document folder route
        Route::prefix('folder')->group(function() {
            Route::controller(FolderController::class)->group(function() {
                Route::get("/paths", "get_parent_paths");
                Route::get("/{folder}", "show");
                Route::patch("/{folder}", "update");
                Route::post("/", "store");
                Route::delete("/{folder}", "destroy");
            });
        });

        Route::prefix("profile")->group(function() {
            Route::controller(HRController::class)->group(function() {
                Route::get("/{hr}", "show");
                Route::patch("/{hr}", "update");
            });
        });
    });

    // employee routes
    Route::middleware(["auth", "user_token:employee"])->prefix("employee")->group(function() {

        Route::controller(EmployeeDashboardController::class)
            ->prefix("dashboard")
            ->group(function() {
                Route::get("/", "index");
            });

        Route::controller(BaseAuthController::class)
            ->prefix("auth")
            ->group(function() {
                Route::post("/logout", "logout");
                Route::patch("/change-password", "change_password");
            });

        // attendance route
        Route::controller(EmployeeAttendanceController::class)
            ->prefix("attendance")
            ->group(function() {
                Route::get("/{attendance}","show");
                Route::post("/","store");
                Route::patch("/{attendance}", "update");
            });

        // employee onboarding route
        Route::prefix("employee_onboarding")->group(function() {
            Route::controller(EmployeeOnboardingController::class)->group(function() {
                Route::get("/", "index");
                Route::get("/{employee_onboarding}", "show");
            });
        });

        // employee onboarding policy acknowledgement
        Route::prefix("employee_onboarding_policy_acknowledgement")->group(function() {
            Route::controller(EmployeeOnboardingPolicyAcknowledgementController::class)->group(function() {
                Route::post("/", "store");
            });
        });

        // employee onboarding required documents
        Route::prefix("employee_onboarding_required_documents")->group(function() {
            Route::controller(EmployeeOnboardingRequiredDocumentsController::class)->group(function() {
                Route::post("/", "store");
                Route::patch("/{required_document}", "update");
                Route::delete("/{required_document}", "destroy");
            });
        });

        // employee performance review route
        Route::prefix("employee_performance_review")->group(function() {
            Route::controller(EmployeePerformanceReviewController::class)->group(function() {
                Route::get("/", "index");
                Route::get("/{employee_performance_review}", "show");
            });
        });

        // employee performance review response
        Route::prefix("employee_performance_review_response")->group(function() {
            Route::controller(EmployeePerformanceReviewResponseController::class)->group(function() {
                Route::post("/", "store");
            });
        });

        // employee leave balance route
        Route::prefix("leave_balance")->group(function() {
            Route::controller(EmployeeLeaveBalanceController::class)->group(function() {
                Route::get("/", "index");
            });
        });

        // employee leave request route
        Route::prefix("leave_request")->group(function() {
            Route::controller(EmployeeLeaveRequestController::class)->group(function() {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{leave_request}", "show");
                Route::patch("/{leave_request}", "update");
                Route::delete("/{leave_request}", "destroy");
            });
        });

        // employee training
        Route::prefix("employee_training")->group(function() {
            Route::controller(EmployeeTrainingController::class)->group(function() {
                Route::get("/", "index");
                Route::get("/{employee_training}", "show");
            });
        });

        // employee training review response
        Route::prefix("employee_training_review_response")->group(function() {
            Route::controller(EmployeeTrainingReviewResponseController::class)->group(function() {
                Route::post("/", "store");
            });
        });

        // employee document
        Route::prefix("document")->group(function() {
            Route::controller(DocumentController::class)->group(function() {
                Route::get("/", "index");
                Route::get("/{document}", "show");
            });
        });

        // employee document folder
        Route::prefix("folder")->group(function() {
            Route::controller(FolderController::class)->group(function() {
                Route::get("/{folder}", "show");
            });
        });

        Route::prefix("profile")->group(function() {
            Route::controller(EmployeeController::class)->group(function() {
                Route::get("/{employee}", "show");
                Route::patch("/{employee}", "update");
            });
        });
    });

    // admin routes
    Route::middleware(["auth", "user_token:admin"])->prefix("admin")->group(function() {
        Route::prefix("auth")->group(function() {
            Route::controller(BaseAuthController::class)->group(function() {
                Route::post("/change_password", "change_password");
                Route::post("/logout", "logout");
            });
        });

        Route::prefix("/dashboard")->group(function() {
            Route::controller(AdminDashboardController::class)->group(function() {
                Route::get("/", "index");
            });
        });

        Route::prefix("hr")->group(function() {
            Route::controller(BaseAuthController::class)->group(function() {
                Route::post("/register", "register");
            });

            Route::controller(AdminHRController::class)->group(function() {
                Route::get("/", "index");
                Route::patch("/{hr}", "update");
            });
        });

        Route::prefix("profile")->group(function() {
            Route::controller(AdminController::class)->group(function() {
                Route::get("/{admin}", "show");
                Route::patch("/{admin}", "update");
            });
        });
    });
});
