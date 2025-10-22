<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PartnerAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('partner')->check()) {
            return redirect()->route('partner:login');
        }

        $partner = Auth::guard('partner')->user();

        // Check if partner is active
        if (!$partner->status) {
            Auth::guard('partner')->logout();
            return redirect()->route('partner:login')->with('error', 'Your account is inactive.');
        }

        // Check if partner is approved
        if ($partner->approval_status !== 'approved') {
            Auth::guard('partner')->logout();
            $message = $partner->approval_status === 'rejected' 
                ? 'Your account has been rejected.'
                : 'Your account is pending approval.';
            return redirect()->route('partner:login')->with('error', $message);
        }

        return $next($request);
    }
}
