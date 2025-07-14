<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory;

    protected $table = "evaluations";

    protected $fillable = ['date', 'title', 'content', 'author', 'classroom_id', 'evaluation_model_id'];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function parameters()
    {
        return $this->hasMany(EvaluationParameter::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function evaluationModel()
    {
        return $this->belongsTo(EvaluationModel::class);
    }

    public function studentEvaluations()
    {
        return $this->hasMany(StudentEvaluation::class);
    }

    public function getDateAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d', $value)->format('d/m/Y');
    }
}
