<?php

use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminHRController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentFolderController;
use App\Http\Controllers\Employee\EmployeeLeaveBalanceController;
use App\Http\Controllers\Employee\EmployeeLeaveRequestController;
use App\Http\Controllers\HR\HRAttendanceController;
use App\Http\Controllers\HR\HREmployeeController;
use App\Http\Controllers\HR\HRLeaveTypeController;
use App\Http\Controllers\HR\HROnboardingController;
use App\Http\Controllers\HR\HRPerformanceReviewController;
use App\Http\Controllers\HR\HRTrainingController;
use App\Http\Controllers\BaseAuthController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Http\Controllers\Employee\EmployeeOnboardingController;
use App\Http\Controllers\Employee\EmployeeOnboardingPolicyAcknowledgementController;
use App\Http\Controllers\Employee\EmployeeOnboardingRequiredDocumentsController;
use App\Http\Controllers\Employee\EmployeePerformanceReviewController;
use App\Http\Controllers\Employee\EmployeePerformanceReviewResponseController;
use App\Http\Controllers\Employee\EmployeeTrainingController;
use App\Http\Controllers\EmployeeTrainingReviewResponseController;
use App\Http\Controllers\HR\HRController;
use App\Http\Controllers\HR\HREmployeeOnboardingController;
use App\Http\Controllers\HR\HREmployeePerformanceReviewController;
use App\Http\Controllers\HR\HREmployeeTrainingController;
use App\Http\Controllers\HR\HRLeaveBalanceController;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::prefix("api")->group(function() {

    Route::get('/', function () {
        return redirect(env("NEST_URL"));
    });

    Route::get('csrf-cookie', function() {
        return response()->json(["token" => csrf_token()]);
    });

    // base auth
    Route::prefix("auth")->group(function() {
        Route::controller(BaseAuthController::class)->group(function() {
            Route::post("/login", "login");
            Route::post("/register", "register");
            Route::patch('/verify', "verify");
            Route::post("/verification-notification", "resend_verification")->middleware(["auth:base", "throttle:6,1"]);
            Route::post("/forgot-password", "forgot_password");
            Route::patch("/reset-password", "reset_password");
        });
    });

    // admin auth
    Route::prefix("admin_auth")->group(function() {
        Route::controller(AdminAuthController::class)->group(function() {
            Route::post("/login", "login");
            Route::patch("/verify", "verify");
            Route::post("/verification-notification", "resend_verification")->middleware(["auth:admin", "throttle:6,1"]);
            Route::post("/forgot-password", "forgot_password");
            Route::patch("/reset-password", "reset_password");
        });
    });

    // hr routes
    Route::middleware(["auth:base", "valid_user_token"])->prefix("hr")->group(function() {
        // session routes
        Route::prefix("auth")->group(function() {
            Route::controller(BaseAuthController::class)->group(function() {
                Route::post("/logout", "logout")->can("updateHR", User::class);
                Route::patch("/change-password", "change_password")->can("updateHR", User::class);
            });
        });

        // employee route
        Route::prefix("employee")->group(function() {
            Route::controller(HREmployeeController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::get("/{employee}", "show")->can("updateHR", User::class);
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

        // leave balance route
        Route::prefix("leave_balance")->group(function() {
            Route::controller(HRLeaveBalanceController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
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

        // employee onboarding route
        Route::prefix("employee_onboarding")->group(function() {
            Route::controller(HREmployeeOnboardingController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
            });
        });

        // attendance route
        Route::prefix('attendance')->group(function() {
            Route::controller(HRAttendanceController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::get("/{attendance}", "show")->can("updateHR", User::class);
            });
        });

        // performance review route
        Route::prefix('performance_review')->group(function() {
            Route::controller(HRPerformanceReviewController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::get("/{performance_review}", "show")->can("updateHR", User::class);
                Route::patch("/{performance_review}", "update")->can("updateHR", User::class);
                Route::delete("/{performance_review}", "destroy")->can("updateHR", User::class);
            });
        });

        // employee performance review route
        Route::prefix("employee_performance_review")->group(function() {
            Route::controller(HREmployeePerformanceReviewController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
            });
        });

        // training route
        Route::prefix('training')->group(function() {
            Route::controller(HRTrainingController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::get("/{training}", "show")->can("updateHR", User::class);
                Route::patch("/{training}", "update")->can("updateHR", User::class);
                Route::delete("/{training}", "destroy")->can("updateHR", User::class);
            });
        });

        // employee training route
        Route::prefix('employee_training')->group(function() {
            Route::controller(HREmployeeTrainingController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
            });
        });

        // document route
        Route::prefix("document")->group(function() {
            Route::controller(DocumentController::class)->group(function() {
                Route::get("/", "index")->can("updateHR", User::class);
                Route::get("/{document}", "show")->can("updateHR", User::class);
                Route::patch("/{document}", "update")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::delete("/{document}", "destroy")->can("updateHR", User::class);
            });
        });

        // document folder route
        Route::prefix('document_folder')->group(function() {
            Route::controller(DocumentFolderController::class)->group(function() {
                Route::get("/paths", "get_parent_paths")->can("updateHR", User::class);
                Route::get("/{document_folder}", "show")->can("updateHR", User::class);
                Route::patch("/{document_folder}", "update")->can("updateHR", User::class);
                Route::post("/", "store")->can("updateHR", User::class);
                Route::delete("/{document_folder}", "destroy")->can("updateHR", User::class);
            });
        });

        Route::prefix("profile")->group(function() {
            Route::controller(HRController::class)->group(function() {
                Route::get("/{hr}", "show")->can("updateHR", User::class);
                Route::patch("/{hr}", "update")->can("updateHR", User::class);
            });
        });
    });

    // employee routes
    Route::middleware(["auth:base", "valid_user_token"])->prefix("employee")->group(function() {
        Route::prefix("auth")->group(function() {
            Route::controller(BaseAuthController::class)->group(function() {
                Route::post("/logout", "logout")->can("updateEmployee", User::class);
                Route::patch("/change-password", "change_password")->can("updateEmployee", User::class);
            });
        });

        // attendance route
        Route::prefix("attendance")->group(function() {
            Route::controller(EmployeeAttendanceController::class)->group(function() {
                Route::get("/{attendance}","show")->can("updateEmployee", User::class);
                Route::post("/","store")->can("updateEmployee", User::class);
            });
        });

        // employee onboarding route
        Route::prefix("employee_onboarding")->group(function() {
            Route::controller(EmployeeOnboardingController::class)->group(function() {
                Route::get("/", "index")->can("updateEmployee", User::class);
                Route::get("/{employee_onboarding}", "show")->can("updateEmployee", User::class);
            });
        });

        // employee onboarding policy acknowledgement
        Route::prefix("employee_onboarding_policy_acknowledgement")->group(function() {
            Route::controller(EmployeeOnboardingPolicyAcknowledgementController::class)->group(function() {
                Route::post("/", "store")->can("updateEmployee", User::class);
            });
        });

        // employee onboarding required documents
        Route::prefix("employee_onboarding_required_documents")->group(function() {
            Route::controller(EmployeeOnboardingRequiredDocumentsController::class)->group(function() {
                Route::post("/", "store")->can("updateEmployee", User::class);
                Route::patch("/{required_document_id}", "update")->can("updateEmployee", User::class);
                Route::delete("/{required_document_id}", "destroy")->can("updateEmployee", User::class);
            });
        });

        // employee performance review route
        Route::prefix("employee_performance_review")->group(function() {
            Route::controller(EmployeePerformanceReviewController::class)->group(function() {
                Route::get("/", "index")->can("updateEmployee", User::class);
                Route::get("/{employee_performance_review}", "show")->can("updateEmployee", User::class);
            });
        });

        // employee performance review response
        Route::prefix("employee_performance_review_response")->group(function() {
            Route::controller(EmployeePerformanceReviewResponseController::class)->group(function() {
                Route::post("/", "store")->can("updateEmployee", User::class);
            });
        });

        // employee leave balance route
        Route::prefix("leave_balance")->group(function() {
            Route::controller(EmployeeLeaveBalanceController::class)->group(function() {
                Route::get("/", "index")->can("updateEmployee", User::class);
            });
        });

        // employee leave request route
        Route::prefix("leave_request")->group(function() {
            Route::controller(EmployeeLeaveRequestController::class)->group(function() {
                Route::post("/", "store")->can("updateEmployee", User::class);
            });
        });

        // employee training
        Route::prefix("employee_training")->group(function() {
            Route::controller(EmployeeTrainingController::class)->group(function() {
                Route::get("/", "index")->can("updateEmployee", User::class);
                Route::get("/{employee_training}", "show")->can("updateEmployee", User::class);
            });
        });

        // employee training review response
        Route::prefix("employee_training_review_response")->group(function() {
            Route::controller(EmployeeTrainingReviewResponseController::class)->group(function() {
                Route::post("/", "store")->can("updateEmployee", User::class);
            });
        });

        // employee document
        Route::prefix("document")->group(function() {
            Route::controller(DocumentController::class)->group(function() {
                Route::get("/", "index")->can("updateEmployee", User::class);
                Route::get("/{document}", "show")->can("updateEmployee", User::class);
            });
        });

        // employee document folder
        Route::prefix("document_folder")->group(function() {
            Route::controller(DocumentFolderController::class)->group(function() {
                Route::get("/{document_folder}", "show")->can("updateEmployee", User::class);
            });
        });

        Route::prefix("profile")->group(function() {
            Route::controller(EmployeeController::class)->group(function() {
                Route::get("/{employee}", "show")->can("updateEmployee", User::class);
                Route::patch("/{employee}", "update")->can("updateEmployee", User::class);
            });
        });
    });

    // admin routes
    Route::middleware(["auth:admin", "valid_admin_token"])->prefix("admin")->group(function() {
        Route::prefix("auth")->group(function() {
            Route::controller(AdminAuthController::class)->group(function() {
                Route::post("/change_password", "change_password");
                Route::post("/logout", "logout");
            });
        });

        Route::prefix("hr")->group(function() {
            Route::controller(BaseAuthController::class)->group(function() {
                Route::post("/register", "register")->can("update", Admin::class);
            });

            Route::controller(AdminHRController::class)->group(function() {
                Route::get("/", "index")->can("update", Admin::class);
                Route::patch("/{hr}", "update")->can("update", Admin::class);
            });
        });

        Route::prefix("profile")->group(function() {
            Route::controller(AdminController::class)->group(function() {
                Route::get("/{admin}", "show")->can("update", Admin::class);
                Route::patch("/{admin}", "update")->can("update", Admin::class);
            });
        });
    });
});
