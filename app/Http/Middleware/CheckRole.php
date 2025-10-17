<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {

        $roles = $request->user()->roles->pluck("role");

        if (!$roles->contains($role)) {
            throw new UnauthorizedException("You are not allowed to access this resource.");
        }

        return $next($request);
    }
}
