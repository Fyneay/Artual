<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ArticleSignature extends Pivot
{
    protected $table = 'article_signatures';
    
    public $timestamps = true;
    
    protected $fillable = [
        'article_id',
        'signature_id',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];
}
