<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Signature extends Model
{
    protected $fillable = [
        'signature_data',
        'certificate_name',
        'certificate_subject',
        'signature_hash',
        'signed_by',
    ];

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_signatures')
                    ->using(ArticleSignature::class)
                    ->withPivot('signed_at')
                    ->withTimestamps();
    }
}
