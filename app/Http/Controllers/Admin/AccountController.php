<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AccountCoa, AccountTransaction, Customer, EcommerceOrder, PosOrder, Product, Purchase, PurchaseItem, Supplier, Warehouse, WarehouseProductStock, PredefinedAccountMapping, FinancialYear};
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
    public function settlement(string $type): View
    {
        abort_unless(in_array($type, ['supplier-payment', 'customer-receive']), 404);
        $this->paymentMethods();
        $supplierMode = $type === 'supplier-payment';
        $transactions = AccountTransaction::with(['account', 'supplier', 'customer'])
            ->where('voucher_type', $supplierMode ? 'debit' : 'credit')
            ->whereNotNull($supplierMode ? 'supplier_id' : 'customer_id')
            ->latest('transaction_date')->latest('id')->paginate(20);

        return view('admin.accounts.settlement', [
            'type' => $type,
            'suppliers' => Supplier::orderBy('name')->get(),
            'customers' => Customer::orderBy('first_name')->get(),
            'methods' => \App\Models\PaymentMethod::with('account')->get(),
            'references' => $this->settlementReferences($type),
            'transactions' => $transactions,
        ]);
    }

    public function storeSettlement(Request $request, string $type): RedirectResponse
    {
        abort_unless(in_array($type, ['supplier-payment', 'customer-receive']), 404);
        $data = $request->validate([
            'entity_id' => 'required|integer', 'reference' => 'required|string',
            'payment_method_id' => 'required|exists:payment_methods,id', 'amount' => 'required|numeric|gt:0',
            'transaction_date' => 'required|date', 'remark' => 'nullable|max:1000',
        ]);
        [$referenceType, $referenceId] = array_pad(explode(':', $data['reference'], 2), 2, null);
        $method = \App\Models\PaymentMethod::findOrFail($data['payment_method_id']);

        if ($type === 'supplier-payment') {
            abort_unless($referenceType === 'purchase', 422);
            $supplier = Supplier::findOrFail($data['entity_id']);
            $reference = Purchase::whereKey($referenceId)->where('supplier_id', $supplier->id)->where('status', 'received')->firstOrFail();
            $entity = $this->accounts->ensureSupplierHead($supplier);
            $due = $this->purchaseDue($reference, $entity->id);
            $context = ['supplier_id' => $supplier->id, 'purchase_id' => $reference->id];
            $voucherType = 'debit';
        } else {
            $customer = Customer::findOrFail($data['entity_id']);
            $entity = $this->accounts->ensureCustomerHead($customer);
            $reference = match ($referenceType) {
                'pos' => PosOrder::whereKey($referenceId)->where('customer_id', $customer->id)->where('status', 'completed')->where('payment_method', 'Due')->firstOrFail(),
                'ecommerce' => EcommerceOrder::whereKey($referenceId)->where('customer_id', $customer->id)->where('status', 'delivered')->firstOrFail(),
                default => abort(422),
            };
            $due = $this->customerOrderDue($reference, $referenceType, $entity->id);
            $context = ['customer_id' => $customer->id, $referenceType === 'pos' ? 'pos_order_id' : 'ecommerce_order_id' => $reference->id];
            $voucherType = 'credit';
        }
        if (round((float) $data['amount'], 2) > $due) {
            return back()->withInput()->withErrors(['amount' => 'Amount cannot exceed the outstanding due of BDT '.number_format($due, 2).'.']);
        }

        $voucher = $this->accounts->postVoucher([
            'voucher_type' => $voucherType, 'transaction_date' => $data['transaction_date'],
            'debit_account_id' => $voucherType === 'debit' ? $entity->id : $method->account_coa_id,
            'credit_account_id' => $voucherType === 'credit' ? $entity->id : $method->account_coa_id,
            'amount' => $data['amount'], 'ledger_comment' => $data['remark'],
        ] + $context, $request->user()->id);

        return back()->with('success', "Payment saved as {$voucher}.");
    }

    private function settlementReferences(string $type): \Illuminate\Support\Collection
    {
        if ($type === 'supplier-payment') {
            $heads = AccountCoa::whereNotNull('supplier_id')->pluck('id', 'supplier_id');
            return Purchase::where('status', 'received')->latest('purchase_date')->get()->map(function (Purchase $purchase) use ($heads) {
                $due = $this->purchaseDue($purchase, $heads[$purchase->supplier_id] ?? null);
                return ['type' => 'purchase', 'id' => $purchase->id, 'entity_id' => $purchase->supplier_id, 'number' => $purchase->purchase_number, 'due' => $due];
            })->filter(fn ($reference) => $reference['due'] > 0)->values();
        }

        $heads = AccountCoa::whereNotNull('customer_id')->pluck('id', 'customer_id');
        $pos = PosOrder::where('status', 'completed')->where('payment_method', 'Due')->whereNotNull('customer_id')->latest()->get()
            ->map(function (PosOrder $order) use ($heads) { $due = $this->customerOrderDue($order, 'pos', $heads[$order->customer_id] ?? null); return ['type' => 'pos', 'id' => $order->id, 'entity_id' => $order->customer_id, 'number' => $order->order_number, 'due' => $due]; });
        $ecommerce = EcommerceOrder::where('status', 'delivered')->whereNotNull('customer_id')->latest()->get()
            ->map(function (EcommerceOrder $order) use ($heads) { $due = $this->customerOrderDue($order, 'ecommerce', $heads[$order->customer_id] ?? null); return ['type' => 'ecommerce', 'id' => $order->id, 'entity_id' => $order->customer_id, 'number' => $order->order_number, 'due' => $due]; });

        return $pos->merge($ecommerce)->filter(fn ($reference) => $reference['due'] > 0)->values();
    }

    private function purchaseDue(Purchase $purchase, ?int $supplierHeadId): float
    {
        $paid = $supplierHeadId ? AccountTransaction::where('purchase_id', $purchase->id)->where('account_coa_id', $supplierHeadId)->where('entry_type', 'debit')->sum('amount') : 0;
        return max(0, round((float) $purchase->total - (float) $paid, 2));
    }

    private function customerOrderDue(PosOrder|EcommerceOrder $order, string $type, ?int $customerHeadId): float
    {
        $paid = $customerHeadId ? AccountTransaction::where($type === 'pos' ? 'pos_order_id' : 'ecommerce_order_id', $order->id)->where('account_coa_id', $customerHeadId)->where('entry_type', 'credit')->sum('amount') : 0;
        return max(0, round((float) $order->total - (float) $paid, 2));
    }
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
        $data = $request->validate(['parent_id' => ['required', 'exists:account_coas,id'], 'head_name' => ['required', 'string', 'max:180'], 'is_group' => ['nullable', 'boolean']]);
        $account = $this->accounts->createChild(AccountCoa::findOrFail($data['parent_id']), $data['head_name'], (bool) ($data['is_group'] ?? false));
        $kind = $account->is_group ? 'group' : 'head';
        return back()->with('success', "Account {$kind} {$account->code} created successfully.");
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
