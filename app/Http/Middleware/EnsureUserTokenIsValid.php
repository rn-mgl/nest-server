<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use App\Models\User;
use Exception;
use Illuminate\Validation\UnauthorizedException;

class EnsureUserTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $authToken = $request->header("Authorization");

        if (!$authToken || !Str::startsWith($authToken, "Bearer ")) {
            throw new UnauthorizedException("You are unauthorized to proceed.");
        }

        $token = explode(" ", $authToken)[1];
        $key = env("JWT_KEY");

        try {
            $decoded = JWT::decode($token, new Key($key, "HS256"));

            $user = User::findOrFail($decoded->user);

            // check if payload match db
            if ($user->id !== $decoded->user || $user->email !== $decoded->email || $user->role !== $decoded->role) {
                throw new UnauthorizedException("You are unauthorized to proceed.");
            }

            // check if expired
            $expiration = Carbon::createFromTimestamp($decoded->exp);

            if (Carbon::now()->greaterThanOrEqualTo($expiration)) {
                throw new UnauthorizedException("Session expired. Please log in again.");
            }

            return $next($request);

        } catch (\Throwable $th) {
            throw new Exception("Something went wrong when verifying your account.");
        }

    }
}