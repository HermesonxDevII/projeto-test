<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models;

class School extends Model
{
    use HasFactory, softDeletes;

    public function students()
    {
        return $this->hasMany(Student::class, 'school_id');
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'school_id');
    }

    public function activities()
    {
        return $this->hasMany(Comment::class, 'school_id');
    }
}