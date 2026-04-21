<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{

    public $timestamps = true;

    protected $table = 'users_groups';

    protected $fillable = ['name'];

    public function users() : HasMany
    {
        return $this->hasMany(User::class, 'role_id','id');
    }
}
