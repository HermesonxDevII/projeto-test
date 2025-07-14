<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEvaluationValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_evaluation_id',
        'evaluation_parameter_id',
        'evaluation_value_id'
    ];

    public function studentEvaluation()
    {
        return $this->belongsTo(StudentEvaluation::class);
    }

    public function parameter()
    {
        return $this->belongsTo(EvaluationParameter::class, 'evaluation_parameter_id');
    }

    public function value()
    {
        return $this->belongsTo(EvaluationValue::class, 'evaluation_value_id');
    }
}
