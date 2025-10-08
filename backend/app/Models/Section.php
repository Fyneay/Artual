<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';

    protected $fillable = ['name','type_id','user_id'];

    protected $primaryKey = 'id';

    public function type() : hasOne
    {
        return $this->hasOne(TypeSection::class,'sections_type_id_foreign','type_id');
    }

    public function user() : HasOne
    {
        return $this->hasOne(User::class,'sections_user_id_foreign','user_id');
    }
}
