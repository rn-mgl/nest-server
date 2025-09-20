<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\UnauthorizedException;

class EmployeeController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $employee)
    {
        try {

            return response()->json(["profile" => $employee->load("image")]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
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
    public function update(Request $request, User $employee)
    {
        try {

            $attributes = $request->validate([
                "first_name" => ["required", "string"],
                "last_name" => ["required", "string"],
                "image" => ["nullable", "file"]
            ]);


            if ($request->hasFile("image")) {
                $file = $request->file("image");

                $uploaded = Storage::disk("user")->put("/profile", $file);

                $employee->profilePictures()->create([
                    "disk" => "user",
                    "path" => $uploaded,
                    "original_name" => $file->getClientOriginalName(),
                    "mime_type" => $file->getMimeType(),
                    "size" => $file->getSize(),
                ]);
            }

            unset($attributes["image"]);

            $updated = $employee->update($attributes);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
