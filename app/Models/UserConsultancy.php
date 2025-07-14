<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConsultancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'has_consultancy',
    ];

    protected $casts = [
        'has_consultancy' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
