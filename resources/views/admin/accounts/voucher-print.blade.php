@extends('layouts.admin')
@section('title', ucfirst($voucherType).' Voucher '.$voucherNo)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div class="content-heading"><h1>{{ ucfirst($voucherType) }} Voucher</h1><p>{{ $voucherNo }}</p></div>
    <div class="d-flex gap-2"><button type="button" class="btn btn-outline-secondary" onclick="window.close()">Close</button><button type="button" class="btn text-white" style="background:var(--brand)" onclick="window.print()"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</button></div>
</div>
<article class="panel invoice-sheet mx-auto voucher-sheet">
    <header class="d-flex justify-content-between border-bottom pb-4 mb-4"><div><div class="brand-name fs-4">EBay</div><div class="text-muted small mt-2">Accounting voucher</div></div><div class="text-end"><div class="small text-uppercase text-muted">{{ ucfirst($voucherType) }} voucher</div><div class="fw-bold fs-5">{{ $voucherNo }}</div><div class="text-muted small">{{ $transactionDate->format('d M Y') }}</div></div></header>
    <div class="row mb-4"><div class="col-sm-7"><div class="text-muted small text-uppercase mb-1">Narration</div><div>{{ $narration ?: 'System-generated accounting entry' }}</div></div><div class="col-sm-5 text-sm-end mt-3 mt-sm-0"><div class="text-muted small text-uppercase mb-1">Reference</div><div>{{ $entries->first()->supplier?->name ?: ($entries->first()->customer?->full_name ?: 'Internal transaction') }}</div></div></div>
    <table class="table"><thead><tr><th>Account code</th><th>Account head</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead><tbody>@foreach($entries as $entry)<tr><td>{{ $entry->account->code }}</td><td>{{ $entry->account->head_name }}</td><td class="text-end">{{ $entry->entry_type === 'debit' ? 'BDT '.number_format($entry->amount,2) : '—' }}</td><td class="text-end">{{ $entry->entry_type === 'credit' ? 'BDT '.number_format($entry->amount,2) : '—' }}</td></tr>@endforeach</tbody><tfoot><tr><th colspan="2" class="text-end">Total</th><th class="text-end">BDT {{ number_format($entries->where('entry_type','debit')->sum('amount'),2) }}</th><th class="text-end">BDT {{ number_format($entries->where('entry_type','credit')->sum('amount'),2) }}</th></tr></tfoot></table>
    <footer class="border-top pt-4 mt-5 row text-muted small"><div class="col-6">Prepared by: ____________________</div><div class="col-6 text-end">Approved by: ____________________</div></footer>
</article>
@endsection
