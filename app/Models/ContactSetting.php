<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $fillable = ['phone','whatsapp','messenger_url','email','address','business_hours'];
}
