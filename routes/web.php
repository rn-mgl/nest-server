<?php

// Base / Shared controllers
use App\Http\Controllers\Base\AuthController;
use App\Http\Controllers\Base\DashboardController;
use App\Http\Controllers\Base\AttendanceController;
use App\Http\Controllers\Document\DocumentController;
use App\Http\Controllers\Document\FolderController;
use App\Http\Controllers\UserController;

// Privilege / Roles
use App\Http\Controllers\Privilege\HRController;
use App\Http\Controllers\Privilege\ManagementController;

// Onboarding
use App\Http\Controllers\Onboarding\ResourceOnboardingController;
use App\Http\Controllers\Onboarding\AssignedOnboardingController;
use App\Http\Controllers\Onboarding\AssignmentOnboardingController;

// Performance Reviews
use App\Http\Controllers\Performance\ResourcePerformanceReviewController;
use App\Http\Controllers\Performance\AssignedPerformanceReviewController;
use App\Http\Controllers\Performance\AssignmentPerformanceReviewController;

// Trainings
use App\Http\Controllers\Training\ResourceTrainingController;
use App\Http\Controllers\Training\AssignedTrainingController;
use App\Http\Controllers\Training\AssignmentTrainingController;

// Leave
use App\Http\Controllers\Leave\LeaveTypeController;
use App\Http\Controllers\Leave\LeaveRequestController;
use App\Http\Controllers\Permission\AssignmentPermissionController;
use App\Http\Controllers\Permission\ResourcePermissionController;
use App\Http\Controllers\Role\AssignmentRoleController;
use App\Http\Controllers\Role\ResourceRoleController;
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
        Route::prefix("onboarding")
            ->group(function () {
                // route for the assigned onboardings
                Route::controller(AssignedOnboardingController::class)
                    ->prefix("assigned")
                    ->group(function () {
                    Route::get("/", "assignedIndex");
                    Route::get("/{userOnboarding}", "assignedShow");
                    Route::patch("/{userOnboarding}", "assignedUpdate");
                    Route::post("/policy-acknowledgement", "policyAcknowledgementStore");
                    Route::post("/required-document", "requiredDocumentStore");
                    Route::patch("/required-document/{requiredDocument}", "requiredDocumentUpdate");
                    Route::delete("/required-document/{requiredDocument}", "requiredDocumentDestroy");
                });

                // route for the resource onboardings
                Route::controller(ResourceOnboardingController::class)
                    ->prefix("resource")
                    ->group(function () {
                    Route::get("/", "resourceIndex")->middleware(["check_permission:read.onboarding_resource"]);
                    Route::post("/", "resourceStore")->middleware(["check_permission:create.onboarding_resource"]);
                    Route::get("/{onboarding}", "resourceShow")->middleware(["check_permission:read.onboarding_resource"]);
                    Route::patch("/{onboarding}", "resourceUpdate")->middleware(["check_permission:update.onboarding_resource"]);
                    Route::delete("/{onboarding}", "resourceDestroy")->middleware(["check_permission:delete.onboarding_resource"]);
                });

                // route for the assigning of onboardings
                Route::controller(AssignmentOnboardingController::class)
                    ->prefix("assignment")
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
                // route for the assigned leave requests (for hr to approve request)
                Route::prefix("assigned")
                    ->group(function () {
                    Route::patch("/{leaveRequest}", "assignedUpdate")->middleware(["check_permission:update.leave_request_resource"]);
                });

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
        Route::prefix("performance-review")
            ->group(function () {

                Route::controller(AssignedPerformanceReviewController::class)
                    ->prefix("assigned")
                    ->group(function () {
                        Route::get("/", "assignedIndex");
                        Route::get("/{performanceReview}", "assignedShow");
                        Route::post("/review-response", "reviewResponseStore");
                    });

                Route::controller(ResourcePerformanceReviewController::class)
                    ->prefix("resource")
                    ->group(function () {
                        Route::get("/", "resourceIndex")->middleware(["check_permission:read.performance_review_resource"]);
                        Route::post("/", "resourceStore")->middleware(["check_permission:create.performance_review_resource"]);
                        Route::get("/{performanceReview}", "resourceShow")->middleware(["check_permission:read.performance_review_resource"]);
                        Route::patch("/{performanceReview}", "resourceUpdate")->middleware(["check_permission:update.performance_review_resource"]);
                        Route::delete("/{performanceReview}", "resourceDestroy")->middleware(["check_permission:delete.performance_review_resource"]);
                    });

                Route::controller(AssignmentPerformanceReviewController::class)
                    ->prefix("assignment")
                    ->middleware(["check_permission:assign.performance_review_resource"])
                    ->group(function () {
                        Route::get("/", "assignmentIndex");
                        Route::post("/", "assignmentStore");
                    });

            });

        // trainings
        Route::prefix("training")
            ->group(function () {

                // route for assigned training
                Route::controller(AssignedTrainingController::class)
                    ->prefix("assigned")
                    ->group(function () {
                    Route::get("/", "assignedIndex");
                    Route::get("/{training}", "assignedShow");
                    Route::post("/review-response", "reviewResponseStore");
                });

                // route for training resource
                Route::controller(ResourceTrainingController::class)
                    ->prefix("resource")
                    ->group(function () {
                    Route::get("/", "resourceIndex")->middleware(["check_permission:read.training_resource"]);
                    Route::post("/", "resourceStore")->middleware(["check_permission:create.training_resource"]);
                    Route::get("/{training}", "resourceShow")->middleware(["check_permission:read.training_resource"]);
                    Route::patch("/{training}", "resourceUpdate")->middleware(["check_permission:update.training_resource"]);
                    Route::delete("/{training}", "resourceDestroy")->middleware(["check_permission:delete.training_resource"]);
                });

                // route for training assignment
                Route::controller(AssignmentTrainingController::class)
                    ->prefix("assignment")
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

        Route::controller(UserController::class)
            ->group(function () {

                // user list
                Route::prefix("users")
                    ->group(function () {
                    Route::get("/", "index")->middleware(["check_permission:read.users"]);
                });

                // user profile
                Route::prefix("profile")
                    ->group(function () {
                    Route::get("/{user}", "show")->can("read", "user");
                    Route::patch("/{user}", "update")->can("update", "user");
                });

            });

        // management (for hr)
        Route::controller(ManagementController::class)
            ->middleware(["check_role:hr", "check_permission:read.management"])
            ->prefix("management")
            ->group(function () {
                Route::get("/", "index");
                Route::get("/{user}", "show");
            });

        // hr (for admin)
        Route::prefix("hr")
            ->middleware(["check_role:admin"])
            ->group(function () {

                // main hr controller
                Route::controller(HRController::class)
                    ->group(function () {
                    Route::get("/", "index")->middleware(["check_permission:read.hr"]);
                    Route::patch("/{hr}", "update")->middleware(["check_permission:update.hr"]);
                });

                // create hr
                Route::post("/", [AuthController::class, "register"])->middleware(["check_permission:create.hr"]);

            });

        // roles (for admin)
        Route::prefix("role")
            ->group(function () {

                // resource
                Route::controller(ResourceRoleController::class)
                    ->prefix("resource")
                    ->group(function () {
                    Route::get("/", "resourceIndex")->middleware(["check_permission:read.role_resource"]);
                    Route::get("/{role}", "resourceShow")->middleware(["check_permission:read.role_resource"]);
                    Route::post("/", "resourceStore")->middleware(["check_permission:create.role_resource"]);
                    Route::patch("/{role}", "resourceUpdate")->middleware(["check_permission:update.role_resource"]);
                    Route::delete("/{role}", "resourceDestroy")->middleware(["check_permission:delete.role_resource"]);
                });

                // assignment
                Route::controller(AssignmentRoleController::class)
                    ->middleware(["check_permission:assign.role_resource"])
                    ->prefix("assignment")
                    ->group(function () {
                    Route::get("/", "assignmentIndex");
                    Route::post("/", "assignmentStore");
                });

            });

        // permission (for admin)
        Route::prefix("permission")
            ->group(function () {

                // resource
                Route::controller(ResourcePermissionController::class)
                    ->prefix("resource")
                    ->group(function () {
                    Route::get("/", "resourceIndex")->middleware(["check_permission:read.permission_resource"]);
                    Route::get("/{permission}", "resourceShow")->middleware(["check_permission:read.permission_resource"]);
                    Route::post("/", "resourceStore")->middleware(["check_permission:create.permission_resource"]);
                    Route::patch("/{permission}", "resourceUpdate")->middleware(["check_permission:update.permission_resource"]);
                    Route::delete("/{permission}", "resourceDestroy")->middleware(["check_permission:delete.permission_resource"]);
                });

                // assignment (permission to role)
                Route::controller(AssignmentPermissionController::class)
                    ->prefix("assignment")
                    ->middleware(["check_permission:assign.permission_resource"])
                    ->group(function () {
                    Route::get("/", "assignmentIndex");
                    Route::post("/", "assignmentStore");
                });

            });

    });

});
