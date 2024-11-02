<?php

namespace App\Http\Controllers;

use App\Events\Registered;
use App\Models\User;
use App\Utils\Tokens;
use Carbon\Carbon;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;

class UserAuthController extends Controller
{
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

            $verify = DB::table("users")
                        ->where("id", "=", $user->id)
                        ->update(["email_verified_at" => Carbon::now()]);

            return response()->json(["success" => $verify > 0]);
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
}
