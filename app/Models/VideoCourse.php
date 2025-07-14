<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoCourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'thumbnail', 'cover', 'teacher_access'];

    protected $appends = ['students_count', 'modules_count', 'classes_count', 'classes_duration_sum'];

    public function modules()
    {
        return $this->hasMany(VideoCourseModule::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_video_course')->withTimeStamps();
    }

    public function classes()
    {
        return $this->hasManyThrough(VideoCourseClass::class, VideoCourseModule::class);
    }

    public function studentsCount(): Attribute
    {
        return new Attribute(
            get: fn () => $this->students()->count()
        );
    }

    public function modulesCount(): Attribute
    {
        return new Attribute(
            get: fn () => $this->modules()->count()
        );
    }

    public function classesCount(): Attribute
    {
        return new Attribute(
            get: fn () => $this->classes()->count()
        );
    }

    public function viewedClassesCount(): Attribute
    {
        return new Attribute(
            get: function () {
                $student = selectedStudent();

                if (!$student) {
                    return 0;
                }

                return $this->classes()->whereRelation('videoCourseClassViews', 'student_id', $student->id)->count();
            }
        );
    }

    public function classesDurationSum(): Attribute
    {
        return new Attribute(
            // get: fn () => $this->classes()->sum('duration')
            get: function() {
                return sumTimesFromArray($this->classes()->pluck('duration')->toArray());
            }
        );
    }
}
