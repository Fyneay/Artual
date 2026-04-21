<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeDocument extends Model
{
    use HasFactory;

    protected $table = 'types_document';

    public $timestamps = true;

    protected $fillable = ['name'];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'type_document_id', 'id');
    }
}
