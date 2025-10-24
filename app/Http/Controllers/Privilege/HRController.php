<?php

namespace App\Http\Controllers\Privilege;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class HRController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {

            $hrs = User::ofRole(["hr"])->with(["image"])->get();

            return response()->json(["hrs" => $hrs]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $hr)
    {
        try {

            $attributes = $request->validate([
                "toggle" => ["required", "boolean"]
            ]);

            $verification = $attributes["toggle"] ? Carbon::now() : null;

            $updated = $hr->update(["email_verified_at" => $verification]);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }
}
