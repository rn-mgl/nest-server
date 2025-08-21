<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\UserOnboarding;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HREmployeeOnboardingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "onboarding_id" => ["required", "integer"]
            ]);

            $employees = DB::table("users as u")
                        ->leftJoin("employee_onboardings as eo", function(JoinClause $join) use($attributes) {
                            $join->on("u.id", "=", "eo.user_id")
                            ->where("u.is_deleted", "=", false)
                            ->where("onboarding_id", "=", $attributes["onboarding_id"]);
                        })
                        ->ofRole("employee")
                        ->select([
                            "u.id as user_id",
                            "u.first_name",
                            "u.last_name",
                            "u.email",
                            "u.email_verified_at",
                            "u.created_at",
                            "eo.id as employee_onboarding_id",
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
                "onboarding_id" => ["required", "integer", "exists:onboardings,id"]
            ]);

            $employeeOnboardingAttr = [
                "assigned_by" => Auth::id(),
                "onboarding_id" => $attributes["onboarding_id"]
            ];

            $alreadyAssigned = UserOnboarding::where("onboarding_id", "=", $attributes["onboarding_id"])
                        ->get()
                        ->pluck("user_id")
                        ->toArray();

            // assign to employees
            foreach($attributes["user_ids"] as $id) {
                if (!in_array($id, $alreadyAssigned)) {
                    $employeeOnboardingAttr["user_id"] = $id;
                    $created = UserOnboarding::create($employeeOnboardingAttr);
                }
            }

            // remove unassigned
            foreach($alreadyAssigned as $id) {
                if (!in_array($id, $attributes["user_ids"])) {
                    $employeeOnboarding = UserOnboarding::where("user_id", "=", $id)
                                            ->where("onboarding_id", "=", $attributes["onboarding_id"])
                                            ->first();

                    if (!empty($employeeOnboarding)) {
                        $employeeOnboarding->delete();
                    }
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
