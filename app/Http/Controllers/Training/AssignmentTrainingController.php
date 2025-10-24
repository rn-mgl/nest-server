<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\User;
use App\Models\UserTraining;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssignmentTrainingController extends Controller
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
                "training_id" => ["required", "integer", "exists:trainings,id"]
            ]);

            $users = User::with(
                [
                    "assignedTrainings" => function ($query) use ($attributes) {
                        $query->where("training_id", "=", $attributes["training_id"])
                            ->withTrashed();
                    },
                    "image"
                ]
            )->get()->each(function ($user) {
                if ($user->relationLoaded("assignedTrainings")) {
                    $user->assigned_training = $user->assignedTrainings->first();
                    $user->unsetRelation("assignedTrainings");
                }
            });

            return response()->json(["users" => $users]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function assignmentStore(Request $request)
    {
        try {
            $attributes = $request->validate([
                "user_ids" => ["array"],
                "user_ids.*" => ["integer", "exists:users,id"],
                "training_id" => ["required", "integer", "exists:trainings,id"]
            ]);

            DB::transaction(function () use ($attributes) {
                $checkedUserIds = collect($attributes["user_ids"]);
                $trainingId = $attributes["training_id"];
                $training = Training::find($trainingId);
                $deadline = $training->deadline_days ? Carbon::now()->addDays($training->deadline_days)->toDateTimeString() : null;

                $employeeTrainings = UserTraining::withTrashed()
                    ->where("training_id", "=", $trainingId)
                    ->get();

                $alreadyAssignedIds = $employeeTrainings->pluck("assigned_to");

                $newlyAssigned = $checkedUserIds->diff($alreadyAssignedIds);

                $assignData = $newlyAssigned->map(function ($user) use ($trainingId, $deadline) {
                    return [
                        "assigned_to" => $user,
                        "assigned_by" => Auth::id(),
                        "training_id" => $trainingId,
                        "deadline" => $deadline
                    ];
                });

                UserTraining::insert($assignData->all());

                // re-assign deleted records that were rechecked
                $employeeTrainings
                    ->filter(fn($training) => $training->trashed() && $checkedUserIds->contains($training->assigned_to))
                    ->each(fn($training) => $training->restore());

                $revoked = $alreadyAssignedIds->diff($checkedUserIds);

                UserTraining::where("training_id", "=", $trainingId)
                    ->whereIn("assigned_to", $revoked)
                    ->delete();
            });

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

}
