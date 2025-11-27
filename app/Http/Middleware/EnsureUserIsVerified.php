<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class EnsureUserIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $redirectToRoute = null)
    {
        // Skip if user is not logged in (handled by auth middleware)
        if (! $request->user()) {
            return $next($request);
        }

        // Skip if email verification is not required
        if (! config('auth.verification.required', true)) {
            return $next($request);
        }

        // Check if user is verified
        if (! $request->user()->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your email address is not verified.'], 403)
                : Redirect::route($redirectToRoute ?: 'verification.notice')
                    ->with('warning', 'You must verify your email address before accessing this page.');
        }

        return $next($request);
    }
}
