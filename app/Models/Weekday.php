<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Weekday extends Model
{
    use HasFactory;

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class)->withPivot('name');
    }

    protected function firstLetter(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->name[0]
        );
    }
}
