<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Media extends Model
{
    protected $fillable = ['path', 'original_name', 'mime_type', 'size'];

    public function iconCategories(): HasMany { return $this->hasMany(Category::class, 'icon_media_id'); }
    public function bannerCategories(): HasMany { return $this->hasMany(Category::class, 'banner_media_id'); }
    public function iconSubcategories(): HasMany { return $this->hasMany(Subcategory::class, 'icon_media_id'); }

    public function getUrlAttribute(): string { return asset('storage/'.$this->path); }
}
