<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';

    protected $fillable = ['name','type_id','user_id'];

    protected $primaryKey = 'id';

    public function type() : BelongsTo
    {
        return $this->belongsTo(TypeSection::class,'type_id','id');
    }

    public function user() : HasOne
    {
        return $this->hasOne(User::class,'sections_user_id_foreign','user_id');
    }
    public function articles() : HasMany
    {
        return $this->hasMany(Article::class,'section_id','id');
    }
}
