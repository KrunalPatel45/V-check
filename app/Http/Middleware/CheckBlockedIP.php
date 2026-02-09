<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\BlockedIP;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckBlockedIP
{
    public function handle(Request $request, Closure $next)
{
    // Skip admin routes
    if ($request->is('admin*')) {
        return $next($request);
    }

    $ip = $request->ip();

    $blocked = BlockedIP::where('ip_address', $ip)->first();

    if ($blocked) {
        return response()->view('errors.blocked', [], 403);
    }

    return $next($request);
}

}
