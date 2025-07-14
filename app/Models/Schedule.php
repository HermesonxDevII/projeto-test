<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\{ Classroom, Course };

class Schedule extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'schedules';
    protected $fillable = ['status', 'course_id', 'weekday_id'];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function weekday()
    {
        return $this->belongsTo(Weekday::class);
    }
}