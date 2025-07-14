<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Casts\Attribute;

class Calendar extends Model
{
    use HasFactory, softDeletes;
    protected $fillable = ['title', 'embed_code'];

    public function grades()
    {
        return $this->hasMany(CalendarGrade::class);
        // return $this->belongsToMany(Grade::class, 'grade_id');
    }

    // Accessors
    public function formattedName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->title
        );
    }

    protected function gradesList(): Attribute
    {
        $grades = $this->grades;

        return Attribute::make(
            get: fn ($value) => $grades->count() > 0 ? $grades->implode('gradeName', ', ') : '-'
        );
    }

    protected function gradesIdList(): Attribute
    {
        $grades = $this->grades;

        return Attribute::make(
            get: fn ($value) => $grades->pluck('grade_id')->toArray()
        );
    }

    public function embedurl(): Attribute
    {
        return new Attribute(
            get: fn ($value) => generateVideoEmbedUrl($this->embed_code)
        );
    }

    public function findGradeId($grade_id)
    {
        return $this->grades->where('grade_id', '=', $grade_id)->value('id') ?? null;
    }
}
