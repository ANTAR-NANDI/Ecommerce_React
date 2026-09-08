<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View { return view('admin.pos.index',['products'=>Product::latest()->take(30)->get(),'brands'=>Brand::where('is_active',true)->get(),'categories'=>Category::where('is_active',true)->get(),'warehouses'=>Warehouse::where('is_active',true)->get()]); }
}
