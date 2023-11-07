<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'programs';

    protected $fillable = ['title', 'creator', 'public_key', 'private_key', 'api_token'];


    public function users()
    {
        return $this->hasMany(ProgramUsers::class, 'program_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(ProgramFile::class, 'program_id', 'id');
    }
}
