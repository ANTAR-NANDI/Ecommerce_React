<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountCoa extends Model
{
    protected $fillable = ['parent_id', 'code', 'head_name', 'account_type', 'is_group', 'supplier_id', 'customer_id', 'employee_id'];
    protected function casts(): array { return ['is_group' => 'boolean']; }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('code'); }
    public function transactions(): HasMany { return $this->hasMany(AccountTransaction::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function employee(): BelongsTo { return $this->belongsTo(User::class, 'employee_id'); }
}
