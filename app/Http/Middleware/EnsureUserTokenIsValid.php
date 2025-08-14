<?php

namespace App\Http\Middleware;

use App\Utils\Tokens;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

class EnsureUserTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        try {

            $authToken = $request->header("Authorization");

            if (!$authToken || !Str::startsWith($authToken, "Bearer ")) {
                throw new UnauthorizedException("You are unauthorized to proceed.");
            }

            $tokens = new Tokens();
            $token = explode(" ", $authToken)[1];
            $key = env("SESSION_KEY");

            $decoded = JWT::decode($token, new Key($key, "HS256"));

            $isCorrectMetadata = $tokens->verifyTokenMetadata($decoded);

            if (!$isCorrectMetadata) {
                throw new UnauthorizedException("Your token is invalid. Please log in again.");
            }

            $user = $request->user();

            // check if payload match db
            if ($user->id !== $decoded->user || $user->email !== $decoded->email || $user->roles->role !== $decoded->role) {
                throw new UnauthorizedException("You are unauthorized to proceed.");
            }

            if ($user->roles->role !== $role) {
                throw new UnauthorizedException("You are unauthorized to proceed.");
            }

            // check if expired
            $expiration = Carbon::createFromTimestamp($decoded->exp);

            if (Carbon::now()->greaterThanOrEqualTo($expiration)) {
                throw new UnauthorizedException("Session expired. Please log in again.");
            }

            return $next($request);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }
}
