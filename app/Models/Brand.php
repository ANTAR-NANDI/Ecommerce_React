<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = ['name', 'icon_media_id', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function icon(): BelongsTo { return $this->belongsTo(Media::class, 'icon_media_id'); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
}
