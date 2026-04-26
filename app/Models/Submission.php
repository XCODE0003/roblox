<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = ['content', 'new_cookie', 'ip_address', 'user_agent'];
}
