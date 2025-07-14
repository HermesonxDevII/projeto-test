<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationValue extends Model
{
    use HasFactory;
    protected $table = "evaluation_values";

    protected $fillable = ['title'];

    public function parameter()
    {
        return $this->belongsTo(EvaluationParameter::class);
    }
}
