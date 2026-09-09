<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['name','code','manager_name','phone','email','address','is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function productStocks(): HasMany { return $this->hasMany(WarehouseProductStock::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
}
