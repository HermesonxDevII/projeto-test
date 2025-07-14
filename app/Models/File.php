<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'path', 'status', 'owner_id'];
    protected $appends = ['size'];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function material()
    {
        return $this->HasOne(Material::class);
    }

    // Accessors
    protected function bytes(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Storage::disk('course_materials')->size($this->path)
        );
    }

    protected function size(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => formattedSize($this->bytes)
        );
    }

}
