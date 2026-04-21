<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exchange extends Model
{
    use HasFactory;

    protected $table = 'exchange';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'reason',
        'fund_name',
        'receiving_organization',
        'created_by',
    ];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_exchange')
                    ->using(ArticleExchange::class)
                    ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
