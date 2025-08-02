<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HRLeaveRequestController extends Controller
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
                "start_date" => ["required", "string", "date"],
                "end_date" => ["required", "string", "date"],
                "reason" => ["required", "string"],
                "leave_type_id" => ["required", "exists:leave_types,id"]
            ]);

            $attributes["user_id"] = Auth::id();
            $attributes["status"] = "Pending";
            $attributes["start_date"] = Carbon::parse($attributes["start_date"])->toDateTimeString();
            $attributes["end_date"] = Carbon::parse($attributes["end_date"])->toDateTimeString();

            $created = LeaveRequest::create($attributes);

            return response()->json(["success" => $created]);

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
