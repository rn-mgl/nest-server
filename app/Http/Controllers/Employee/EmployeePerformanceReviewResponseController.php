<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeePerformanceReviewResponse;
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
                "response.*.employee_performance_review_response_id" => ["nullable", "integer"],
                "response.*.response" => ["required", "string"],
            ]);

            $user = Auth::id();

            $responseAttribute = [
                "performance_review_content_id" => null,
                "response_by" => $user,
                "response" => "",
            ];

            foreach ($attributes["response"] as $response) {

                $responseAttribute["response"] = $response["response"];
                $responseAttribute["performance_review_content_id"] = $response["performance_review_content_id"];

                // if employee_performance_review_response_id is not null, update the response, else insert
                if ($response["employee_performance_review_response_id"] === null) {
                    $created = EmployeePerformanceReviewResponse::create($responseAttribute);
                } else {
                    $update = EmployeePerformanceReviewResponse::find($response["employee_performance_review_response_id"])->update($responseAttribute);
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
    public function show(EmployeePerformanceReviewResponse $EmployeePerformanceReviewResponse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeePerformanceReviewResponse $EmployeePerformanceReviewResponse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeePerformanceReviewResponse $EmployeePerformanceReviewResponse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeePerformanceReviewResponse $EmployeePerformanceReviewResponse)
    {
        //
    }
}
