<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class OAuthProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'access_token',
        'refresh_token',
        'provider_data',
    ];

    protected $casts = [
        'provider_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
