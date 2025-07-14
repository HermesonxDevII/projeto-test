<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use HasFactory, softDeletes;
    protected $fillable = ['name', 'short_name'];

    public function students()
    {
        return $this->hasMany(Student::class, 'id');
    }
}
