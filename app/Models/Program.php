<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'programs';

    protected $fillable = ['title', 'creator', 'public_key', 'private_key'];


    public function users()
    {
        return $this->hasMany(ProgramUsers::class, 'program_id', 'id');
    }
}
