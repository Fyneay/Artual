<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleFile extends Model
{
    protected $table = 'article_files';

    public $timestamps = true;

    protected $fillable = [
        'article_id',
        'filename',
        'path',
        'mime_type',
        'file_size',
        'status',
        'threat_name',
    ];

    protected function casts(): array
    {  
        return [
            'file_size' => 'integer',
            'status' => 'string',
            'threat_name' => 'string',
        ];
    }
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
