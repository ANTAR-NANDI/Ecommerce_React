<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(): View { return view('admin.purchases.index', ['purchases' => Purchase::with('warehouse')->latest('purchase_date')->paginate(12)]); }
    public function create(): View { return view('admin.purchases.create', ['warehouses'=>Warehouse::where('is_active',true)->orderBy('name')->get(), 'products'=>Product::orderBy('name')->get(), 'nextNumber'=>'PUR-'.now()->format('ymd').'-'.str_pad((string)(Purchase::count()+1),4,'0',STR_PAD_LEFT)]); }
    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate(['warehouse_id'=>'required|exists:warehouses,id','supplier_name'=>'required|max:150','supplier_phone'=>'nullable|max:30','invoice_number'=>'nullable|max:100','purchase_date'=>'required|date','status'=>'required|in:draft,ordered,received','discount'=>'nullable|numeric|min:0','tax'=>'nullable|numeric|min:0','notes'=>'nullable','items'=>'required|array|min:1','items.*.product_id'=>'nullable|exists:products,id','items.*.product_name'=>'required|max:180','items.*.quantity'=>'required|numeric|gt:0','items.*.unit_cost'=>'required|numeric|min:0']);
        return DB::transaction(function () use ($data) { $subtotal=collect($data['items'])->sum(fn($item)=>$item['quantity']*$item['unit_cost']); $purchase=Purchase::create(['purchase_number'=>'PUR-'.now()->format('ymdHis').'-'.random_int(100,999),'warehouse_id'=>$data['warehouse_id'],'supplier_name'=>$data['supplier_name'],'supplier_phone'=>$data['supplier_phone']??null,'invoice_number'=>$data['invoice_number']??null,'purchase_date'=>$data['purchase_date'],'status'=>$data['status'],'subtotal'=>$subtotal,'discount'=>$data['discount']??0,'tax'=>$data['tax']??0,'total'=>$subtotal-($data['discount']??0)+($data['tax']??0),'notes'=>$data['notes']??null]); foreach($data['items'] as $item){$purchase->items()->create($item+['line_total'=>$item['quantity']*$item['unit_cost']]);} return to_route('admin.purchases.index')->with('success','Purchase saved for '.$purchase->warehouse->name.'.'); });
    }
}
