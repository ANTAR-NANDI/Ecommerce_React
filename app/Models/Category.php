<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'display_order', 'icon_media_id', 'banner_media_id', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function icon(): BelongsTo { return $this->belongsTo(Media::class, 'icon_media_id'); }
    public function banner(): BelongsTo { return $this->belongsTo(Media::class, 'banner_media_id'); }
    public function subcategories(): BelongsToMany { return $this->belongsToMany(Subcategory::class)->withTimestamps(); }
}
