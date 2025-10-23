<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewSurvey;
use App\Models\User;
use App\Models\UserPerformanceReview;
use App\Models\UserPerformanceReviewResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssignmentPerformanceReviewController extends Controller
{

    ##############
    # ASSIGNMENT #
    ##############

    /**
     * Display a listing of the resource.
     */
    public function assignmentIndex(Request $request)
    {
        try {

            $attributes = $request->validate([
                "performance_review_id" => ["required", "integer", "exists:performance_reviews,id"]
            ]);

            $users = User::with(
                [
                    "assignedPerformanceReviews" => function ($query) use ($attributes) {
                        $query->where("performance_review_id", "=", $attributes["performance_review_id"])
                            ->withTrashed();
                    },
                    "image"
                ]
            )->get()->each(function ($user) {
                if ($user->relationLoaded("assignedPerformanceReviews")) {
                    $user->assigned_performance_review = $user->assignedPerformanceReviews->first();
                    $user->unsetRelation("assignedPerformanceReviews");
                }
            });

            return response()->json(["users" => $users]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function assignmentStore(Request $request)
    {
        try {

            $attributes = $request->validate([
                "user_ids" => ["array"],
                "user_ids.*" => ["integer", "exists:users,id"],
                "performance_review_id" => ["required", "integer", "exists:performance_reviews,id"]
            ]);

            DB::transaction(function () use ($attributes) {
                $performanceReviewId = $attributes["performance_review_id"];
                $checkedUserIds = collect($attributes["user_ids"]);

                $performanceReviews = UserPerformanceReview::withTrashed()
                    ->where("performance_review_id", "=", $performanceReviewId)
                    ->get();

                $alreadyAssignedIds = $performanceReviews->pluck("assigned_to");
                $newlyAssigned = $checkedUserIds->diff($alreadyAssignedIds);
                // revoke unchecked ids
                $revoked = $alreadyAssignedIds->diff($checkedUserIds);

                $assignData = $newlyAssigned->map(function ($user) use ($performanceReviewId) {
                    return [
                        "performance_review_id" => $performanceReviewId,
                        "assigned_to" => $user,
                        "assigned_by" => Auth::id()
                    ];
                });

                UserPerformanceReview::insert($assignData->all());

                // re-assign the previously deleted records but were rechecked
                $performanceReviews
                    ->filter(fn($performance) => $performance->trashed() && $checkedUserIds->contains($performance->assigned_to))
                    ->each(fn($onboarding) => $onboarding->restore());

                UserPerformanceReview::where("performance_review_id", "=", $performanceReviewId)
                    ->whereIn("assigned_to", $revoked)
                    ->delete();

            });

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

}
