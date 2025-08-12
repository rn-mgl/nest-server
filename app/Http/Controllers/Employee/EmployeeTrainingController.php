<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\EmployeeTraining;
use App\Models\TrainingContent;
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
    public function index(SearchRequest $searchRequest, SortRequest $sortRequest, CategoryRequest $categoryRequest)
    {
        try {

            $searchAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();
            $categoryAttributes = $categoryRequest->validated();

            $attributes = array_merge($searchAttributes, $sortAttributes, $categoryAttributes);

            $searchKey = $attributes["searchKey"];
            $searchValue = $attributes["searchValue"] ?? "";
            $sortKey = $attributes["sortKey"];
            $isAsc = filter_var($attributes["sortKey"], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $sortType = $isAsc ? "ASC" : "DESC";
            $categoryKey = $attributes["categoryKey"];
            $categoryValue = $attributes["categoryValue"] ==="all" ? "" : $attributes["categoryValue"];

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
                        ->where("et.is_deleted", "=", false)
                        ->where("et.employee_id", "=", $user)
                        ->where("{$searchKey}", "LIKE", "%{$searchValue}%")
                        ->where("{$categoryKey}", "LIKE", "%{$categoryValue}%")
                        ->select([
                            'et.id as employee_training_id',
                            'et.status',
                            'et.deadline',
                            't.id as training_id',
                            't.title',
                            't.description',
                            't.deadline_days',
                            't.created_by',
                            DB::raw("CASE WHEN et.status != 'done' THEN NULL ELSE t.certificate END as certificate"),
                            'u.id as user_id',
                            'u.first_name',
                            'u.last_name',
                            'u.email',
                            'u.email_verified_at',
                            'u.created_at',
                        ])
                        ->orderBy("{$sortKey}", $sortType)
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
                            DB::raw("CASE WHEN et.status != 'done' THEN NULL ELSE t.certificate END as certificate"),
                            "et.score",
                            "et.id as employee_training_id",
                            "et.status",
                            "et.deadline",
                        ])
                        ->first();

            $training->contents = TrainingContent::where("is_deleted", "=", false)
                                ->select([
                                    "id as training_content_id",
                                    "title",
                                    "description",
                                    "content",
                                    "type"
                                ])
                                ->get();

            $training->reviews = DB::table("training_reviews as tr")
                                ->leftJoin("employee_training_review_responses as etrr", function(JoinClause $join) {
                                    $join->on("tr.id", "=", "etrr.training_review_id")
                                    ->where("etrr.is_deleted", "=", false);
                                })
                                ->where("tr.training_id", "=", $training->training_id)
                                ->where("tr.is_deleted", "=", false)
                                ->select([
                                    "etrr.id as employee_training_review_response_id",
                                    "tr.id as training_review_id",
                                    "etrr.answer as employee_answer",
                                    DB::raw("CASE WHEN etrr.answer = tr.answer THEN true ELSE false END as is_correct"),
                                    "tr.question",
                                    "tr.choice_1",
                                    "tr.choice_2",
                                    "tr.choice_3",
                                    "tr.choice_4",
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
