<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CourseNotification extends Model
{
    use HasFactory;
    protected $appends = ['date'];
    protected $table = 'course_notifications';
    protected $fillable = ['title', 'message', 'course_id'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function date(): Attribute
    {
        return new Attribute(
            get: fn ($value) => Carbon::parse($this->updated_at)->diffForHumans()
        );
    }
}
