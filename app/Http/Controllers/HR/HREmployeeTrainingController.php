<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\UserTraining;
use App\Models\EmployeeTrainingReview;
use App\Models\Training;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HREmployeeTrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "training_id" => ["required", "integer", "exists:trainings,id"]
            ]);

            $trainingId = $attributes["training_id"];

            $employees = DB::table("users as u")
                ->leftJoin("user_trainings as ut", function (JoinClause $join) use ($trainingId) {
                    $join->on("u.id", "=", "ut.assigned_to")
                        ->where("ut.training_id", "=", $trainingId);
                })
                ->select([
                    "u.id as user_id",
                    "u.first_name",
                    "u.last_name",
                    "u.email",
                    "u.email_verified_at",
                    "u.created_at",
                    "ut.id as user_training_id",
                    "ut.status",
                    "ut.deadline"
                ])
                ->get();

            return response()->json(["employees" => $employees]);
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
                "user_ids" => ["array"],
                "user_ids.*" => ["integer", "exists:users,id"],
                "training_id" => ["required", "integer", "exists:trainings,id"]
            ]);

            $employeeIds = $attributes["user_ids"];
            $trainingId = $attributes["training_id"];
            $training = Training::find($trainingId);
            $deadline = $training->deadline_days ? Carbon::now()->addDays($training->deadline_days)->toDateTimeString() : null;

            $employeeTrainings = UserTraining::where("training_id", "=", $trainingId)->get();

            $alreadyAssigned = $employeeTrainings->pluck("user_id")->toArray();

            foreach ($employeeIds as $employee) {

                if (!in_array($employee, $alreadyAssigned)) {

                    $employeeTrainingAttr = [
                        "user_id" => $employee,
                        "assigned_by" => Auth::id(),
                        "training_id" => $trainingId,
                        "deadline" => $deadline
                    ];

                    $created = UserTraining::create($employeeTrainingAttr);
                }

            }

            foreach ($alreadyAssigned as $id) {

                if (!in_array($id, $employeeIds)) {
                    $deletedEmployeeTraining = UserTraining::where("user_id", "=", $id)
                        ->where("training_id", "=", $trainingId)
                        ->delete();
                }

            }

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
