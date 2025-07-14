<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoCourseClassView extends Model
{
    use HasFactory;

    const CREATED_AT = 'viewed_at';
    const UPDATED_AT = null;
}
