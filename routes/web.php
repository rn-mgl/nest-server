<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminHRController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\HR\HRController;
use App\Http\Controllers\HR\HRDashboardController;
use App\Http\Controllers\HR\HRUserController;
use App\Http\Controllers\HR\HRUserLeaveRequestController;
use App\Http\Controllers\HR\HRLeaveTypeController;
use App\Http\Controllers\HR\HROnboardingController;
use App\Http\Controllers\HR\HRPerformanceReviewController;
use App\Http\Controllers\HR\HRTrainingController;
use App\Http\Controllers\HR\HRUserOnboardingController;
use App\Http\Controllers\HR\HRUserPerformanceReviewController;
use App\Http\Controllers\HR\HRUserTrainingController;
use App\Http\Controllers\HR\HRUserLeaveBalanceController;

use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeOnboardingController;
use App\Http\Controllers\Employee\EmployeeOnboardingPolicyAcknowledgementController;
use App\Http\Controllers\Employee\EmployeeOnboardingRequiredDocumentsController;
use App\Http\Controllers\Employee\EmployeePerformanceReviewController;
use App\Http\Controllers\Employee\EmployeePerformanceReviewResponseController;
use App\Http\Controllers\Employee\EmployeeLeaveBalanceController;
use App\Http\Controllers\Employee\EmployeeLeaveRequestController;
use App\Http\Controllers\Employee\EmployeeTrainingController;
use App\Http\Controllers\Employee\EmployeeTrainingReviewResponseController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PerformanceReviewController;
use App\Http\Controllers\TrainingController;
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
            Route::post("/verification-notification", "resendVerification")->middleware(["auth", "throttle:6,1"]);
            Route::post("/forgot-password", "forgotPassword");
            Route::patch("/reset-password", "resetPassword");
        });

    // shared
    Route::middleware(["auth", "user_token"])->group(function () {

        // dashboard
        Route::controller(DashboardController::class)
            ->prefix("dashboard")
            ->group(function () {
                Route::get("/", "index");
            });

        // auth
        Route::controller(AuthController::class)
            ->prefix("auth")
            ->group(function () {
                Route::post("/logout", "logout");
                Route::patch("/change-password", "changePassword");
            });

        // attendance
        Route::controller(AttendanceController::class)
            ->prefix("attendance")
            ->group(function () {
                Route::get("/", "index")->middleware(["check_permission:read.attendance"]);
                Route::post("/", "store");
                Route::get("/{attendance}", "show");
                Route::patch("/{attendance}", "update");
            });

        // onboarding
        Route::controller(OnboardingController::class)
            ->prefix("onboarding")
            ->group(function () {
                // route for the assigned onboardings
                Route::prefix("assigned")
                    ->group(function () {
                    Route::get("/", "assignedIndex");
                    Route::get("/{userOnboarding}", "assignedShow");
                });

                // route for the resource onboardings
                Route::prefix("resource")
                    ->group(function () {
                    Route::get("/", "resourceIndex")->middleware(["check_permission:read.onboarding_resource"]);
                    Route::post("/", "resourceStore")->middleware(["check_permission:create.onboarding_resource"]);
                    Route::get("/{onboarding}", "resourceShow")->middleware(["check_permission:read.onboarding_resource"]);
                    Route::patch("/{onboarding}", "resourceUpdate")->middleware(["check_permission:update.onboarding_resource"]);
                    Route::delete("/{onboarding}", "resourceDestroy")->middleware(["check_permission:delete.onboarding_resource"]);
                });

                // route for the assigning of onboardings
                Route::prefix("assignment")
                    ->middleware(["check_permission:assign.onboarding_resource"])
                    ->group(function () {
                    Route::get("/", "assignmentIndex");
                    Route::post("/", "assignmentStore");
                });
            });

        // leave types
        Route::controller(LeaveTypeController::class)
            ->prefix("leave-type")
            ->group(function () {
                // route for the assigned leave types (leave balance)
                Route::prefix("assigned")
                    ->group(function () {
                    Route::get("/", "assignedIndex");
                });

                // route for the resource leave types
                Route::prefix("resource")
                    ->group(function () {
                    Route::get("/", "resourceIndex")->middleware(["check_permission:read.leave_type_resource"]);
                    Route::post("/", "resourceStore")->middleware(["check_permission:create.leave_type_resource"]);
                    Route::get("/{leaveType}", "resourceShow")->middleware(["check_permission:read.leave_type_resource"]);
                    Route::patch("/{leaveType}", "resourceUpdate")->middleware(["check_permission:update.leave_type_resource"]);
                    Route::delete("/{leaveType}", "resourceDestroy")->middleware(["check_permission:delete.leave_type_resource"]);
                });

                // route for the assigning of leave types (leave balance)
                Route::prefix("assignment")
                    ->middleware(["check_permission:assign.leave_type_resource"])
                    ->group(function () {
                    Route::get("/", "assignmentIndex");
                    Route::post("/", "assignmentStore");
                });
            });

        // leave requests
        Route::controller(LeaveRequestController::class)
            ->prefix("leave-request")
            ->group(function () {
                // route for the resource leave types
                Route::prefix("resource")
                    ->group(function () {
                    Route::get("/", "resourceIndex");
                    Route::post("/", "resourceStore");
                    Route::get("/{leaveRequest}", "resourceShow");
                    Route::patch("/{leaveRequest}", "resourceUpdate");
                    Route::delete("/{leaveRequest}", "resourceDestroy");
                });
            });

        // performance reviews
        Route::controller(PerformanceReviewController::class)
            ->prefix("performance-review")
            ->group(function () {

                Route::prefix("assigned")
                    ->group(function () {
                        Route::get("/", "assignedIndex");
                        Route::get("/{performanceReview}", "assignedShow");
                    });

                Route::prefix("resource")
                    ->group(function () {
                        Route::get("/", "resourceIndex")->middleware(["check_permission:read.performance_review_resource"]);
                        Route::post("/", "resourceStore")->middleware(["check_permission:create.performance_review_resource"]);
                        Route::get("/{performanceReview}", "resourceShow")->middleware(["check_permission:read.performance_review_resource"]);
                        Route::patch("/{performanceReview}", "resourceUpdate")->middleware(["check_permission:update.performance_review_resource"]);
                        Route::delete("/{performanceReview}", "resourceDestroy")->middleware(["check_permission:delete.performance_review_resource"]);
                    });

                Route::prefix("assignment")
                    ->middleware(["check_permission:assign.performance_review_resource"])
                    ->group(function () {
                        Route::get("/", "assignmentIndex");
                        Route::post("/", "assignmentStore");
                    });

            });

        // trainings
        Route::controller(TrainingController::class)
            ->prefix("training")
            ->group(function () {

                // route for assigned training
                Route::prefix("assigned")
                    ->group(function () {
                    Route::get("/", "assignedIndex");
                    Route::get("/{training}", "assignedShow");
                });

                // route for training resource
                Route::prefix("resource")
                    ->group(function () {
                    Route::get("/", "resourceIndex")->middleware(["check_permission:read.training_resource"]);
                    Route::post("/", "resourceStore")->middleware(["check_permission:create.training_resource"]);
                    Route::get("/{training}", "resourceShow")->middleware(["check_permission:read.training_resource"]);
                    Route::patch("/{training}", "resourceUpdate")->middleware(["check_permission:update.training_permission"]);
                    Route::delete("/{training}", "resourceDestroy")->middleware(["check_permission:delete.training_resource"]);
                });

                // route for training assignment
                Route::prefix("assignment")
                    ->middleware(["check_permission:assign.training_resource"])
                    ->group(function () {
                    Route::get("/", "assignmentIndex");
                    Route::post("/", "assignmentStore");
                });

            });

        // documents
        Route::controller(DocumentController::class)
            ->prefix("document")
            ->group(function () {

                // route for document resource
                Route::prefix("resource")
                    ->group(function () {
                    Route::get("/", "resourceIndex");
                    Route::get("/{document}", "resourceShow");
                    Route::post("/", "resourceStore")->middleware(["check_permission:create.document_resource"]);
                    Route::patch("/{document}", "resourceUpdate")->middleware(["check_permission:update.document_resource"]);
                    Route::delete("/{document}", "resourceDestroy")->middleware(["check_permission:delete.document_resource"]);
                });

            });

        // folders
        Route::controller(FolderController::class)
            ->prefix("folder")
            ->group(function () {

                // route for folder resource
                Route::prefix("resource")
                    ->group(function () {
                    Route::get("/paths", "getFolderPaths");
                    Route::get("/{folder}", "resourceShow");
                    Route::post("/", "resourceStore")->middleware(["check_permission:create.folder_resource"]);
                    Route::patch("/{folder}", "resourceUpdate")->middleware(["check_permission:update.folder_resource"]);
                    Route::delete("/{folder}", "resourceDestroy")->middleware(["check_permission:delete.folder_resource"]);
                });

            });

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

        // hr to user route
        Route::controller(HRUserController::class)
            ->prefix("user")
            ->group(function () {
                Route::get("/", "index");
                Route::get("/{user}", "show");
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

        // hr to user leave balance route
        Route::controller(HRUserLeaveBalanceController::class)->prefix("user_leave_balance")->group(function () {
            Route::get("/", "index");
            Route::post("/", "store");
        });

        // hr to user leave request route
        Route::controller(HRUserLeaveRequestController::class)
            ->prefix("user_leave_request")
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

        // hr to user onboarding route
        Route::controller(HRUserOnboardingController::class)
            ->prefix("user_onboarding")
            ->group(function () {
                Route::get("/", "index");
                Route::post("/", "store");
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

        // hr to user performance review route
        Route::controller(HRUserPerformanceReviewController::class)
            ->prefix("user_performance_review")
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

        // hr to user training route
        Route::controller(HRUserTrainingController::class)
            ->prefix("user_training")
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
