<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\TrainingReview;
use App\Models\UserTraining;
use App\Models\UserTrainingReviewResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssignedTrainingController extends Controller
{
    ############
    # ASSIGNED #
    ############

    public function assignedIndex()
    {
        try {
            $user = Auth::id();

            $trainings = UserTraining::with(["training", "assignedBy"])->where("assigned_to", "=", $user)->get();

            return response()->json(["trainings" => $trainings]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function assignedShow(UserTraining $training)
    {
        try {

            $training->load([
                "training" => [
                    "contents" => ["contentFile"],
                    "reviews" => [
                        "userResponse" => function ($query) use ($training) {
                            $query->where("response_from", "=", $training->assigned_to);
                        }
                    ]
                ]
            ]);

            $training->training->contents->each(function ($content) {
                $content->content = $content->contentFile ?? $content->content;
                $content->unsetRelation("contentFile");
            });

            $training->training->reviews->each(function ($review) {
                if (!$review->userResponse) {
                    $review->makeHidden("answer");
                    $review->unsetRelation("userResponse");
                }
            });

            return response()->json(["training" => $training]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function assignedUpdate(Request $request, UserTraining $training)
    {
        try {

            $attributes = $request->validate([
                "status" => ["string", "required", "in:pending,in_progress,done"]
            ]);

            $updated = $training->update($attributes);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function reviewResponseStore(Request $request)
    {
        try {
            $attributes = $request->validate([
                "training_id" => ["required", "integer", "exists:trainings,id"],
                "reviews" => ["array", "required"],
                "reviews.*.training_review_id" => ["required", "integer", "exists:training_reviews,id"],
                "reviews.*.user_answer" => ["required", "integer", "in:1,2,3,4"],
                "assigned_training" => ["required", "integer", "exists:user_trainings,id"]
            ]);

            DB::transaction(function () use ($attributes) {

                $assignedTraining = UserTraining::find($attributes["assigned_training"]);

                if (!in_array($assignedTraining->status, ["in_progress", "done"])) {
                    $assignedTraining->update(["status" => "in_progress"]);
                }

                $user = Auth::id();
                $trainingId = $attributes['training_id'];

                $answeredReviews = collect($attributes['reviews']);

                // get test reviews for answer checking
                $trainingReviews = TrainingReview::where("training_id", "=", $trainingId)
                    ->get()
                    ->keyBy("id");

                if ($answeredReviews->count() !== $trainingReviews->count()) {
                    throw new \InvalidArgumentException("Finish answering all the reviews first before submitting.");
                }

                // for score tracking
                $userTraining = UserTraining::where([
                    "training_id" => $trainingId,
                    "assigned_to" => $user
                ])->firstOrFail();

                $score = 0;

                foreach ($answeredReviews as $review) {

                    $reviewId = $review['training_review_id'];
                    $userAnswer = $review['user_answer'];

                    if ($trainingReviews->get($reviewId)?->answer === $userAnswer) {
                        $score++;
                    }

                    UserTrainingReviewResponse::create([
                        "response_from" => $user,
                        "training_review_id" => $reviewId,
                        "answer" => $userAnswer
                    ]);
                }

                $userTraining->update(["score" => $score]);
            });

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

}
