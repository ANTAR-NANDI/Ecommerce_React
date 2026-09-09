<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosOrder extends Model
{
    protected $fillable=['order_number','warehouse_id','customer_name','customer_phone','payment_method','status','subtotal','total'];
    protected function casts(): array { return ['subtotal'=>'decimal:2','total'=>'decimal:2']; }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function items(): HasMany { return $this->hasMany(PosOrderItem::class); }
}
