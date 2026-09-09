<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsFooterSetting extends Model
{
    protected $fillable = ['about_title', 'about_text', 'facebook_url', 'instagram_url', 'youtube_url', 'whatsapp_url', 'copyright_text'];
}
