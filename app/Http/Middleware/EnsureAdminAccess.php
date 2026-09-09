<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->isSuperAdmin()) return $next($request);
        abort_unless($user && $user->warehouse_id, 403, 'This account must be assigned to a warehouse.');
        abort_unless($request->routeIs('admin.dashboard', 'admin.pos.*', 'admin.orders.*', 'admin.products.index'), 403, 'Your role does not have permission for this area.');
        return $next($request);
    }
}
