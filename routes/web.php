<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminHRController;

use App\Http\Controllers\HR\HRController;
use App\Http\Controllers\HR\HRDashboardController;
use App\Http\Controllers\HR\HRAttendanceController;
use App\Http\Controllers\HR\HREmployeeController;
use App\Http\Controllers\HR\HREmployeeLeaveRequestController;
use App\Http\Controllers\HR\HRLeaveTypeController;
use App\Http\Controllers\HR\HROnboardingController;
use App\Http\Controllers\HR\HRPerformanceReviewController;
use App\Http\Controllers\HR\HRTrainingController;
use App\Http\Controllers\HR\HREmployeeOnboardingController;
use App\Http\Controllers\HR\HREmployeePerformanceReviewController;
use App\Http\Controllers\HR\HREmployeeTrainingController;
use App\Http\Controllers\HR\HREmployeeLeaveBalanceController;
use App\Http\Controllers\HR\HRLeaveBalanceController;
use App\Http\Controllers\HR\HRLeaveRequestController;

use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Http\Controllers\Employee\EmployeeOnboardingController;
use App\Http\Controllers\Employee\EmployeeOnboardingPolicyAcknowledgementController;
use App\Http\Controllers\Employee\EmployeeOnboardingRequiredDocumentsController;
use App\Http\Controllers\Employee\EmployeePerformanceReviewController;
use App\Http\Controllers\Employee\EmployeePerformanceReviewResponseController;
use App\Http\Controllers\Employee\EmployeeLeaveBalanceController;
use App\Http\Controllers\Employee\EmployeeLeaveRequestController;
use App\Http\Controllers\Employee\EmployeeTrainingController;
use App\Http\Controllers\Employee\EmployeeTrainingReviewResponseController;

use App\Http\Controllers\Base\AuthController;
use App\Http\Controllers\Base\DocumentController;
use App\Http\Controllers\Base\FolderController;

use Illuminate\Support\Facades\Route;

