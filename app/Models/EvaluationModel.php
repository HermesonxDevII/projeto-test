<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "evaluation_models";

    protected $fillable = ['title'];

    public function parameters()
    {
        return $this->hasMany(EvaluationParameter::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
}
