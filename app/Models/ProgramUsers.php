<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramUsers extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'programs_users';

    protected $fillable = ['program_id', 'user_id', 'max_sessions'];

    protected $hidden = ['user_id'];

    protected $with = ['user'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
