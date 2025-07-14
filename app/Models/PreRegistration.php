<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreRegistration extends Model
{
    use HasFactory;

    protected $table = 'pre_registrations';

    protected $fillable = [
        'study_plan',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'zipcode',
        'province',
        'city',
        'district',
        'address',
        'complement',
        'japan_time',
        'children',
        'family_structure',
        'family_workers',
        'workload',
        'speaks_japanese',
        'studies_at_home',
        'will_return_to_home_country',
        'home_language',
        'student_name',
        'student_class',
        'student_language',
        'student_japan_arrival',
        'student_is_shy',
        'student_time_alone',
        'student_rotine',
        'student_extra_activities',
        'student_is_focused',
        'student_is_organized',
        'student_has_good_memory',
        'student_has_a_study_plan',
        'student_reviews_exams',
        'student_reads',
        'student_studies',
        'student_watches_tv',
        'student_uses_internet',
        'student_has_smartphone',
        'kokugo_grade',
        'shakai_grade',
        'sansuu_grade',
        'rika_grade',
        'eigo_grade',
        'student_has_difficult',
        'student_difficult_in_class',
        'student_frequency_in_support_class',
        'student_will_take_entrance_exams',
        'student_has_taken_online_classes',
        'guardian_expectations',
        'guardian_concerns',
        'guardian_motivations',
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
