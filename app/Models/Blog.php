<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blog extends Model
{
    protected $fillable = ['title','slug','category_id','tags','description','thumbnail_media_id','status','published_at','meta_title','meta_description'];
    protected function casts(): array { return ['tags'=>'array','published_at'=>'datetime']; }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function thumbnail(): BelongsTo { return $this->belongsTo(Media::class, 'thumbnail_media_id'); }
}
