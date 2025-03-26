<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTraining;
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
                        ->leftJoin("employee_trainings as et", function(JoinClause $join) use($trainingId) {
                            $join->on("u.id", "=", "et.employee_id")
                            ->where("et.training_id", "=", $trainingId);
                        })
                        ->select([
                            "u.id as user_id",
                            "u.first_name",
                            "u.last_name",
                            "u.email",
                            "u.email_verified_at",
                            "u.created_at",
                            "et.id as employee_training_id",
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
                "employee_ids" => ["required", "array"],
                "employee_ids.*" => ["integer", "exists:users,id"],
                "training_id" => ["required", "integer", "exists:trainings,id"]
            ]);

            $employeeIds = $attributes["employee_ids"];
            $trainingId = $attributes["training_id"];

            $employeeTrainings = DB::table("employee_trainings")
                                ->where("training_id", "=", $trainingId)
                                ->get();

            $alreadyAssigned = $employeeTrainings->pluck("employee_id")->toArray();

            foreach ($employeeIds as $employee) {

                if (!in_array($employee, $alreadyAssigned)) {

                    $employeeTrainingAttr = [
                        "employee_id" => $employee,
                        "assigned_by" => Auth::guard("base")->id(),
                        "training_id" => $trainingId
                    ];

                    $created = EmployeeTraining::create($employeeTrainingAttr);

                }

            }

            foreach ($alreadyAssigned as $id) {

                if (!in_array($id, $employeeIds)) {
                    $deleted = DB::table("employee_trainings")
                                ->where("employee_id", "=", $id)
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
