<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function index()
    {
        try {

            $users = User::with(
                [
                    "image",
                    "roles"
                ]
            )->get();

            return response()->json(["users" => $users]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        try {

            return response()->json(["user" => $user->load("image")]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {

            $attributes = $request->validate([
                "first_name" => ["required", "string"],
                "last_name" => ["required", "string"],
                "image" => [
                    Rule::when(
                        $request->hasFile("image"),
                        "file"
                    ),
                    "nullable"
                ]
            ]);

            if ($request->hasFile("image")) {
                $file = $request->file("image");

                $uploaded = Storage::disk("user")->put("/profile", $file);

                $user->profilePictures()->create([
                    "disk" => "user",
                    "path" => $uploaded,
                    "original_name" => $file->getClientOriginalName(),
                    "mime_type" => $file->getMimeType(),
                    "size" => $file->getSize()
                ]);
            }

            unset($attributes['image']);

            $updated = $user->update($attributes);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
