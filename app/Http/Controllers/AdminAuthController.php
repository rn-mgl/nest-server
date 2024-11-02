<?php

namespace App\Http\Controllers;

use App\Events\AdminRegistered;
use App\Models\Admin;
use App\Utils\Tokens;
use Carbon\Carbon;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;

class AdminAuthController extends Controller
{
    public function verify(Request $request)
    {

        try {
            $token = $request->get("token");
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
}
