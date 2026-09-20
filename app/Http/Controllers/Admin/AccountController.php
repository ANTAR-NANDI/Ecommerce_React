<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AccountCoa, AccountTransaction, Customer, EcommerceOrder, Product, Purchase, PurchaseItem, Supplier, Warehouse, WarehouseProductStock, PredefinedAccountMapping, FinancialYear};
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(private readonly AccountService $accounts) {}

    public function coa(): View
    {
        $this->accounts->syncProfileHeads();
        $nodes = AccountCoa::with('children.children.children.children')->whereNull('parent_id')->orderBy('code')->get();
        return view('admin.accounts.coa', ['nodes' => $nodes, 'groups' => AccountCoa::where('is_group', true)->orderBy('code')->get()]);
    }

    public function printCoa(): View
    {
        $this->accounts->syncProfileHeads();
        return view('admin.accounts.coa-print', ['nodes' => AccountCoa::with('children.children.children.children')->whereNull('parent_id')->orderBy('code')->get()]);
    }

    public function subAccounts(): View
    {
        $this->accounts->syncProfileHeads();
        $accounts = AccountCoa::with(['supplier', 'customer', 'employee'])
            ->where('is_group', false)
            ->where(fn ($query) => $query->whereNotNull('supplier_id')->orWhereNotNull('customer_id')->orWhereNotNull('employee_id'))
            ->latest()->paginate(15);

        return view('admin.accounts.sub-accounts', compact('accounts'));
    }

    public function predefinedAccounts(): View
    {
        return view('admin.accounts.predefined-accounts', ['accounts' => AccountCoa::where('is_group', false)->orderBy('code')->get(), 'mappings' => PredefinedAccountMapping::pluck('account_coa_id', 'key')]);
    }

    public function financialYears(): View { return view('admin.accounts.financial-years', ['years' => FinancialYear::latest('start_date')->paginate(15)]); }

    public function openingBalances(): View { return view('admin.accounts.opening-balances', ['balances'=>\App\Models\OpeningBalance::with(['year','items'])->latest()->paginate(15),'years'=>FinancialYear::whereNull('closed_at')->latest('start_date')->get(),'accounts'=>AccountCoa::where('is_group',false)->orderBy('code')->get()]); }

    public function printOpeningBalance(\App\Models\OpeningBalance $openingBalance): View { return view('admin.accounts.opening-balance-print', ['balance' => $openingBalance->load(['year', 'items.account'])]); }

    public function paymentMethods(): View { $cash=AccountCoa::where('code','10011')->firstOrFail();\App\Models\PaymentMethod::firstOrCreate(['name'=>'Cash in Hand'],['account_coa_id'=>$cash->id,'is_fixed'=>true]);\App\Models\PaymentMethod::whereDoesntHave('account')->each(function($method)use($cash){$method->update(['account_coa_id'=>$cash->id,'is_fixed'=>true]);});return view('admin.accounts.payment-methods',['methods'=>\App\Models\PaymentMethod::with('account')->latest()->paginate(15)]); }
    public function storePaymentMethod(Request $request): RedirectResponse { $data=$request->validate(['name'=>'required|string|max:100|unique:payment_methods,name']);$parent=AccountCoa::where('code','10012')->firstOrFail();$account=$this->accounts->createChild($parent,'Cash at '.$data['name']);\App\Models\PaymentMethod::create(['name'=>$data['name'],'account_coa_id'=>$account->id]);return back()->with('success',"Payment method created under Bank Accounts as {$account->head_name}."); }
    public function settlement(string $type): View { abort_unless(in_array($type,['supplier-payment','customer-receive']),404);$this->paymentMethods();$transactions=AccountTransaction::with(['account','supplier','customer'])->where('voucher_type',$type==='supplier-payment'?'debit':'credit')->whereNotNull($type==='supplier-payment'?'supplier_id':'customer_id')->latest('transaction_date')->latest('id')->paginate(20);return view('admin.accounts.settlement',['type'=>$type,'suppliers'=>Supplier::orderBy('name')->get(),'customers'=>Customer::orderBy('first_name')->get(),'methods'=>\App\Models\PaymentMethod::with('account')->get(),'purchases'=>Purchase::latest('purchase_date')->get(),'orders'=>EcommerceOrder::latest()->get(),'transactions'=>$transactions]); }
    public function storeSettlement(Request $request,string $type): RedirectResponse { abort_unless(in_array($type,['supplier-payment','customer-receive']),404);$data=$request->validate(['entity_id'=>'required|integer','payment_method_id'=>'required|exists:payment_methods,id','amount'=>'required|numeric|gt:0','transaction_date'=>'required|date','remark'=>'nullable|max:1000']);$method=\App\Models\PaymentMethod::findOrFail($data['payment_method_id']);if($type==='supplier-payment'){$supplier=Supplier::findOrFail($data['entity_id']);$entity=AccountCoa::where('supplier_id',$supplier->id)->firstOrFail();$voucher=$this->accounts->postVoucher(['voucher_type'=>'debit','transaction_date'=>$data['transaction_date'],'debit_account_id'=>$entity->id,'credit_account_id'=>$method->account_coa_id,'amount'=>$data['amount'],'ledger_comment'=>$data['remark'],'supplier_id'=>$supplier->id],$request->user()->id);}else{$customer=Customer::findOrFail($data['entity_id']);$entity=AccountCoa::where('customer_id',$customer->id)->firstOrFail();$voucher=$this->accounts->postVoucher(['voucher_type'=>'credit','transaction_date'=>$data['transaction_date'],'debit_account_id'=>$method->account_coa_id,'credit_account_id'=>$entity->id,'amount'=>$data['amount'],'ledger_comment'=>$data['remark'],'customer_id'=>$customer->id],$request->user()->id);}return back()->with('success',"Payment saved as {$voucher}."); }
    public function cashAdjustment(): View { return view('admin.accounts.cash-adjustment',['cash'=>AccountCoa::where('code','10011')->firstOrFail()]); }
    public function storeCashAdjustment(Request $request): RedirectResponse { $data=$request->validate(['transaction_date'=>'required|date','adjustment_type'=>'required|in:debit,credit','amount'=>'required|numeric|gt:0','remark'=>'nullable|max:1000']);$cash=AccountCoa::where('code','10011')->firstOrFail();$adjustment=AccountCoa::where('head_name','Cash Adjustment')->first()??$this->accounts->createChild(AccountCoa::where('code','500')->firstOrFail(),'Cash Adjustment');$voucher=$this->accounts->postVoucher(['voucher_type'=>'journal','transaction_date'=>$data['transaction_date'],'debit_account_id'=>$data['adjustment_type']==='debit'?$cash->id:$adjustment->id,'credit_account_id'=>$data['adjustment_type']==='credit'?$cash->id:$adjustment->id,'amount'=>$data['amount'],'ledger_comment'=>$data['remark']],$request->user()->id);return back()->with('success',"Cash adjustment {$voucher} saved."); }
    public function storeOpeningBalance(Request $request): RedirectResponse { $data=$request->validate(['financial_year_id'=>'required|exists:financial_years,id','balance_date'=>'required|date','items'=>'required|array|min:1','items.*.account_coa_id'=>'required|distinct|exists:account_coas,id','items.*.debit'=>'nullable|numeric|min:0','items.*.credit'=>'nullable|numeric|min:0']);$debit=collect($data['items'])->sum('debit');$credit=collect($data['items'])->sum('credit');if($debit<=0||round($debit,2)!==round($credit,2))return back()->withInput()->withErrors(['items'=>'Debit and credit totals must be equal.']);\Illuminate\Support\Facades\DB::transaction(function()use($data,$request){$balance=\App\Models\OpeningBalance::updateOrCreate(['financial_year_id'=>$data['financial_year_id']],['balance_date'=>$data['balance_date']]);$balance->items()->delete();$balance->items()->createMany($data['items']);$voucherNo='OPENING-FY-'.$data['financial_year_id'];AccountTransaction::where('voucher_no',$voucherNo)->delete();$entries=collect($data['items'])->flatMap(fn($item)=>collect([['account_coa_id'=>$item['account_coa_id'],'entry_type'=>'debit','amount'=>(float)($item['debit']??0)],['account_coa_id'=>$item['account_coa_id'],'entry_type'=>'credit','amount'=>(float)($item['credit']??0)]])->filter(fn($entry)=>$entry['amount']>0))->values()->all();$this->accounts->postEntries($entries,['voucher_no'=>$voucherNo,'voucher_type'=>'journal','transaction_date'=>$data['balance_date'],'ledger_comment'=>'Opening balance for financial year '.$data['financial_year_id']],$request->user()->id);});return to_route('admin.accounts.opening-balances')->with('success','Opening balance saved and posted to the ledger automatically.'); }

    public function storeFinancialYear(Request $request): RedirectResponse
    {
        $data = $request->validate(['name'=>['required','string','max:30','unique:financial_years,name'],'start_date'=>['required','date'],'end_date'=>['required','date','after:start_date']]);
        FinancialYear::create($data);
        return back()->with('success', 'Financial year created.');
    }

    public function closeFinancialYear(FinancialYear $financialYear): RedirectResponse
    {
        $financialYear->update(['closed_at' => now()]);
        return back()->with('success', "Financial year {$financialYear->name} closed.");
    }

    public function updatePredefinedAccounts(Request $request): RedirectResponse
    {
        $data = $request->validate(['mappings' => ['required', 'array'], 'mappings.*' => ['nullable', 'exists:account_coas,id']]);
        foreach ($data['mappings'] as $key => $accountId) PredefinedAccountMapping::updateOrCreate(['key' => $key], ['account_coa_id' => $accountId]);
        return back()->with('success', 'Predefined account mappings updated.');
    }

    public function storeCoa(Request $request): RedirectResponse
    {
        $data = $request->validate(['parent_id' => ['required', 'exists:account_coas,id'], 'head_name' => ['required', 'string', 'max:180']]);
        $account = $this->accounts->createChild(AccountCoa::findOrFail($data['parent_id']), $data['head_name']);
        return back()->with('success', "Account head {$account->code} created successfully.");
    }

    public function vouchers(Request $request): View
    {
        $voucherType = $request->string('type', 'debit')->toString();
        abort_unless(in_array($voucherType, ['debit', 'credit', 'contra', 'journal'], true), 404);
        return view('admin.accounts.vouchers', [
            'accounts' => AccountCoa::where('is_group', false)->orderBy('code')->get(),
            'transactions' => AccountTransaction::with('account')->where('voucher_type', $voucherType)->latest('transaction_date')->latest('id')->paginate(20),
            'voucherType' => $voucherType,
            'voucherLabel' => ucfirst($voucherType).' Voucher',
        ]);
    }

    public function printVoucher(string $voucherNo): View
    {
        $entries = AccountTransaction::with(['account', 'supplier', 'customer'])
            ->where('voucher_no', $voucherNo)
            ->orderBy('entry_type')
            ->orderBy('id')
            ->get();

        abort_unless($entries->isNotEmpty(), 404);

        return view('admin.accounts.voucher-print', [
            'voucherNo' => $voucherNo,
            'entries' => $entries,
            'voucherType' => $entries->first()->voucher_type,
            'transactionDate' => $entries->first()->transaction_date,
            'narration' => $entries->first()->ledger_comment,
        ]);
    }

    public function storeVoucher(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'voucher_type' => ['required', 'in:debit,credit,contra,journal'], 'transaction_date' => ['required', 'date'],
            'debit_account_id' => ['required', 'different:credit_account_id', 'exists:account_coas,id'], 'credit_account_id' => ['required', 'exists:account_coas,id'],
            'amount' => ['required', 'numeric', 'gt:0'], 'ledger_comment' => ['nullable', 'string', 'max:2000'],
        ]);
        $voucher = $this->accounts->postVoucher($data, $request->user()->id);
        return to_route('admin.accounts.vouchers', ['type' => $data['voucher_type']])->with('success', "Voucher {$voucher} posted with balanced debit and credit entries.");
    }

    public function reports(Request $request): View
    {
        $report = $request->string('report', 'stock')->toString();
        abort_unless(in_array($report, ['stock', 'sales', 'purchase', 'supplier-ledger', 'customer-ledger'], true), 404);
        $filters = $request->validate(['from_date' => ['nullable', 'date'], 'to_date' => ['nullable', 'date', 'after_or_equal:from_date'], 'product_id' => ['nullable', 'exists:products,id'], 'warehouse_id' => ['nullable', 'exists:warehouses,id'], 'customer_id' => ['nullable'], 'supplier_id' => ['nullable', 'exists:suppliers,id']]);
        $data = match ($report) {
            'stock' => $this->stockReport($filters),
            'sales' => $this->salesReport($filters),
            'purchase' => $this->purchaseReport($filters),
            'supplier-ledger' => $this->ledgerReport($filters, 'supplier'),
            'customer-ledger' => $this->ledgerReport($filters, 'customer'),
        };
        return view('admin.accounts.reports', $data + ['report' => $report, 'filters' => $filters, 'products' => Product::orderBy('name')->get(), 'warehouses' => Warehouse::orderBy('name')->get(), 'customers' => Customer::orderBy('first_name')->get(), 'suppliers' => Supplier::orderBy('name')->get()]);
    }

    private function stockReport(array $filters): array
    {
        $query = WarehouseProductStock::with(['product', 'warehouse'])->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('updated_at', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('updated_at', '<=', $date));
        return ['rows' => $query->orderBy('warehouse_id')->paginate(50), 'summary' => ['quantity' => (clone $query)->sum('quantity')]];
    }

    private function salesReport(array $filters): array
    {
        $query = EcommerceOrder::with(['customer', 'items'])->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))->when($filters['customer_id'] ?? null, fn ($q, $id) => $id === 'walking' ? $q->whereNull('customer_id') : $q->where('customer_id', $id))->when($filters['product_id'] ?? null, fn ($q, $id) => $q->whereHas('items', fn ($items) => $items->where('product_id', $id)));
        return ['rows' => $query->latest()->paginate(50), 'summary' => ['total' => (clone $query)->sum('total'), 'count' => (clone $query)->count()]];
    }

    private function purchaseReport(array $filters): array
    {
        $query = Purchase::with(['supplier', 'warehouse', 'items'])->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('purchase_date', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('purchase_date', '<=', $date))->when($filters['supplier_id'] ?? null, fn ($q, $id) => $q->where('supplier_id', $id))->when($filters['product_id'] ?? null, fn ($q, $id) => $q->whereHas('items', fn ($items) => $items->where('product_id', $id)));
        return ['rows' => $query->latest('purchase_date')->paginate(50), 'summary' => ['total' => (clone $query)->sum('total'), 'count' => (clone $query)->count()]];
    }

    private function ledgerReport(array $filters, string $entity): array
    {
        $key = $entity.'_id';
        $query = AccountTransaction::with('account')->whereNotNull($key)->when($filters[$key] ?? null, fn ($q, $id) => $q->where($key, $id))->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date));
        return ['rows' => $query->orderBy('transaction_date')->orderBy('id')->paginate(100), 'summary' => ['debit' => (clone $query)->where('entry_type', 'debit')->sum('amount'), 'credit' => (clone $query)->where('entry_type', 'credit')->sum('amount')]];
    }
}
