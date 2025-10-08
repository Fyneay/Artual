<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    protected $table='articles';

    public $timestamps=true;

    public $fillable=['name', 'path-file','section_id','secrecy_grade','created_by', 'created_at','updated_at', 'user_id'];

    protected function casts() : array
    {
        return [
            'secrecy_grade' => 'boolean',
        ];
    }
    public function user() : belongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }


}
