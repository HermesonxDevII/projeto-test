<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoCourseModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'position', 'open', 'video_course_id'];

    protected $appends = ['classes_count'];

    public function videoCourse()
    {
        return $this->belongsTo(VideoCourse::class, 'video_course_id');
    }

    public function classes()
    {
        return $this->hasMany(VideoCourseClass::class);
    }

    public function viewedClassesCount(): int
    {
        $user = loggedUser();
        $student = selectedStudent();

        if ($user && $user->is_guardian && $student) {
            return $this->classes()->whereRelation('videoCourseClassViews', 'student_id', $student->id)->count();
        }

        return 0;
    }

    public function classesCount(): Attribute
    {
        return new Attribute(
            get: fn () => $this->classes()->count()
        );
    }

    public function getClassesOrdered()
    {
        return $this->classes()->orderBy('position')->get();
    }
}
