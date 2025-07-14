<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Address extends Model
{
    use HasFactory;
    protected $table = "user_address";
    protected $fillable = ['user_id', 'zip_code', 'province', 'city', 'number', 'district', 'complement'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}