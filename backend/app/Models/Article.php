<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $table='articles';

    public $timestamps=true;

    public $fillable=['name', 'path-file','section_id','secrecy_grade','created_by', 'created_at','updated_at', 'user_id', 'list_period_id', 'location', 'type_document_id', 'description', 'status_id'];

    protected function casts() : array
    {
        return [
            'secrecy_grade' => 'boolean',
        ];
    }
    public function user() : belongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function section(): BelongsTo
    {
    return $this->belongsTo(Section::class, 'section_id', 'id');
    }


    public function signatures(): BelongsToMany
    {
        return $this->belongsToMany(Signature::class, 'article_signatures')
                    ->using(ArticleSignature::class)
                    ->withPivot('signed_at')
                    ->withTimestamps();
    }

    public function files(): HasMany
    {
        return $this->hasMany(ArticleFile::class, 'article_id', 'id');
    }

    public function destructions(): BelongsToMany
    {
        return $this->belongsToMany(Destruction::class, 'article_destruction')
                ->using(ArticleDestruction::class)
                ->withTimestamps();
    }

    public function exchanges(): BelongsToMany
    {
        return $this->belongsToMany(Exchange::class, 'article_exchange')
                ->using(ArticleExchange::class)
                ->withTimestamps();
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(Access::class, 'article_id', 'id');
    }

    public function listPeriod(): BelongsTo
    {
        return $this->belongsTo(ListPeriod::class, 'list_period_id');
    }

    public function typeDocument(): BelongsTo
    {
        return $this->belongsTo(TypeDocument::class, 'type_document_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function getExpirationDateAttribute(): ?\Illuminate\Support\Carbon
    {
        if (!$this->listPeriod || !$this->created_at) {
            return null;
        }

        if ($this->listPeriod->retention_period === 0) {
            return null;
        }

        return $this->created_at->copy()->addYears($this->listPeriod->retention_period);
    }
}
