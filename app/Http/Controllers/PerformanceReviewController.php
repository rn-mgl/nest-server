<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Models\PerformanceReviewContent;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $performances = DB::table("performance_reviews as pr")
                            ->join("users as u", function(JoinClause $join) {
                                $join->on("u.id", "=", "pr.created_by")
                                ->where("u.is_deleted", "=", false);
                            })
                            ->get();

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
                "surveys.*" => ["string"]
            ]);

            $performanceAttr = [
                "title" => $attributes["title"],
                "description" => $attributes["description"],
                "created_by" => Auth::guard("base")->id()
            ];

            $createdPerformance = PerformanceReview::create($performanceAttr);
            $surveys = $attributes["surveys"];

            $createdPerformanceReviews = 0;

            foreach($surveys as $survey) {
                $performanceReviewAttr = [
                    "survey" => $survey,
                    "performance_review_id" => $createdPerformance->id
                ];
                PerformanceReviewContent::create($performanceReviewAttr);
                $createdPerformanceReviews++;
            }

            return response()->json(["success" => $createdPerformance, "contents" => $createdPerformanceReviews]);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PerformanceReview $performanceReview)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PerformanceReview $performanceReview)
    {
        //
    }
}
