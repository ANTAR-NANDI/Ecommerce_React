<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subcategory extends Model
{
    protected $fillable = ['name', 'slug', 'short_description', 'icon_media_id'];
    public function icon(): BelongsTo { return $this->belongsTo(Media::class, 'icon_media_id'); }
    public function categories(): BelongsToMany { return $this->belongsToMany(Category::class)->withTimestamps(); }
}
