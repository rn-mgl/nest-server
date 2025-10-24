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

class AssignedPerformanceReviewController extends Controller
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

    public function reviewResponseStore(Request $request)
    {
        try {
            $attributes = $request->validate([
                "response" => ["string", "required"],
                "survey_id" => ["required", "integer"],
                "response_id" => ["nullable", "integer"],
            ]);

            DB::transaction(function () use ($attributes) {

                $user = Auth::id();

                UserPerformanceReviewResponse::create([
                    'response' => $attributes["response"],
                    'response_from' => $user,
                    'performance_review_survey_id' => $attributes["survey_id"]
                ]);

            });

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

}
