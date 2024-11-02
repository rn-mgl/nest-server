<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Utils\Tokens;
use Carbon\Carbon;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class EnsureAdminTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        try {
            $authToken = $request->header("Authorization");

            if (!$authToken || !Str::startsWith($authToken, "Bearer ")) {
                throw new UnauthorizedException("You are unauthorized to proceed.");
            }

            $tokens = new Tokens(true);
            $token = explode(" ", $authToken)[1];
            $key = env("ADMIN_SESSION_KEY");

            $decoded = JWT::decode($token, new Key($key, "HS256"));

            $isCorrectMetadata = $tokens->verifyTokenMetadata($decoded);

            if (!$isCorrectMetadata) {
                throw new UnauthorizedException("You are unauthorized to proceed.");
            }

            $admin = Admin::findOrFail($decoded->admin);

            $adminName = "{$admin->first_name} {$admin->last_name}";

            // check if payload match db
            if ($admin->id !== $decoded->admin || $admin->email !== $decoded->email || $adminName !== $decoded->name) {
                throw new UnauthorizedException("You are unauthorized to proceed.");
            }

            // check if expired
            $expiration = Carbon::createFromTimestamp($decoded->exp);

            if (Carbon::now()->greaterThanOrEqualTo($expiration)) {
                throw new UnauthorizedException("Session expired. Please log in again.");
            }

            return $next($request);

        } catch (\Throwable $th) {
            throw new Exception("You are unauthorized to proceed.");
        }

    }
}
