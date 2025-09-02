<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // API key set
        if (!$request->session()->has('api_key')) {
            return redirect('/set-api-key')->with('error', 'Missing API key.');
        }

        // Session expired
        if ($request->session()->get('expires_at') < now()) {
            $request->session()->flush();
            return redirect('/set-api-key')->with('error', 'Session expired.');
        }

        return $next($request);
    }
}
