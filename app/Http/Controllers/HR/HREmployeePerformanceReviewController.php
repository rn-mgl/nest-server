<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPerformanceReview;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HREmployeePerformanceReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
                    }
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $attributes = $request->validate([
                "user_ids" => ["required", "array"],
                "user_ids.*" => ["integer", "exists:users,id"],
                "performance_review_id" => ["required", "integer", "exists:performance_reviews,id"]
            ]);

            DB::transaction(function () use ($attributes) {
                $performanceReviewId = $attributes["performance_review_id"];
                $checkedUserIds = collect($attributes["user_ids"]);

                $performanceReviews = UserPerformanceReview::withTrashed()
                    ->where("performance_review_id", "=", $performanceReviewId)
                    ->get();

                $alreadyAssignedIds = $performanceReviews->pluck("user_id");

                $newlyAssigned = $checkedUserIds->diff($alreadyAssignedIds);

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
                    ->filter(fn($performance) => $performance->trashed() && $checkedUserIds->contains($performance->user_id))
                    ->each(fn($onboarding) => $onboarding->restore());

                // revoke unchecked ids
                $revoked = $alreadyAssignedIds->diff($checkedUserIds);

                UserPerformanceReview::where("performance_review_id", "=", $performanceReviewId)
                    ->whereIn("user_id", $revoked)
                    ->delete();

            });

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
