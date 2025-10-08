<?php

namespace App\Services;

use App\Models\Article;
use App\Classes\DataManipulator;
class ArticleService
{
    protected DataManipulator $dataManipulator;

    public function __construct(DataManipulator $dataManipulator){
        $this->dataManipulator = $dataManipulator;
        $this->dataManipulator->model = 'App\Models\Article';
    }
    public function save(array $data) : Article
    {
        return $this->dataManipulator->save($data);
    }

    public function update(array $data, Article $article) : Article
    {
        $article = $this->dataManipulator->update($data, $article);
        return $article;
    }

    public function delete(Article $article) : Article
    {
        return $this->dataManipulator->delete($article);
    }
}