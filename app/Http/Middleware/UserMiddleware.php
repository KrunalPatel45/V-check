<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PaymentSubscription;
use Carbon\Carbon;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && User::where('Email', Auth::user()->Email)->exists()) {
            $PaymentSubscription = PaymentSubscription::where('PackageID', Auth::user()->CurrentPackageID)->where('UserID', Auth::user()->UserID)->where('Status', 'Canceled')->first();
            if ($PaymentSubscription) {
                $nextRenewalDate = Carbon::parse($PaymentSubscription->NextRenewalDate);
            
                if ($nextRenewalDate->isPast()) {
                    return redirect()->route('expired_sub');
                }
            }
            return $next($request);
        }


        // Redirect if the user is not an admin
        return redirect()->route('user.login')->with('error', 'You do not have access.');
    }
}
