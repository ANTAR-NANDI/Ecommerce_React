<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    private const MODULES = [
        'admin.dashboard' => 'dashboard', 'admin.orders.' => 'orders', 'admin.pos.' => 'pos', 'admin.products.' => 'products',
        'admin.categories.' => 'categories', 'admin.subcategories.' => 'categories', 'admin.brands.' => 'variants', 'admin.colors.' => 'variants', 'admin.sizes.' => 'variants', 'admin.units.' => 'variants',
        'admin.purchases.' => 'purchases', 'admin.warehouses.' => 'warehouses', 'admin.suppliers.' => 'suppliers', 'admin.customers.' => 'customers',
        'admin.promotions.' => 'promotions', 'admin.blogs.' => 'blogs', 'admin.cms.' => 'cms', 'admin.contact.' => 'contact', 'admin.media.' => 'media',
        'admin.users.' => 'users', 'admin.roles.' => 'users', 'admin.profile.' => 'dashboard',
    ];
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->isSuperAdmin()) return $next($request);
        abort_unless($user && $user->warehouse_id, 403, 'This account must be assigned to a warehouse.');
        $module = collect(self::MODULES)->first(fn ($value, $pattern) => $request->routeIs($pattern));
        abort_unless($module && $user->canAccessModule($module), 403, 'Your role does not have permission for this area.');
        return $next($request);
    }
}
