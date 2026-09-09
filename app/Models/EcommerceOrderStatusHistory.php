<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceOrderStatusHistory extends Model
{
    protected $fillable = ['status','note'];
    public function order(): BelongsTo { return $this->belongsTo(EcommerceOrder::class, 'ecommerce_order_id'); }
}
