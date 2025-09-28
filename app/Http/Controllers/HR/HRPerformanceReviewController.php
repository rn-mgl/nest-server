<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewSurvey;
use App\Models\UserPerformanceReview;
use App\Models\UserPerformanceReviewResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HRPerformanceReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $performances = PerformanceReview::with(["createdBy"])->get();

            return response()->json(["performances" => $performances]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
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
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PerformanceReview $performanceReview)
    {
        try {
            return response()->json(["performance" => $performanceReview->load("surveys")]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PerformanceReview $performanceReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PerformanceReview $performanceReview)
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
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PerformanceReview $performanceReview)
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
            throw new \Exception($th->getMessage());
        }
    }
}
