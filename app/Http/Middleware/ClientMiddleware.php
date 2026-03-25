<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if (!$user->company_id) {
            Auth::logout();
            return redirect()->route('home')
                ->with('error', 'Votre compte n\'est pas associé à une entreprise.');
        }

        return $next($request);
    }
}
