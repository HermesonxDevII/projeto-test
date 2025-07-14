<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEvaluation extends Model
{
    use HasFactory;

    protected $fillable = ['evaluation_id', 'student_id', 'comment'];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function values()
    {
        return $this->hasMany(StudentEvaluationValue::class);
    }
}
