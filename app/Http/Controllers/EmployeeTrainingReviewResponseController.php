<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTrainingReviewResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeTrainingReviewResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
                "training_id" => ["required", "integer", "exists:trainings,id"],
                "reviews" => ["array", "required"],
                "reviews.*.training_review_id" => ["required", "integer", "exists:training_reviews,id"],
                "reviews.*.answer" => ["required", "integer", "in:1,2,3,4"],
            ]);

            $user = Auth::guard("base")->id();

            // check if there are records that are already answered and stored
            $alreadyAnsweredReviews = DB::table("employee_training_review_responses")
                                        ->where("response_by", "=", $user)
                                        ->pluck("training_review_id")
                                        ->toArray();

            // get test reviews and map as [id => answer]
            $reviews = DB::table("training_reviews")
                        ->where("training_id", "=", $attributes["training_id"])
                        ->where("is_deleted", "=", false)
                        ->get()
                        ->mapWithKeys(function($item) {
                            return [$item->id => $item->answer];
                        });

            $score = 0;
            $shouldUpdateScore = false;

            foreach ($attributes["reviews"] as $review) {

                // if current review is already stored, skip it
                if (in_array($review["training_review_id"], $alreadyAnsweredReviews)) {
                    continue;
                }

                // check if training review's answer is similar to the employee's answer, add 1 to score if yes, retain if no
                $score = $reviews[$review['training_review_id']] === $review['answer'] ? $score + 1 : $score;

                $trainingReviewResponseAttr = [
                    "response_by" => $user,
                    "training_review_id" => $review["training_review_id"],
                    "answer" => $review["answer"]
                ];

                $created = EmployeeTrainingReviewResponse::create($trainingReviewResponseAttr);

                $shouldUpdateScore = true;
            }

            // update score if there is a stored response
            if ($shouldUpdateScore) {

                $updateScore = DB::table("employee_trainings as et")
                                ->where("employee_id", "=", $user)
                                ->where("training_id", "=", $attributes["training_id"])
                                ->update(["score" => $score]);

            }

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeTrainingReviewResponse $employeeTrainingReviewResponse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeTrainingReviewResponse $employeeTrainingReviewResponse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeTrainingReviewResponse $employeeTrainingReviewResponse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeTrainingReviewResponse $employeeTrainingReviewResponse)
    {
        //
    }
}
