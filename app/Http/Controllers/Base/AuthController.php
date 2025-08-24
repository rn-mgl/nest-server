<?php

namespace App\Http\Controllers\Base;

use App\Events\Registered;
use App\Http\Controllers\Controller;
use App\Mail\PasswordResetLink;
use App\Models\Role;
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

class AuthController extends Controller
{

    public function register(Request $request) {
        try {
            $attributes = $request->validate([
                "first_name" => ["required", "string"],
                "last_name" => ["required", "string"],
                "email" => ["required", "string", "email", "unique:users,email"],
                "password" => ["required", Password::min(8)],
                "role" => ["required", "string", "in:employee,hr"]
            ]);

            $role = Role::where("role", "=", $attributes["role"])->firstOrFail();

            $attributes["role_id"] = $role->id;

            unset($attributes["role"]);

            $user = User::create($attributes);
            $tokens = new Tokens("VERIFICATION");
            $token = $tokens->createToken($user->id, "{$user->first_name} {$user->last_name}", $user->email, $user->roles->role);

            Auth::login($user);

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

            $tokens = new Tokens("VERIFICATION");
            $decoded = $tokens->decodeToken($token);
            $correctMetadata = $tokens->verifyMetadata($decoded);

            if (!$correctMetadata) {
                throw new UnauthorizedException("The token you used is invalid");
            }

            $user = User::findOrFail($decoded->user);

            // check if payload match db
            if ($user->id !== $decoded->user || $user->email !== $decoded->email || $user->roles->role !== $decoded->role) {
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

            if (!Auth::attempt($attributes)) {
                throw new AuthorizationException("Invalid Credentials");
            }

            $request->session()->regenerateToken();

            $id = Auth::id();

            $user = User::findOrFail($id);

            $isVerified = $user->email_verified_at;

            if (!$isVerified) {
                $tokens = new Tokens("VERIFICATION");
                $token = $tokens->createToken($user->id, "{$user->first_name} {$user->last_name}", $user->email, $user->roles->role);
                event(new Registered($user, $token));
                return response()->json(["success" => true, "token" => null, "role" => $user->roles->role, "isVerified" => false]);
            }

            $tokens = new Tokens("SESSION");
            $token = $tokens->createToken($user->id, "{$user->first_name} {$user->last_name}", $user->email, $user->roles->role);

            return response()->json(["success" => true, "token" => $token, "current" => $user->id, "role" => $user->roles->role, "isVerified" => $isVerified]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function resend_verification()
    {
        try {
            $id = Auth::id();
            $user = User::findOrFail($id);

            $tokens = new Tokens("VERIFICATION");
            $token = $tokens->createToken($user->id, "{$user->first_name} {$user->last_name}", $user->email, $user->roles->role);

            event(new Registered($user, $token));

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function logout(Request $request)
    {

        try {
            Auth::logout();

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

            $authenticated = Auth::user();

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

            $tokens = new Tokens("RESET");
            $token = $tokens->createToken($user->id, "{$user->first_name} {$user->last_name}", $user->email, $user->roles->role);

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

            $tokens = new Tokens("RESET");
            $token = $tokens->decodeToken($attributes["reset_token"]);

            if (!$tokens->verifyMetadata($token)) {
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
