<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AdminSessionController extends Controller
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
        $attributes = $request->validate([
            "email" => ["required", "string", "email"],
            "password" => ["required"],
        ]);

        if (!Auth::guard('admin')->attempt($attributes)) {
            throw new AuthorizationException("Invalid Credentials");
        }

        $request->session()->regenerateToken();

        $id = Auth::guard("admin")->id();

        $admin = Admin::find($id);

        $isVerified = $admin->email_verified_at;

        $payload = [
            "admin" => $admin->id,
            "name" => "{$admin->first_name} {$admin->last_name}",
            "email" => $admin->email,
            "role" => "admin",
            "iss" => "Nest",
            "aud" => env("APP_URL"),
            "iat" => Carbon::now()->timestamp,
            "exp" => Carbon::now()->addDay()->timestamp,
        ];

        $token = JWT::encode($payload, env("ADMIN_SESSION_KEY"), "HS256");

        return response()->json(["success" => true, "token" => $token, "isVerified" => $isVerified, "role" => "admin"]);
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
    public function destroy(Request $request)
    {
        try {
            Auth::guard("admin")->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
}
