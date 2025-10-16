<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {

        $actions = $request->user()->roles
            ->load("permissions")
            ->pluck("permissions")
            ->flatten()
            ->pluck("action");

        if (!$actions->contains($permission)) {

            [$action, $resource] = explode(".", $permission);

            throw new UnauthorizedException("You are not allowed to perform the action: " . Str::ucfirst($action) . " " . Str::ucfirst($resource));
        }

        return $next($request);
    }
}
