<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permissions';

    protected $fillable = ['title', 'permissions'];

    protected $appends = ['users_count'];


    public function getUsersCountAttribute()
    {
        return User::where('permission', '=', $this->id)->count();
    }
}
