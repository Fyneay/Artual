<?php

namespace App\Observers;

use App\Models\Article;
use Illuminate\Support\Facades\Log;

class ArticleObserver
{
    public function created(Article $article) : void
    {
        Log::info("Документ {name} был создан ", ['name'=> $article->name, 'path' => $article->{'path-file'}]);
    }

    public function updated(Article $article) : void
    {
        Log::info("Документ {name} был изменен ", ['name'=> $article->name, 'path' => $article->{'path-file'}]);
    }

    public function deleted(Article $article) : void
    {
        Log::info("Документ {name} был удален ", ['name'=> $article->name, 'path' => $article->{'path-file'}]);
    }
}
