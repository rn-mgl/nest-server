<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\UserTraining;
use App\Models\UserTrainingReviewResponse;
use App\Models\TrainingReview;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                "reviews.*.user_answer" => ["required", "integer", "in:1,2,3,4"],
            ]);

            $user = Auth::id();
            $trainingId = $attributes['training_id'];

            $answeredReviews = collect($attributes['reviews'])->pluck('training_review_id');

            // cross check already answered reviews and newly answered
            $alreadyAnswered = UserTrainingReviewResponse::where("response_from", "=", $user)
                ->whereIn("training_review_id", $answeredReviews)
                ->pluck("training_review_id");

            // get the responses that are not recorded yet
            $pendingReviews = collect($attributes['reviews'])
                ->whereNotIn('training_review_id', $alreadyAnswered);

            // get test reviews for answer checking
            $trainingReviews = TrainingReview::where("training_id", "=", $trainingId)
                ->get()
                ->keyBy("id");

            // for score tracking
            $userTraining = UserTraining::where([
                "training_id" => $trainingId,
                "assigned_to" => $user
            ])->firstOrFail();

            $newScore = $userTraining->score ?? 0;

            foreach ($pendingReviews as $review) {

                $reviewId = $review['training_review_id'];
                $userAnswer = $review['user_answer'];

                if ($trainingReviews->get($reviewId)?->answer === $userAnswer) {
                    $newScore++;
                }

                UserTrainingReviewResponse::create([
                    "response_from" => $user,
                    "training_review_id" => $reviewId,
                    "answer" => $userAnswer
                ]);
            }

            // update score if there is a change
            if ($newScore !== $userTraining?->score) {
                $userTraining->update(["score" => $newScore]);
            }

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserTrainingReviewResponse $employeeTrainingReviewResponse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserTrainingReviewResponse $employeeTrainingReviewResponse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserTrainingReviewResponse $employeeTrainingReviewResponse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserTrainingReviewResponse $employeeTrainingReviewResponse)
    {
        //
    }
}
