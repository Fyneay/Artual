<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListPeriod extends Model
{
    use HasFactory;

    protected $table = 'lists_periods';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'retention_period',
    ];

    protected function casts(): array
    {
        return [
            'retention_period' => 'integer',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'list_period_id');
    }
}
