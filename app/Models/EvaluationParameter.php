<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationParameter extends Model
{
    use HasFactory;
    protected $table = "evaluation_parameters";

    protected $fillable = ['title', 'required'];

    public function models()
    {
        return $this->belongsTo(EvaluationModel::class);
    }

    public function values()
    {
        return $this->hasMany(EvaluationValue::class);
    }
}
