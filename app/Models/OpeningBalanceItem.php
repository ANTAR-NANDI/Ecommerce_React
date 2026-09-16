<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OpeningBalanceItem extends Model {protected $fillable=['account_coa_id','debit','credit'];public function account():BelongsTo{return $this->belongsTo(AccountCoa::class);}}
