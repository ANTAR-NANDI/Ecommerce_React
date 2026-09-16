<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;use Illuminate\Database\Eloquent\Relations\HasMany;
class OpeningBalance extends Model {protected $fillable=['financial_year_id','balance_date'];protected function casts():array{return ['balance_date'=>'date'];}public function year():BelongsTo{return $this->belongsTo(FinancialYear::class,'financial_year_id');}public function items():HasMany{return $this->hasMany(OpeningBalanceItem::class);}}
