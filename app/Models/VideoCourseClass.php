<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoCourseClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'furigana_title', 'original_title',  'translated_title', 'link',
        'duration', 'description', 'position', 'teacher', 'video_course_module_id',
        'thumbnail'
    ];

    protected $appends = ['embedurl'];

    public function module()
    {
        return $this->belongsTo(VideoCourseModule::class, 'video_course_module_id');
    }

    public function files()
    {
        return $this->hasMany(VideoCourseFile::class);
    }

    public function videoCourseClassViews()
    {
        return $this->belongsToMany(Student::class, 'video_course_class_views')->withPivot('viewed_at');
    }

    public function getViewedAttribute(): bool
    {
        $user = loggedUser();
        $student = selectedStudent();

        if ($user && $user->is_guardian && $student) {
            return $this->videoCourseClassViews()->where('student_id', $student->id)->exists();
        }

        return false;
    }

    public function embedurl(): Attribute
    {
        return new Attribute(
            get: fn ($value) => generateVideoEmbedUrl($this->link)
        );
    }

    protected function descriptionFormatted(): Attribute
    {
        return new Attribute(
            get: fn () => $this->description ? formatTextWithHyperlinks($this->description) : ''
        );
    }

    protected function isLastOfModule(): Attribute
    {
        return new Attribute(
            get: fn () => $this->position === $this->module->classes()->whereNotNull('link')->count()
        );
    }
}
