<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCourier
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'courier') {
            return response()->json([
                'message' => 'Faqat kuryerlar uchun ruxsat berilgan.',
            ], 403);
        }

        return $next($request);
    }
}
