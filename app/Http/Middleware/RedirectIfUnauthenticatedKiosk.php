<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfUnauthenticatedKiosk
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hasKioskStudent = $request->session()->has('kiosk_student_id');
        $hasWebAuth = Auth::check() && !$request->session()->get('kiosk_logged_out', false);

        if (!$hasKioskStudent && !$hasWebAuth) {
            return redirect()->route('kiosk.login', [
                'redirect' => $request->getRequestUri(),
            ]);
        }

        return $next($request);
    }
}
