<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PaymentMethod extends Model {protected $fillable=['name','account_coa_id','is_fixed'];protected function casts():array{return ['is_fixed'=>'boolean'];}public function account():BelongsTo{return $this->belongsTo(AccountCoa::class);}}
