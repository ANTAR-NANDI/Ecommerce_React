<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsMenu extends Model
{
    protected $fillable = ['label', 'url', 'location', 'sort_order', 'is_active', 'open_in_new_tab'];
    protected function casts(): array { return ['is_active' => 'boolean', 'open_in_new_tab' => 'boolean']; }
}
