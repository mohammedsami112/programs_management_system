<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramFile extends Model
{
    use HasFactory;

    protected $table = 'program_files';

    protected $guarded = [];
}
