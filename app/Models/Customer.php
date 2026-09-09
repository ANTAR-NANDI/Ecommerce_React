<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = ['first_name','last_name','phone','email','password','profile_media_id','date_of_birth','gender','is_active'];
    protected $hidden = ['password'];
    protected function casts(): array { return ['password'=>'hashed','date_of_birth'=>'date','is_active'=>'boolean']; }
    public function profileMedia(): BelongsTo { return $this->belongsTo(Media::class, 'profile_media_id'); }
    public function getFullNameAttribute(): string { return trim($this->first_name.' '.$this->last_name); }
}
