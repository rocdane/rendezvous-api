<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
    protected $fillable = ['langue', 'timezone','notifications_email', 'notifications_sms'];
}
