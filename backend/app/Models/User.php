<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'users';
    protected $fillable = ['nickname','password','email', 'role_id'];
    protected $primaryKey = 'id';

    public function section() : belongsTo
    {
        return $this->belongsTo(Section::class,'sections_user_id_foreign','id');
    }

    // public function userGroup() : hasOne
    // {
    //     return $this->hasOne(UserGroup::class,'users_role_id_foreign','role_id');
    // }
    public function userGroup() : belongsTo
    {
        return $this->belongsTo(UserGroup::class,'role_id','id');
    }

    public function articles() : hasMany
    {
        return $this->hasMany(Article::class,'user_id','id');
    }
}
