<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = 'logs';

    protected $guarded = [];

    protected $hidden = ['user_id', 'program_id'];

    protected $with = ['program', 'user'];

    public function program()
    {
        return $this->hasOne(Program::class, 'id', 'program_id')->select(['id', 'title']);
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')->select(['id', 'username']);
    }
}
