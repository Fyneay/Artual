<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ArticleExchange extends Pivot
{
    protected $table = 'article_exchange';

    public $timestamps = true;

    protected $fillable = [
        'article_id',
        'exchange_id',
    ];
}
