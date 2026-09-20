<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosOrder extends Model
{
    protected $fillable = ['order_number', 'warehouse_id', 'customer_id', 'customer_name', 'customer_phone', 'payment_method', 'payment_method_id', 'status', 'subtotal', 'discount_type', 'discount_value', 'discount_amount', 'total'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'discount_value' => 'decimal:2', 'discount_amount' => 'decimal:2', 'total' => 'decimal:2'];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }
}
