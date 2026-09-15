@extends('layouts.admin')
@section('title','Chart of Accounts')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 content-heading mb-4"><div><h1>Chart of Accounts</h1><p>Create account heads under any group account. Codes are generated automatically from the parent: <strong>100 → 1001 → 10011</strong>.</p></div></div>
<div class="row g-4"><div class="col-xl-8"><div class="panel p-0 overflow-hidden"><div class="p-3 border-bottom d-flex justify-content-between"><strong>Account tree</strong><span class="small text-muted">Profile heads are created automatically for suppliers, customers, and employees.</span></div><div class="table-responsive"><table class="table mb-0 align-middle"><thead><tr><th>Account head</th><th>Code</th><th>Type</th><th>Kind</th></tr></thead><tbody>@php
    $draw = null;
    $draw = function ($items, $depth = 0) use (&$draw) {
        foreach ($items as $account) {
            echo '<tr><td style="padding-left:'.(24 + ($depth * 28)).'px"><i class="bi '.($account->is_group ? 'bi-folder2-open text-warning' : 'bi-file-earmark-text text-secondary').' me-2"></i><strong>'.e($account->head_name).'</strong></td><td><code>'.e($account->code).'</code></td><td>'.e(ucfirst($account->account_type)).'</td><td><span class="badge '.($account->is_group ? 'text-bg-light' : 'text-bg-secondary').'">'.($account->is_group ? 'Group' : 'Ledger').'</span></td></tr>';
            if ($account->children->isNotEmpty()) $draw($account->children, $depth + 1);
        }
    };
    $draw($nodes);
@endphp</tbody></table></div></div></div><div class="col-xl-4"><form method="POST" action="{{ route('admin.accounts.coa.store') }}" class="panel">@csrf<h5 class="mb-3">Create sub-account</h5><div class="mb-3"><label class="form-label">Parent group</label><select class="form-select" name="parent_id" required><option value="">Choose group account</option>@foreach($groups as $group)<option value="{{ $group->id }}" @selected(old('parent_id') == $group->id)>{{ $group->code }} — {{ $group->head_name }}</option>@endforeach</select></div><div class="mb-3"><label class="form-label">Account head name</label><input class="form-control" name="head_name" value="{{ old('head_name') }}" placeholder="e.g. Petty Cash" required></div><button class="btn text-white" style="background:var(--brand)"><i class="bi bi-plus-lg me-1"></i>Create account head</button></form><div class="panel small text-muted"><strong class="d-block text-dark mb-2">Automatic ledger mapping</strong>Supplier → Accounts Payable<br>Customer → Accounts Receivable<br>Employee (admin user) → Employee Advances</div></div></div>
@endsection
