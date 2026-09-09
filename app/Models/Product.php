<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name','slug','short_description','description','category_id','subcategory_ids','brand_id','color_id','unit_id','size_id','sku','weight','buying_price','selling_price','discount_type','discount','stock_quantity','thumbnail_media_id','gallery_media_ids','video_url','meta_title','meta_description','meta_keywords','is_active'];
    protected function casts(): array { return ['subcategory_ids'=>'array','gallery_media_ids'=>'array','is_active'=>'boolean']; }
    public function thumbnail(): BelongsTo { return $this->belongsTo(Media::class, 'thumbnail_media_id'); }
    public function warehouseStocks(): HasMany { return $this->hasMany(WarehouseProductStock::class); }
}
