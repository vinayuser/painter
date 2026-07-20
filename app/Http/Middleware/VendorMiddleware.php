<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if (! $user || $user->role !== UserRole::Vendor || ! $user->is_active) {
            return redirect()->route('vendor.login');
        }

        return $next($request);
    }
}
