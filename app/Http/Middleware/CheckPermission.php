<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		if (auth()->check()) {
			$excl = ['dashboard', 'check-visitor'];
			$user = auth()->user();
			$name = $request->route()->getName();
			if (in_array($name, $excl) || $user->hasRole('Super Admin') || $user->hasPermissionTo($name)) {
				return $next($request);
			} else {
				Log::debug("Redirecting $user->name ($user->id) from : " . $request->route()->getName());
                return resp(0, 'Unauthorized Access1',[], Response::HTTP_FORBIDDEN);
			}
		} else {
            return resp(0, 'Unauthenticated1',[], Response::HTTP_UNAUTHORIZED);
		}
	}
}
