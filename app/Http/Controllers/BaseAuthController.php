<?php

namespace App\Http\Controllers;

use App\Events\Registered;
use App\Mail\PasswordResetLink;
use App\Models\User;
use App\Utils\Tokens;
use Carbon\Carbon;
use Exception;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\UnauthorizedException;

class BaseAuthController extends Controller
{

    public function register(Request $request) {
        try {
            $attributes = $request->validate([
                "first_name" => ["required", "string"],
                "last_name" => ["required", "string"],
                "email" => ["required", "string", "email", "unique:users,email"],
                "password" => ["required", Password::min(8)],
                "role" => ["required", "string"]
            ]);

            $user = User::create($attributes);
            $tokens = new Tokens();
            $token = $tokens->createVerificationToken($user->id, "{$user->first_name} {$user->last_name}", $user->email, $user->role);

            Auth::guard("base")->login($user);

            event(new Registered($user, $token));

            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function verify(Request $request)
    {

        try {
            $token = $request->get("token");

            $tokens = new Tokens();
            $decoded = $tokens->decodeVerificationToken($token);
            $correctMetadata = $tokens->verifyTokenMetadata($decoded);

            if (!$correctMetadata) {
                throw new UnauthorizedException("The token you used is invalid");
            }

            $user = User::findOrFail($decoded->user);

            // check if payload match db
            if ($user->id !== $decoded->user || $user->email !== $decoded->email || $user->role !== $decoded->role) {
                throw new UnauthorizedException("You are unauthorized to proceed.");
            }

            $verify = User::where("id", "=", $user->id)
                        ->update(["email_verified_at" => Carbon::now()]);

            return response()->json(["success" => $verify > 0]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }

    public function login(Request $request)
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

            $user = User::findOrFail($id);

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
                "aud" => env("TOKEN_AUDIENCE"),
                "iat" => Carbon::now()->timestamp,
                "exp" => Carbon::now()->addDay()->timestamp,
            ];

            $token = JWT::encode($payload, env("SESSION_KEY"), "HS256");

            return response()->json(["success" => true, "token" => $token, "current" => $user->id, "role" => $user->role, "isVerified" => $isVerified]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function resend_verification()
    {
        try {
            $id = Auth::guard("base")->id();
            $user = User::findOrFail($id);

            $tokens = new Tokens();
            $token = $tokens->createVerificationToken($user->id, "{$user->first_name} {$user->last_name}", $user->email, $user->role);

            event(new Registered($user, $token));

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function logout(Request $request)
    {

        try {
            Auth::guard("base")->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }

    public function change_password(Request $request)
    {
        try {

            $attributes = $request->validate([
                "current_password" => ["required", "string"],
                "new_password" => ["required", "string", "confirmed", Password::min(8)],
            ]);

            $authenticated = Auth::guard("base")->user();

            if (!Hash::check($attributes["current_password"], $authenticated->password)) {
                throw new UnauthorizedException("The current password you entered does not match our record.");
            }

            $user = User::findOrFail($authenticated->id);

            $updated = $user->update(["password" => $attributes["new_password"]]);

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function forgot_password(Request $request)
    {
        try {

            $attributes = $request->validate([
                "email" => ["required", "string", "email"]
            ]);

            $user = User::where("email", "=", $attributes["email"])->firstOrFail();

            $payload = [
                "user" => $user->id,
                "name" => "{$user->first_name} {$user->last_name}",
                "email" => $user->email,
                "role" => $user->role,
                "iss" => env("TOKEN_ISSUER"),
                "aud" => env("TOKEN_AUDIENCE"),
                "iat" => Carbon::now()->timestamp,
                "exp" => Carbon::now()->addMinutes(30)->timestamp,
            ];

            $token = JWT::encode($payload, env("RESET_KEY"), "HS256");

            Mail::to($user->email, "{$user->first_name} {$user->last_name}")->queue(new PasswordResetLink($token));

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function reset_password(Request $request)
    {
        try {

            $attributes = $request->validate([
                "new_password" => ["required", "string", "confirmed", Password::min(8)],
                "reset_token" => ["required", "string"]
            ]);

            $token = JWT::decode($attributes["reset_token"], new Key(env("RESET_KEY"), "HS256"));

            $tokens = new Tokens();

            if (!$tokens->verifyTokenMetadata($token)) {
                throw new UnauthorizedException("The token you used is invalid.");
            }

            $user = User::findOrFail($token->user);

            $updated = $user->update(["password" => $attributes["new_password"]]);

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
