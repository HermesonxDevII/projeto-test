<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'email', 'profile', 'module', 'action', 'action_model', 'action_id', 'old_request', 'new_request', 'ip_address', 'route'];
}
