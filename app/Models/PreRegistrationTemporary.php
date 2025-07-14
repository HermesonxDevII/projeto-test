<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreRegistrationTemporary extends Model
{
    use HasFactory;

    protected $table = 'pre_registrations_temporary';

    protected $fillable = [
        'guardian_name',
        'guardian_email',
        'guardian_phone',        
        'province',
        'student_name',
        'student_class',
        'student_language',
        'student_japan_arrival',
        'student_has_difficult',
        'student_difficult_in_class',
        'student_id'
    ];

    protected $appends = [
        'formatted_created_at',
    ];

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
