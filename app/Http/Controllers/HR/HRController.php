<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

class HRController extends Controller
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
    public function show(User $hr)
    {
        try {

            return response()->json(["profile" => $hr]);

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
    public function update(Request $request, User $hr)
    {
        try {

            $attributes = $request->validate([
                "first_name" => ["required", "string"],
                "last_name" => ["required", "string"],
                "image" => ["nullable"]
            ]);

            if ($request->hasFile("image")) {
                $uploaded = cloudinary()->uploadFile($request->file("image")->getRealPath(), ["folder" => "nest-uploads"])->getSecurePath();
                $attributes["image"] = $uploaded;
            }

            $updated = $hr->update($attributes);

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
