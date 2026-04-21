<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Destruction extends Model
{
    use HasFactory;

    protected $table = 'destruction';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'created_by',
    ];


    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_destruction')
                    ->using(ArticleDestruction::class)
                    ->withTimestamps();
    }

}
