<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeliveryPanelMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if (! $user || $user->role !== UserRole::DeliveryAgent || ! $user->is_active) {
            return redirect()->route('delivery.login');
        }

        return $next($request);
    }
}
