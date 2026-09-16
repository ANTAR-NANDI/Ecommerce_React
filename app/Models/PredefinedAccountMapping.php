<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PredefinedAccountMapping extends Model { protected $fillable=['key','account_coa_id']; public function account(): BelongsTo { return $this->belongsTo(AccountCoa::class); } }
