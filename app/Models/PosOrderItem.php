<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderItem extends Model
{
    protected $fillable=['product_id','product_name','quantity','unit_price','line_total'];
    protected function casts(): array { return ['quantity'=>'decimal:3','unit_price'=>'decimal:2','line_total'=>'decimal:2']; }
    public function order(): BelongsTo { return $this->belongsTo(PosOrder::class,'pos_order_id'); }
}
