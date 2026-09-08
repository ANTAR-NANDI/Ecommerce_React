<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Brand,Category,Color,Product,Size,Subcategory,Unit};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View { return view('admin.products.index', ['products' => Product::latest()->paginate(12)]); }
    public function create(): View { return view('admin.products.create', ['categories'=>Category::where('is_active',true)->get(),'subcategories'=>Subcategory::all(),'brands'=>Brand::where('is_active',true)->get(),'colors'=>Color::where('is_active',true)->get(),'units'=>Unit::where('is_active',true)->get(),'sizes'=>Size::where('is_active',true)->get()]); }
    public function store(Request $request): RedirectResponse { $data=$request->validate(['name'=>'required|max:180','short_description'=>'required|max:500','description'=>'nullable','category_id'=>'nullable|exists:categories,id','subcategory_ids'=>'nullable|array','brand_id'=>'nullable|exists:brands,id','color_id'=>'nullable|exists:colors,id','unit_id'=>'nullable|exists:units,id','size_id'=>'nullable|exists:sizes,id','sku'=>'required|max:100|unique:products,sku','weight'=>'nullable|numeric','buying_price'=>'required|numeric','selling_price'=>'required|numeric','discount_type'=>'required|in:percent,amount','discount'=>'nullable|numeric','stock_quantity'=>'required|integer|min:0','thumbnail_media_id'=>'nullable|exists:media,id','gallery_media_ids'=>'nullable|array','video_url'=>'nullable|url','meta_title'=>'nullable|max:255','meta_description'=>'nullable','meta_keywords'=>'nullable|max:500']); $data['slug']=Str::slug($data['name']).'-'.Str::lower(Str::random(5)); $data['discount']=$data['discount']??0; Product::create($data); return to_route('admin.products.index')->with('success','Product created successfully.'); }
}
