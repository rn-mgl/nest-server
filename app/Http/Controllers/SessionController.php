<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SessionController extends Controller
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
            "email" => ["required", "email"],
            "password" => ["required"]
        ]);

        if (!Auth::attempt($attributes)) {
            throw new AuthorizationException("Invalid Credentials");
        }

        $request->session()->regenerateToken();

        $id = Auth::id();

        $user = User::find($id);

        $payload = [
            "user" => $user->id,
            "name" => "{$user->first_name} {$user->last_name}",
            "email" => $user->email,
            "role" => $user->role,
            "iss" => "Nest",
            "aud" => env("APP_URL"),
            "iat" => Carbon::now()->timestamp,
            "exp" => Carbon::now()->addDay()->timestamp,
        ];

        $token = JWT::encode($payload, env("JWT_KEY"), "HS256");

        return response()->json(["success" => true, "token" => $token]);
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
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(env("APP_URL"));
    }
}
