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

class ResourcePerformanceReviewController extends Controller
{
    ############
    # ASSIGNED #
    ############

    public function assignedIndex(Request $request)
    {
        try {
            $user = Auth::id();

            $performanceReviews = UserPerformanceReview::with(["performanceReview", "assignedBy"])
                ->where("assigned_to", "=", $user)
                ->get();

            return response()->json(["performance_reviews" => $performanceReviews]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function assignedShow(UserPerformanceReview $performanceReview)
    {
        try {

            $performanceReview->load(
                [
                    "performanceReview" => [
                        "surveys" => [
                            "userResponse" => function ($query) use ($performanceReview) {
                                $query->where("response_from", "=", $performanceReview->assigned_to);
                            }
                        ]
                    ]
                ]
            );

            return response()->json(["performance_review" => $performanceReview]);


        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    ############
    # RESOURCE #
    ############

    /**
     * Display a listing of the resource.
     */
    public function resourceIndex()
    {
        try {

            $performances = PerformanceReview::with(["createdBy"])->get();

            return response()->json(["performance_reviews" => $performances]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function resourceStore(Request $request)
    {
        try {
            $attributes = $request->validate([
                "title" => ["required", "string"],
                "description" => ["required", "string"],
                "surveys" => ["required", "array"],
                "surveys.*.survey" => ["string"]
            ]);

            $created = DB::transaction(function () use ($attributes) {
                $createdPerformance = PerformanceReview::create([
                    "title" => $attributes["title"],
                    "description" => $attributes["description"],
                    "created_by" => Auth::id()
                ]);

                $surveyData = collect($attributes["surveys"])->map(function ($survey) use ($createdPerformance) {
                    return [
                        "survey" => $survey["survey"],
                        "created_by" => Auth::id(),
                        "performance_review_id" => $createdPerformance->id
                    ];
                });

                PerformanceReviewSurvey::insert($surveyData->all());

                return $createdPerformance;
            });

            return response()->json(["success" => $created]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function resourceShow(PerformanceReview $performanceReview)
    {
        try {
            return response()->json(["performance_review" => $performanceReview->load("surveys")]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function resourceUpdate(Request $request, PerformanceReview $performanceReview)
    {

        try {
            $attributes = $request->validate([
                "title" => ["string", "required"],
                "description" => ["string", "required"],
                "surveys" => ["array", "required"],
                "surveys.*.survey" => ["string", "required"],
                "surveys.*.id" => ["integer", "nullable"],
                "surveyToDelete" => ["array"],
                "surveyToDelete.*" => ["integer", "nullable"]
            ]);

            $updated = DB::transaction(function () use ($attributes, $performanceReview) {
                $surveyToDelete = $attributes["surveyToDelete"];

                $surveyData = collect($attributes["surveys"])->map(function ($survey) use ($performanceReview) {
                    return [
                        "id" => $survey["id"] ?? null,
                        "survey" => $survey["survey"],
                        "created_by" => Auth::id(),
                        "performance_review_id" => $performanceReview->id
                    ];
                });

                PerformanceReviewSurvey::upsert($surveyData->all(), ["id"], ["survey"]);

                PerformanceReviewSurvey::whereIn("id", $surveyToDelete)->delete();

                $updated = $performanceReview->update([
                    "title" => $attributes["title"],
                    "description" => $attributes["description"],
                    "created_by" => Auth::id(),
                ]);

                return $updated;
            });

            return response()->json(["success" => $updated]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function resourceDestroy(PerformanceReview $performanceReview)
    {
        try {

            $deleted = DB::transaction(function () use ($performanceReview) {

                $deleted = $performanceReview->delete();

                $affectedContents = PerformanceReviewSurvey::where("performance_review_id", "=", $performanceReview->id)->get()->pluck("id");

                $deletedContents = PerformanceReviewSurvey::whereIn("id", $affectedContents)->delete();

                $deletedUserPerformance = UserPerformanceReview::where("performance_review_id", "=", $performanceReview->id)->delete();

                $deletedUserPerformanceResponses = UserPerformanceReviewResponse::whereIn("performance_review_survey_id", $affectedContents)->delete();

                return $deleted || $affectedContents || $deletedContents || $deletedUserPerformance || $deletedUserPerformanceResponses;

            });

            return response()->json(["success" => $deleted]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

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
