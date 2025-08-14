<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

class AdminController extends Controller
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
    public function show(Admin $admin)
    {
        try {

            $authenticated = Auth::user();

            if ($authenticated->id !== $admin->id) {
                throw new UnauthorizedException("The client session you use does not match our server.");
            }

            return response()->json(["profile" => $admin]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin $admin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Admin $admin)
    {
        try {

            $attributes = $request->validate([
                "image" => ["nullable"],
                "first_name" => ["string", "required"],
                "last_name" => ["string", "required"]
            ]);

            if ($request->hasFile("image")) {
                $uploadedFile = cloudinary()->uploadFile($request->file("image")->getRealPath(), ["folder" => "nest-uploads"])->getSecurePath();
                $attributes["image"] = $uploadedFile;
            }

            $updated = $admin->update($attributes);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        //
    }
}
