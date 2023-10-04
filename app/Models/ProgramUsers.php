<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramUsers extends Model
{
    use HasFactory;

    protected $table = 'programs_users';

    protected $fillable = ['program_id', 'user_id'];
}
