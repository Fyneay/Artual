<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TypeSection extends Model
{
    protected $table = 'types_sections';

    public $timestamps = true;


    protected $fillable = ['name', 'created_at','updated_at'];

    public function sections() : HasMany
    {
        return $this->hasMany(Section::class, 'type_id', 'id');
    }

}
