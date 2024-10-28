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



class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $authToken = $request->header("Authorization");

        logger($authToken);

        if (!$authToken || !Str::startsWith($authToken, "Bearer ")) {
            return response()->json(["message" => "Please login first."]);
        }

        $token = explode(" ", $authToken)[1];
        $key = env("JWT_KEY");

        try {
            $decoded = JWT::decode($token, new Key($key, "HS256"));

            $user = User::findOrFail($decoded->user);

            // check if payload match db
            if ($user->id !== $decoded->user || $user->email !== $decoded->email || $user->role !== $decoded->role) {
                return response()->json(["message" => "Stop using modified tokens."]);
            }

            // check if expired
            $expiration = Carbon::createFromTimestamp($decoded->exp);

            if (Carbon::now()->greaterThanOrEqualTo($expiration)) {
                return response()->json(["message" => "Please Log In again."]);
            }

            return $next($request);

        } catch (\Throwable $th) {
            return response()->json(["message" => "Something went wrong when verifying your account."]);
        }

    }
}