Route::prefix("api")->group(function () {

    Route::get('/', fn() => redirect(env("NEST_URL")));

    Route::get('csrf-cookie', fn() => response()->json(["token" => csrf_token()]));

    // auth
    Route::controller(AuthController::class)
        ->prefix("auth")
        ->group(function () {
            Route::post("/login", "login");
            Route::post("/register", "register");
            Route::patch('/verify', "verify");
            Route::post("/verification-notification", "resend_verification")->middleware(["auth", "throttle:6,1"]);
            Route::post("/forgot-password", "forgot_password");
            Route::patch("/reset-password", "reset_password");
        });

    // hr routes
    Route::middleware(["auth", "user_token:hr"])->prefix("hr")->group(function () {

        // dashboard route
        Route::controller(HRDashboardController::class)
            ->prefix("/dashboard")
            ->group(function () {
                Route::get("/", "index");
            });

        // session routes
        Route::controller(AuthController::class)
            ->prefix("auth")
            ->group(function () {
                Route::post("/logout", "logout");
                Route::patch("/change-password", "change_password");
            });

        // employee route
        Route::controller(HREmployeeController::class)
            ->prefix("employee")
            ->group(function () {
                Route::get("/", "index");
                Route::get("/{employee}", "show");
            });

        // leave route
        Route::controller(HRLeaveTypeController::class)
            ->prefix("leave_type")
            ->group(function () {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{leaveType}", "show");
                Route::patch("/{leaveType}", "update");
                Route::delete("/{leaveType}", "destroy");
            });

        // leave balance route
        Route::controller(HRLeaveBalanceController::class)
            ->prefix("leave_balance")
            ->group(function () {
                Route::get("/", "index");
            });

        // employee and hr leave balance route
        Route::controller(HREmployeeLeaveBalanceController::class)->prefix("employee_leave_balance")->group(function () {
            Route::get("/", "index");
            Route::post("/", "store");
        });

        // leave request
        Route::controller(HRLeaveRequestController::class)
            ->prefix("leave_request")
            ->group(function () {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{leaveRequest}", "show");
                Route::patch("/{leaveRequest}", "update");
                Route::delete("/{leaveRequest}", "destroy");
            });

        // employee leave request route
        Route::controller(HREmployeeLeaveRequestController::class)
            ->prefix("employee_leave_request")
            ->group(function () {
                Route::patch("/{leaveRequest}", "update");
            });

        // onboarding route
        Route::controller(HROnboardingController::class)
            ->prefix("onboarding")
            ->group(function () {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{onboarding}", "show");
                Route::patch("/{onboarding}", "update");
                Route::delete("/{onboarding}", "destroy");
            });

        // employee onboarding route
        Route::controller(HREmployeeOnboardingController::class)
            ->prefix("employee_onboarding")
            ->group(function () {
                Route::get("/", "index");
                Route::post("/", "store");
            });

        // attendance route
        Route::controller(HRAttendanceController::class)
            ->prefix("attendance")
            ->group(function () {
                Route::get("/", "index");
                Route::get("/{attendance}", "show");
            });

        // performance review route
        Route::controller(HRPerformanceReviewController::class)
            ->prefix("performance_review")
            ->group(function () {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{performanceReview}", "show");
                Route::patch("/{performanceReview}", "update");
                Route::delete("/{performanceReview}", "destroy");
            });

        // employee performance review route
        Route::controller(HREmployeePerformanceReviewController::class)
            ->prefix("employee_performance_review")
            ->group(function () {
                Route::get("/", "index");
                Route::post("/", "store");
            });

        // training route
        Route::controller(HRTrainingController::class)
            ->prefix("training")
            ->group(function () {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{training}", "show");
                Route::patch("/{training}", "update");
                Route::delete("/{training}", "destroy");
            });

        // employee training route
        Route::controller(HREmployeeTrainingController::class)
            ->prefix("employee_training")
            ->group(function () {
                Route::get("/", "index");
                Route::post("/", "store");
            });

        // document route
        Route::controller(DocumentController::class)
            ->prefix("document")
            ->group(function () {
                Route::get("/", "index");
                Route::get("/{document}", "show");
                Route::patch("/{document}", "update");
                Route::post("/", "store");
                Route::delete("/{document}", "destroy");
            });

        // document folder route
        Route::controller(FolderController::class)
            ->prefix("folder")
            ->group(function () {
                Route::get("/paths", "get_folder_paths");
                Route::get("/{folder}", "show");
                Route::patch("/{folder}", "update");
                Route::post("/", "store");
                Route::delete("/{folder}", "destroy");
            });

        // hr profile
        Route::controller(HRController::class)
            ->prefix("profile")
            ->group(function () {
                Route::get("/{hr}", "show");
                Route::patch("/{hr}", "update");
            });
    });

    // employee routes
    Route::middleware(["auth", "user_token:employee"])->prefix("employee")->group(function () {

        // employee dashboard
        Route::controller(EmployeeDashboardController::class)
            ->prefix("dashboard")
            ->group(function () {
                Route::get("/", "index");
            });

        // employee auth
        Route::controller(AuthController::class)
            ->prefix("auth")
            ->group(function () {
                Route::post("/logout", "logout");
                Route::patch("/change-password", "change_password");
            });

        // attendance route
        Route::controller(EmployeeAttendanceController::class)
            ->prefix("attendance")
            ->group(function () {
                Route::get("/{attendance}", "show");
                Route::post("/", "store");
                Route::patch("/{attendance}", "update");
            });

        // employee onboarding route
        Route::controller(EmployeeOnboardingController::class)
            ->prefix("employee_onboarding")
            ->group(function () {
                Route::get("/", "index");
                Route::get("/{employeeOnboarding}", "show");
            });

        // employee onboarding policy acknowledgement
        Route::controller(EmployeeOnboardingPolicyAcknowledgementController::class)
            ->prefix("employee_onboarding_policy_acknowledgement")
            ->group(function () {
                Route::post("/", "store");
            });

        // employee onboarding required documents
        Route::controller(EmployeeOnboardingRequiredDocumentsController::class)
            ->prefix("employee_onboarding_required_documents")
            ->group(function () {
                Route::post("/", "store");
                Route::patch("/{requiredDocument}", "update");
                Route::delete("/{requiredDocument}", "destroy");
            });

        // employee performance review route
        Route::controller(EmployeePerformanceReviewController::class)
            ->prefix("employee_performance_review")
            ->group(function () {
                Route::get("/", "index");
                Route::get("/{employeePerformanceReview}", "show");
            });

        // employee performance review response
        Route::controller(EmployeePerformanceReviewResponseController::class)
            ->prefix("employee_performance_review_response")
            ->group(function () {
                Route::post("/", "store");
            });

        // employee leave balance route
        Route::controller(EmployeeLeaveBalanceController::class)
            ->prefix("leave_balance")
            ->group(function () {
                Route::get("/", "index");
            });

        // employee leave request route
        Route::controller(EmployeeLeaveRequestController::class)
            ->prefix("leave_request")
            ->group(function () {
                Route::get("/", "index");
                Route::post("/", "store");
                Route::get("/{leaveRequest}", "show");
                Route::patch("/{leaveRequest}", "update");
                Route::delete("/{leaveRequest}", "destroy");
            });

        // employee training
        Route::controller(EmployeeTrainingController::class)
            ->prefix("employee_training")
            ->group(function () {
                Route::get("/", "index");
                Route::get("/{employeeTraining}", "show");
            });

        // employee training review response
        Route::controller(EmployeeTrainingReviewResponseController::class)
            ->prefix("employee_training_review_response")
            ->group(function () {
                Route::post("/", "store");
            });

        // employee document
        Route::controller(DocumentController::class)
            ->prefix("document")
            ->group(function () {
                Route::get("/", "index");
                Route::get("/{document}", "show");
            });

        // employee document folder
        Route::controller(FolderController::class)
            ->prefix("folder")
            ->group(function () {
                Route::get("/{folder}", "show");
            });

        // employee profile controller
        Route::controller(EmployeeController::class)
            ->prefix("profile")
            ->group(function () {
                Route::get("/{employee}", "show");
                Route::patch("/{employee}", "update");
            });
    });

    // admin routes
    Route::middleware(["auth", "user_token:admin"])->prefix("admin")->group(function () {

        // admin auth
        Route::controller(AuthController::class)
            ->prefix("auth")
            ->group(function () {
                Route::post("/change_password", "change_password");
                Route::post("/logout", "logout");
            });

        // admin dashboard
        Route::controller(AdminDashboardController::class)
            ->prefix("dashboard")
            ->group(function () {
                Route::get("/", "index");
            });

        // admin hr auth
        Route::controller(AuthController::class)
            ->prefix("hr")
            ->group(function () {
                Route::post("/register", "register");
            });

        // admin hr
        Route::controller(AdminHRController::class)
            ->prefix("hr")
            ->group(function () {
                Route::get("/", "index");
                Route::patch("/{hr}", "update");
            });

        // admin profile
        Route::controller(AdminController::class)
            ->prefix("profile")
            ->group(function () {
                Route::get("/{admin}", "show");
                Route::patch("/{admin}", "update");
            });
    });
});
