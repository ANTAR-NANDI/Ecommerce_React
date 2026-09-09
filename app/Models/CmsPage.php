<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = ['title', 'slug', 'meta_title', 'meta_description', 'content', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
}
