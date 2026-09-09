<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['first_name','last_name','phone','email','password','profile_media_id','date_of_birth','gender','is_active'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password'=>'hashed','date_of_birth'=>'date','is_active'=>'boolean']; }
    public function profileMedia(): BelongsTo { return $this->belongsTo(Media::class, 'profile_media_id'); }
    public function addresses(): HasMany { return $this->hasMany(CustomerAddress::class); }
    public function wishlistItems(): HasMany { return $this->hasMany(CustomerWishlist::class); }
    public function orders(): HasMany { return $this->hasMany(EcommerceOrder::class); }
    public function getFullNameAttribute(): string { return trim($this->first_name.' '.$this->last_name); }
}
