<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso restrito ao Admin Master.',
                'errors' => [],
            ], 403);
        }

        return $next($request);
    }
}
