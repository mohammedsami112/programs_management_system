<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessTokens extends Model
{
    use HasFactory;

    protected $table = 'personal_access_tokens';

    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'tokenable_id');
    }
}
