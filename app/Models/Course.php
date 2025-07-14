<?php

namespace App\Models;

use App\Services\CourseService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'link', 'embed_code', 'type', 'start', 'end', 'description', 'module_id',
        'teacher_id', 'classroom_id', 'recorded_at'
    ];

    protected $appends = ['embedurl'];

    public function Classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function Schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id')->withTrashed();
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_course', 'course_id', 'student_id')->withPivot('status');
    }

    public function materials()
    {
        return $this->hasMany(Material::class)->with('file');
    }

    public function notification()
    {
        return $this->hasOne(CourseNotification::class);
    }

    // Mutators
    public function setRecordedAtAttribute($value)
    {
        $this->attributes['recorded_at'] = Carbon::createFromFormat('Y/m/d', $value)
            ->format('Y-m-d H:i:s');
    }

    // Accessors
    // public function getRecordedAtAttribute($value)
    // {
    //     return Carbon::parse($value)->format('d/m/Y');
    // }

    public function formattedName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->name
        );
    }

    public function embedurl(): Attribute
    {
        return new Attribute(
            get: fn ($value) => generateVideoEmbedUrl($this->link)
        );
    }

    public function hasFiles(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->materials->count() > 0 ? true : false
        );
    }

    public function weekdays(): Attribute
    {
        $weekDays = CourseService::getWeekdaysTranslated($this);

        return new Attribute(
            get: fn ($value) => $weekDays != '' ? $weekDays : '-'
        );
    }

    protected function descriptionFormatted(): Attribute
    {
        return new Attribute(
            get: fn () => $this->description ? formatTextWithHyperlinks($this->description) : ''
        );
    }

    public function percentageOfConclusionByStudent(Student $student, int $status = 1): string
    {
        return CourseService::getPercentageByStudent($this, $student, $status);
    }

    // Scopes
    public function scopeHasTypes($query, array $type = [1]): Builder
    {
        return $query->whereIn('type', $type);
    }
}
