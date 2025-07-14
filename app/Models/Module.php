<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'position', 'classroom_id'];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    // Accessors
    public function formattedName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->name
        );
    }

    public function hasCourses(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->courses->count() > 0 ? true : false
        );
    }
}
