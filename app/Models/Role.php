<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const MODULES = [
        'dashboard' => 'Dashboard', 'orders' => 'Ecommerce Orders', 'pos' => 'POS & POS Sales History',
        'products' => 'Products & Warehouse Stock', 'categories' => 'Categories', 'variants' => 'Product Variants',
        'purchases' => 'Purchases', 'warehouses' => 'Warehouses', 'suppliers' => 'Suppliers', 'customers' => 'Customers',
        'promotions' => 'Promotions', 'blogs' => 'Blog', 'cms' => 'CMS Pages, Menus & Footer', 'contact' => 'Contact Messages',
        'media' => 'Media Library', 'users' => 'Users & Roles',
    ];
    protected $fillable = ['name', 'slug', 'permissions', 'is_system'];
    protected function casts(): array { return ['permissions' => 'array', 'is_system' => 'boolean']; }
}
