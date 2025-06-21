<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminRegistered;
use App\Http\Controllers\Controller;
use App\Mail\AdminPasswordResetLink;
use App\Models\Admin;
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

class AdminAuthController extends Controller
{

    public function verify(Request $request)
    {

        try {
            $attributes = $request->validate([
                "token" => ["required", "string"]
            ]);
            $token = $attributes["token"];
            $tokens = new Tokens(true);
            $decoded = $tokens->decodeVerificationToken($token);
            $correctMetadata = $tokens->verifyTokenMetadata($decoded);

            if (!$correctMetadata) {
                throw new UnauthorizedException("The token you used is invalid");
            }

            $admin = Admin::findOrFail($decoded->admin);
            $adminName = "{$admin->first_name} {$admin->last_name}";

            // check if payload match db
            if ($admin->id !== $decoded->admin || $admin->email !== $decoded->email || $adminName !== $decoded->name) {
                throw new UnauthorizedException("You are unauthorized to proceed.");
            }

            $verify = DB::table("admins")
                        ->where("id", "=", $admin->id)
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
                "iss" => env("TOKEN_ISSUER"),
                "aud" => env("TOKEN_AUDIENCE"),
                "iat" => Carbon::now()->timestamp,
                "exp" => Carbon::now()->addDay()->timestamp,
            ];

            $token = JWT::encode($payload, env("ADMIN_SESSION_KEY"), "HS256");

            return response()->json(["success" => true, "token" => $token, "current" => $admin->id, "isVerified" => $isVerified, "role" => "admin"]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function resend_verification()
    {
        try {
            $id = Auth::guard("admin")->id();
            $admin = Admin::findOrFail($id);

            $tokens = new Tokens(true);
            $token = $tokens->createVerificationToken($admin->id, "{$admin->first_name} {$admin->last_name}", $admin->email, "admin");

            event(new AdminRegistered($admin, $token));

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::guard("admin")->logout();

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
                "new_password" => ["required", "confirmed", "string", Password::min(8)]
            ]);

            $user = Auth::guard("admin")->user();

            if (!Hash::check($attributes["current_password"], $user->password)) {
                throw new Exception("Entered Current Password did not match your record.");
            }

            $changed = Admin::find($user->id)->update(["password" => $attributes["new_password"]]);

            if ($changed) {
                Auth::guard("admin")->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return  response()->json(["success" => $changed]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function forgot_password(Request $request) {
        try {
            $attributes = $request->validate([
                "email" => ["required", "string", "email"]
            ]);

            $admin = Admin::where("email", "=", $attributes["email"])->firstOrFail();

            $payload = [
                "admin" => $admin->id,
                "name" => "{$admin->first_name} {$admin->last_name}",
                "email" => $admin->email,
                "role" => "admin",
                "iss" => env("TOKEN_ISSUER"),
                "aud" => env("TOKEN_AUDIENCE"),
                "iat" => Carbon::now()->timestamp,
                "exp" => Carbon::now()->addMinutes(30)->timestamp,
            ];

            $token = JWT::encode($payload, env("ADMIN_RESET_KEY"), "HS256");

            Mail::to($admin->email, "{$admin->first_name} {$admin->last_name}")->queue(new AdminPasswordResetLink($token));

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return response()->json(["success" => true]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function reset_password(Request $request){

        try {

            $attributes = $request->validate([
                "new_password" => ["required", "string", "confirmed", Password::min(8)],
                "reset_token" => ["required", "string"]
            ]);

            $resetToken = $attributes["reset_token"];

            $decoded = JWT::decode($resetToken, new Key(env("ADMIN_RESET_KEY"), "HS256"));

            $tokens = new Tokens(true);

            $correctMetadata = $tokens->verifyTokenMetadata($decoded);

            if (!$correctMetadata) {
                throw new UnauthorizedException("The token you used is not valid.");
            }

            $reset = Admin::findOrFail($decoded->admin)->update(["password" => $attributes["new_password"]]);

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return response()->json(["success" => $reset]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }
}
