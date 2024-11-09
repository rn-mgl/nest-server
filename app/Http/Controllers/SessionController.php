<?php

namespace App\Http\Controllers;

use App\Events\Registered;
use App\Models\User;
use App\Utils\Tokens;
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
        return redirect(env("NEST_URL") . "/auth/login");
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
                "email" => ["required", "email"],
                "password" => ["required"]
            ]);

            if (!Auth::guard("base")->attempt($attributes)) {
                throw new AuthorizationException("Invalid Credentials");
            }

            $request->session()->regenerateToken();

            $id = Auth::guard("base")->id();

            $user = User::find($id);

            $isVerified = $user->email_verified_at;

            if (!$isVerified) {
                $tokens = new Tokens();
                $token = $tokens->createVerificationToken($user->id, "{$user->first_name} {$user->last_name}", $user->email, $user->role);
                event(new Registered($user, $token));
                return response()->json(["success" => true, "token" => null, "role" => $user->role, "isVerified" => false]);
            }

            $payload = [
                "user" => $user->id,
                "name" => "{$user->first_name} {$user->last_name}",
                "email" => $user->email,
                "role" => $user->role,
                "iss" => env("TOKEN_ISSUER"),
                "aud" => env("TOKEN_AUDIENNCE"),
                "iat" => Carbon::now()->timestamp,
                "exp" => Carbon::now()->addDay()->timestamp,
            ];

            $token = JWT::encode($payload, env("SESSION_KEY"), "HS256");

            return response()->json(["success" => true, "token" => $token, "role" => $user->role, "isVerified" => $isVerified]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
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
    public function destroy(Request $request)
    {

        try {
            Auth::guard("base")->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }

    }
}
