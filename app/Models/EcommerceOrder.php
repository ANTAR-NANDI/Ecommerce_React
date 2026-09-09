<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcommerceOrder extends Model
{
    public const STATUSES = ['pending', 'accepted', 'processing', 'shipped', 'delivered', 'cancelled'];
    protected $fillable = ['order_number','customer_name','customer_phone','customer_email','shipping_address','warehouse_id','payment_method','payment_status','status','subtotal','shipping_charge','discount','total','customer_note'];
    protected function casts(): array { return ['subtotal'=>'decimal:2','shipping_charge'=>'decimal:2','discount'=>'decimal:2','total'=>'decimal:2']; }
    protected static function booted(): void { static::created(fn (self $order) => $order->statusHistory()->create(['status'=>$order->status])); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function items(): HasMany { return $this->hasMany(EcommerceOrderItem::class); }
    public function statusHistory(): HasMany { return $this->hasMany(EcommerceOrderStatusHistory::class)->oldest(); }
}
