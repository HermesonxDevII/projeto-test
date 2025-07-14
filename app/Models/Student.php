<?php

namespace App\Models;

use App\Services\StudentService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;
use App\Traits\Chatable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\Conversation;

class Student extends Model
{
    use Chatable, HasFactory, softDeletes;

    protected $fillable = [
        'full_name',
        'guardian_id',
        'email',
        'nickname',
        'domain_language_id',
        'system_language_id',
        'school_id',
        'grade_id',
        'avatar_id',
        'status',
        'notes',
        'send_email',
        'classroom_onboarding_preference',
        'course_onboarding_preference',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'date:Y-m-d',
    ];

    public function conversations(): MorphToMany
    {
        return $this->morphToMany(
            Conversation::class,
            'participantable',
            'wire_participants',
            'participantable_id',
            'conversation_id'
        )->withPivot('conversation_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'student_classroom')->withTimeStamps();
    }

    public function grade()
    {
        return $this->hasOne(Grade::class, 'id', 'grade_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'student_course')->withPivot('status');
    }

    public function domain_language()
    {
        return $this->hasOne(Language::class, 'id', 'domain_language_id');
    }

    public function system_language()
    {
        return $this->hasOne(Language::class, 'id', 'system_language_id');
    }

    public function guardian()
    {
        return $this->belongsTo(User::class);
    }

    public function videoCourses()
    {
        return $this->belongsToMany(VideoCourse::class, 'student_video_course')->withPivot('created_at');
    }

    public function videoCourseClassViews()
    {
        return $this->belongsToMany(VideoCourseClass::class, 'video_course_class_views')->withPivot('viewed_at');
    }

    public function preRegistration()
    {
        return $this->hasOne(PreRegistration::class);
    }

    public function preRegistrationTemporary()
    {
        return $this->hasOne(PreRegistrationTemporary::class);
    }

    public function studentEvaluations()
    {
        return $this->hasMany(StudentEvaluation::class, 'student_id', 'id');
    }

    public function getDisplayNameAttribute(): ?string
    {
      return $this->full_name ?? 'user';
    }


    // Accessors
    public function formattedFullName(): Attribute
    {
        return new Attribute(
            get: fn($value) => $this->full_name
        );
    }

    public function nameInitial(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->full_name[0]
        );
    }

    public function firstName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => explode(' ', $this->formattedFullName)[0]
        );
    }

    public function expiresAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? date('Y-m-d', strtotime($value)) : null
        );
    }

    public function expiresAtFormatted(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->expires_at ? Carbon::parse($this->expires_at)->format('d/m/Y') : null
        );
    }

    public function accessUntil(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->expires_at
                ? Carbon::parse($this->expires_at)->subDay()->format('d/m/Y')
                : null
        );
    }

    public function isExpired(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->expires_at
                ? now()->greaterThan($this->expires_at)
                : false
        );
    }

    public function getFinishedCourses($courseType, $module_id, $classroom_id)
    {
        return StudentService::getCourses($this, $courseType, $module_id, $classroom_id)->count();
    }

    public function recentCourses(int $take = 10)
    {
        return StudentService::getRecentCourses($this, $take);
    }

    public function courseIsDone(Course $course): bool
    {
        return StudentService::courseIsDone($this, $course);
    }

    public function coursesWeekdays(): Attribute
    {
        $coursesWeekdays = StudentService::getCoursesWeekdays($this, 2);

        return Attribute::make(
            get: fn($value) => $coursesWeekdays != '' ? $coursesWeekdays : '-'
        );
    }

    public function classroomsList(): Attribute
    {
        $classrooms = $this->classrooms;

        return Attribute::make(
            get: fn($value) => $classrooms->count() > 0 ? $classrooms->implode('formattedName', ', ') : '-'
        );
    }

    public function hasValidPreRegistration(): bool
    {
        return !is_null($this->preRegistration);
    }

    public function hasValidPreRegistrationTemporary(): bool
    {
        return !is_null($this->preRegistrationTemporary);
    }

    // Scopes
    public function scopeIsActive($query, int $status = 1): Builder
    {
        return $query->where('status', $status);
    }

    public function canCreateChats(): bool
    {        
        return true;
    }

    public function canCreateGroups(): bool
    {
        return false;
    }
}
