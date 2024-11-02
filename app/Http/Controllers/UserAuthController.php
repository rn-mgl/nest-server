<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Utils\Tokens;
use Carbon\Carbon;
use Exception;

use Illuminate\Http\Request;
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
}
