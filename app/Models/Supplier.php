<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    protected $fillable=['name','phone','email','address','photo_media_id','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function photo(): BelongsTo { return $this->belongsTo(Media::class,'photo_media_id'); }
}
