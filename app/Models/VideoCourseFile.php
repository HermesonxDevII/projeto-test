<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class VideoCourseFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'path', 'owner_id', 'video_course_class_id'];

    public function class()
    {
        return $this->belongsTo(VideoCourseClass::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    protected function bytes(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Storage::disk('video_course_class_materials')->size($this->path)
        );
    }

    protected function size(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => formattedSize($this->bytes)
        );
    }
}
