<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTraining;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeTrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $user = Auth::guard("base")->id();

            $trainings = DB::table("employee_trainings as et")
                        ->join("users as u", function(JoinClause $join) {
                            $join->on("et.assigned_by", "=", "u.id")
                            ->where("u.is_deleted", "=", false);
                        })
                        ->join("trainings as t", function(JoinClause $join) {
                            $join->on("et.training_id", "=", "t.id")
                            ->where("t.is_deleted", "=", false);
                        })
                        ->where("et.employee_id", "=", $user)
                        ->select([
                            'et.id as employee_training_id',
                            'et.status',
                            'et.deadline',
                            't.id as training_id',
                            't.title',
                            't.description',
                            't.deadline_days',
                            DB::raw("CASE WHEN et.status != 'Done' THEN NULL ELSE t.certificate END as certificate"),
                            'u.id as user_id',
                            'u.first_name',
                            'u.last_name',
                            'u.email',
                            'u.email_verified_at',
                            'u.created_at',
                        ])
                        ->get();

        return response()->json(["trainings" => $trainings]);

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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($employeeTraining)
    {
        try {
            $training = DB::table("employee_trainings as et")
                        ->join("trainings as t", function(JoinClause $join) {
                            $join->on("et.training_id", "=", "t.id")
                            ->where("t.is_deleted", "=", false);
                        })
                        ->where("et.id", "=", $employeeTraining)
                        ->select([
                            "t.id as training_id",
                            "t.title",
                            "t.description",
                            "t.deadline_days",
                            DB::raw("CASE WHEN et.status != 'Done' THEN NULL ELSE t.certificate END as certificate"),
                            "et.id as employee_training_id",
                            "et.status",
                            "et.deadline",
                        ])
                        ->first();

            $training->contents = DB::table("training_contents as t")
                                ->where("training_id", "=", $training->training_id)
                                ->where("is_deleted", "=", false)
                                ->select([
                                    "id as training_content_id",
                                    "title",
                                    "description",
                                    "content",
                                    "type"
                                ])
                                ->get();

            return response()->json(["training" => $training]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeTraining $employeeTraining)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeTraining $employeeTraining)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeTraining $employeeTraining)
    {
        //
    }
}
