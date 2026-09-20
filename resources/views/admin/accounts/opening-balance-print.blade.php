@extends('layouts.admin')
@section('title','Opening Balance '.$balance->year->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print"><div class="content-heading"><h1>Opening Balance</h1><p>{{ $balance->year->name }}</p></div><button type="button" class="btn text-white" style="background:var(--brand)" onclick="window.print()"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</button></div>
<article class="panel invoice-sheet mx-auto">
    <header class="d-flex justify-content-between border-bottom pb-4 mb-4"><div><div class="brand-name fs-4">EBay</div><div class="text-muted small mt-2">Financial year opening balance</div></div><div class="text-end"><div class="fw-bold">{{ $balance->year->name }}</div><div class="small text-muted">{{ $balance->balance_date->format('d M Y') }}</div></div></header>
    <table class="table"><thead><tr><th>Code</th><th>Account head</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead><tbody>@foreach($balance->items as $item)<tr><td>{{ $item->account?->code ?? 'Archived' }}</td><td>{{ $item->account?->head_name ?? 'Archived account #'.$item->account_coa_id }}</td><td class="text-end">{{ $item->debit ? 'BDT '.number_format($item->debit,2) : '—' }}</td><td class="text-end">{{ $item->credit ? 'BDT '.number_format($item->credit,2) : '—' }}</td></tr>@endforeach</tbody><tfoot><tr><th colspan="2" class="text-end">Total</th><th class="text-end">BDT {{ number_format($balance->items->sum('debit'),2) }}</th><th class="text-end">BDT {{ number_format($balance->items->sum('credit'),2) }}</th></tr></tfoot></table>
    <footer class="border-top pt-4 mt-5 row text-muted small"><div class="col-6">Prepared by: ____________________</div><div class="col-6 text-end">Approved by: ____________________</div></footer>
</article>
@endsection
