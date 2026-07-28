<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function channels()
    {
        return $this->hasMany(UserChannel::class, 'user_id', 'id');
    }
}
