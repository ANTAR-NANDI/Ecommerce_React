<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransaction extends Model
{
    protected $fillable = ['voucher_no', 'voucher_type', 'transaction_date', 'account_coa_id', 'entry_type', 'amount', 'ledger_comment', 'supplier_id', 'customer_id', 'employee_id', 'purchase_id', 'sale_id', 'ecommerce_order_id', 'pos_order_id', 'created_by'];
    protected function casts(): array { return ['transaction_date' => 'date', 'amount' => 'decimal:2']; }
    public function account(): BelongsTo { return $this->belongsTo(AccountCoa::class, 'account_coa_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
