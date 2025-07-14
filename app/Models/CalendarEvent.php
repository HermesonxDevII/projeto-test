<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color',
        'start',
        'end',
        'months',
        'days',
        'weekdays',
        'repeat',
        'stop_repetition',
        'classroom_id',
        'all_classrooms'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start' => 'datetime:H:i:s',
        'end' => 'datetime:H:i:s',
        'weekdays' => 'array',
    ];

    protected $appends = [
        'start_formatted',
        'end_formatted',
        'stop_repetition_formatted',
    ];

    public function getStopRepetitionFormattedAttribute()
    {
        return date('d/m/Y', strtotime($this->stop_repetition));
    }

    public function getStartFormattedAttribute()
    {
        return date('H:i', strtotime($this->start));
    }

    public function getEndFormattedAttribute()
    {
        return date('H:i', strtotime($this->end));
    }

    /**
     * Get the classroom that owns the calendar event.
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
