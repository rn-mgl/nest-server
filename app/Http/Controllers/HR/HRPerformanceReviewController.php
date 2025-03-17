<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewContent;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HRPerformanceReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchRequest $searchRequest, SortRequest $sortRequest)
    {
        try {

            $searchAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();

            $attributes = array_merge($searchAttributes, $sortAttributes);

            $searchValue = $attributes["searchValue"] ?? "";
            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $sortType = $isAsc ? "ASC" : "DESC";
            $sortKey = $attributes["sortKey"];
            $searchKey = $attributes["searchKey"];

            $performances = DB::table("performance_reviews as pr")
                            ->join("users as u", function(JoinClause $join) {
                                $join->on("u.id", "=", "pr.created_by")
                                ->where("u.is_deleted", "=", false);
                            })
                            ->where("pr.is_deleted", "=", false)
                            ->whereLike($searchKey, "%$searchValue%")
                            ->orderBy("pr.$sortKey", $sortType)
                            ->select([
                                "pr.id as performance_review_id",
                                "pr.title",
                                "pr.description",
                                "pr.created_by",
                                "u.id as user_id",
                                "u.first_name",
                                "u.last_name",
                                "u.email",
                            ])
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
                "surveys.*.survey" => ["string"]
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
                    "survey" => $survey["survey"],
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
        try {
            $contents = DB::table("performance_review_contents as prc")
                                    ->where("performance_review_id", "=", $performanceReview->id)
                                    ->where("is_deleted", "=", false)
                                    ->select([
                                        "id as performance_review_content_id",
                                        "survey"
                                    ])
                                    ->get();

            $performanceReview->contents = $contents;

            return response()->json(["performance" => $performanceReview]);
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
                "surveys.*.performance_review_content_id" => ["integer", "nullable"],
                "surveyToDelete" => ["array"],
                "surveyToDelete.*" => ["integer", "nullable"]
            ]);

            $surveyToDelete = $attributes["surveyToDelete"];
            $surveys = $attributes["surveys"];
            $performanceReviewAttr = [
                "title" => $attributes["title"],
                "description" => $attributes["description"],
            ];

            // edit or update survey if performance_review_content_id is set
            foreach($surveys as $survey) {
                $id = $survey["performance_review_content_id"] ?? null;
                if ($id) {
                    $performanceReviewContent = PerformanceReviewContent::find($id);

                    if ($performanceReviewContent) {
                        $performanceReviewContent->update([
                            "survey" => $survey["survey"]
                        ]);
                    }
                } else {
                    PerformanceReviewContent::create([
                        "survey" => $survey["survey"],
                        "performance_review_id" => $performanceReview->id
                    ]);
                }
            }

            // delete surveys marked for deletion
            foreach($surveyToDelete as $toDelete) {
                $performanceReviewContent = PerformanceReviewContent::find($toDelete);

                if ($performanceReviewContent) {
                    $performanceReviewContent->update(["is_deleted" => true]);
                }
            }

            $updated = $performanceReview->update($performanceReviewAttr);

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
            $deletedPerformanceReview = $performanceReview->update(["is_deleted" => true]);

            return response()->json(["success" => $deletedPerformanceReview]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
}
