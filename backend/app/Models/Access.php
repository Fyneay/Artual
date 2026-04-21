<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Access extends Model
{
    use HasFactory;

    protected $table = 'access';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'created_by',
        'granted_by',
        'article_id',
        'access_date',
        'close_date',
        'reason',
        'status_id',
    ];

    protected function casts(): array
    {
        return [
            'access_date' => 'date',
            'close_date' => 'date',
        ];
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }


    public function grantedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by', 'id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }
}

