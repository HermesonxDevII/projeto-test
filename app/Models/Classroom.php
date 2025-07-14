<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Services\ClassroomService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\Group;

class Classroom extends Model
{
    use HasFactory, softDeletes;
    protected $fillable = ['name', 'school_id', 'evaluation_model_id', 'description', 'status', 'thumbnail'];

    public function user()
    {
        return $this->belongsToMany(User::class, 'classroom_id');
    }

    public function wireGroups()
    {
        return $this->hasMany(Group::class);
    }

    public function school()
    {
        return $this->belongsTo(school::class, 'school_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_classroom')->withTimeStamps();
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function evaluationModel()
    {
        return $this->belongsTo(EvaluationModel::class, 'evaluation_model_id')->withTrashed();
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    // Accessors
    public function studentsCount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->students->count()
        );
    }

    public function formattedName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->name
        );
    }

    public function liveCourses(): Attribute
    {
        return new Attribute(
            get: fn ($value) => ClassroomService::getCoursesByClassroom($this, 1)
        );
    }

    public function recordedCourses(): Attribute
    {
        return new Attribute(
            get: fn ($value) => ClassroomService::getCoursesByClassroom($this, 2)
        );
    }

    public function startEnd(): Attribute
    {
        return new Attribute(
            get: fn ($value) => ClassroomService::getStartEnd($this)
        );
    }

    public function weekDays(): Attribute
    {
        $weekDays = ClassroomService::getWeekdays($this);
        return new Attribute(
            get: fn ($value) => $weekDays != '' ? $weekDays : '-'
        );
    }

    public function nextClassDate(): Attribute
    {
        [$date, $smallerDate] = '';
        $weekday_numbers = ClassroomService::getSchedules($this)->pluck('weekday.weekday_number');
        foreach($weekday_numbers as $key => $number){
            $date = getNextClassDate($number);
            if($key==0){
                $smallerDate = $date;
            }else if($date < $smallerDate ){
                $smallerDate = $date;
            }
        }

        return new Attribute(
            get: fn ($value) => $smallerDate
        );
    }

    public function hasCoursesWithTeacherId(int $teacherId, int $courseType = 0): bool
    {
        // return true;
        $courses = $this->courses()->where('teacher_id', $teacherId);

        if ($courseType !== 0) {
            $courses->where('type', $courseType);
        }

        return $courses->exists();
    }

    // Scopes
    public function scopeIsActive($query, int $status = 1): Builder
    {
        return $query->where('status', $status);
    }
}
