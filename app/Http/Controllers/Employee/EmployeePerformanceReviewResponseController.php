<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\UserPerformanceReview;
use App\Models\UserPerformanceReviewResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeePerformanceReviewResponseController extends Controller
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
                "response" => ["string", "required"],
                "survey_id" => ["required", "integer"],
                "response_id" => ["nullable", "integer"],
            ]);

            DB::transaction(function () use ($attributes) {

                $user = Auth::id();

                UserPerformanceReviewResponse::create([
                    'response' => $attributes["response"],
                    'response_from' => $user,
                    'performance_review_survey_id' => $attributes["survey_id"]
                ]);

            });

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserPerformanceReviewResponse $UserPerformanceReviewResponse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserPerformanceReviewResponse $UserPerformanceReviewResponse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserPerformanceReviewResponse $UserPerformanceReviewResponse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserPerformanceReviewResponse $UserPerformanceReviewResponse)
    {
        //
    }
}
