<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
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
                "response" => ["array", "required"],
                "response.*.performance_review_content_id" => ["required", "integer"],
                "response.*.user_performance_review_response_id" => ["nullable", "integer"],
                "response.*.response" => ["required", "string"],
            ]);

            $responseAttribute = [
                "performance_review_content_id" => null,
                "response_by" => Auth::id(),
                "response" => "",
            ];

            DB::transaction(function() use ($attributes, $responseAttribute) {

                foreach ($attributes["response"] as $response) {

                    $responseAttribute["response"] = $response["response"];
                    $responseAttribute["performance_review_content_id"] = $response["performance_review_content_id"];
                    $responseId = $response["user_performance_review_response_id"];

                    // if there is an existing response, update it, else insert
                    if ($responseId) {
                        $update = UserPerformanceReviewResponse::find($responseId)->update($responseAttribute);
                    } else {
                        $created = UserPerformanceReviewResponse::create($responseAttribute);
                    }

                }

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
