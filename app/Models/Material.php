<?php

namespace App\Models;

use App\Services\CourseService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    protected $fillable = ['course_id', 'file_id'];
    protected $table = 'course_files';

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
