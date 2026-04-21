<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ArticleDestruction extends Pivot
{
    protected $table = 'article_destruction';
    
    public $timestamps = true;
    
    protected $fillable = [
        'article_id',
        'destruction_id',
    ];
}
