<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypeSection extends Model
{
    protected $table = 'types_sections';

    public $timestamps = true;


    protected $fillable = ['name', 'created_at','updated_at'];

    public function sections() : belongsTo
    {
        return $this->belongsTo(Section::class, 'sections_type_id_foreign', 'id');
    }

}
