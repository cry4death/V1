<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatientAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('patient')->check()) {
            return redirect()->guest(route('patient.login'));
        }

        return $next($request);
    }
}
