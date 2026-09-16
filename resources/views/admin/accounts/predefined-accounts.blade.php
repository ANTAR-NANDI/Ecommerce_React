@extends('layouts.admin')
@section('title', 'Predefined Accounts')
@section('content')
@php($definitions = ['sales_revenue'=>'Sales revenue','cost_of_goods_sold'=>'Cost of goods sold','vat'=>'VAT / sales tax','inventory'=>'Inventory','accounts_receivable'=>'Accounts receivable','accounts_payable'=>'Accounts payable','cash_in_hand'=>'Cash in hand','bank_account'=>'Bank account','salary_expense'=>'Salary expense','employee_advance'=>'Employee advance'])
<div class="content-heading mb-4"><h1>Predefined Accounts</h1><p>Choose which ledger account should be used automatically for each business activity.</p></div>
<form method="POST" action="{{ route('admin.accounts.predefined-accounts.update') }}" class="panel" style="max-width:850px">@csrf @method('PUT')<div class="row g-3">@foreach($definitions as $key=>$label)<div class="col-md-6"><label class="form-label">{{ $label }}</label><select class="form-select" name="mappings[{{ $key }}]"><option value="">Select account</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(old("mappings.$key", $mappings[$key] ?? null) == $account->id)>{{ $account->code }} — {{ $account->head_name }}</option>@endforeach</select></div>@endforeach</div><button class="btn text-white mt-4" style="background:var(--brand)">Save predefined accounts</button></form>
@endsection
