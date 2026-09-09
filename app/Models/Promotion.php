<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    protected $fillable = [
        'type', 'title', 'code', 'description', 'banner_media_id', 'product_ids',
        'placement', 'link_url', 'discount_type', 'discount_value',
        'minimum_order_amount', 'usage_limit', 'used_count', 'budget',
        'starts_at', 'ends_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'product_ids' => 'array',
            'discount_value' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'budget' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }
}
