<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CalendarGrade extends Model
{
    use HasFactory;
    protected $table = 'calendar_grade';
    protected $fillable = ['calendar_id', 'grade_id'];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    // Accessors
    public function gradeName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->grade->name
        );
    }
}
