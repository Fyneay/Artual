<?php

namespace App\Services;

use App\Models\Section;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OpisDocumentService
{
    private string $templatePath;

    public function __construct()
    {
        $this->templatePath = resource_path('templates/opis.docx');
    }

    public function generateOpis(Section $section): StreamedResponse
    {
        $destroyedStatus = \App\Models\Status::where('name', 'уничтожен')->first();

        $articles = \App\Models\Article::query()
            ->where('section_id', $section->id)
            ->with(['listPeriod', 'status'])
            ->when($destroyedStatus, function ($query) use ($destroyedStatus) {
                $query->where(function ($q) use ($destroyedStatus) {
                    $q->whereNull('status_id')
                      ->orWhere('status_id', '!=', $destroyedStatus->id);
                });
            })
            ->get();

        $tempFile = $this->generateDocumentDirectly($section, $articles);

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }, $this->generateFileName($section), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    private function generateDocumentDirectly(Section $section, $articles): string
    {
        $tempFile = sys_get_temp_dir() . '/opis_' . uniqid() . '.docx';

        copy($this->templatePath, $tempFile);

        $zip = new \ZipArchive();
        if ($zip->open($tempFile) !== true) {
            throw new \Exception('Не удалось открыть шаблон для обработки');
        }

        $documentXml = $zip->getFromName('word/document.xml');

        if ($documentXml === false) {
            $zip->close();
            throw new \Exception('Не удалось прочитать document.xml из шаблона');
        }

        if ($articles->isNotEmpty()) {
            $documentXml = $this->fillTableInXml($documentXml, $articles);
        } else {
            $documentXml = str_replace(['{number}', '{article_id}', '{article_name}', '{article_date}'], '', $documentXml);
        }

        $user = auth()->user();
        $userNickname = $user ? ($user->nickname ?? '—') : '—';
        $userJob = '—';

        if ($user && $user->userGroup) {
            $userJob = $user->userGroup->name ?? '—';
        } elseif ($user) {
            $user->load('userGroup');
            $userJob = $user->userGroup ? ($user->userGroup->name ?? '—') : '—';
        }

        $dateNow = now()->format('d.m.Y');

        $replacements = [
            '{articles_count}' => (string)($articles->count() ?? 0),
            '{job}' => $userJob,
            '{date_now}' => $dateNow,
            '{nickname}' => $userNickname,
        ];

        foreach ($replacements as $search => $replace) {
            $replaceEscaped = htmlspecialchars($replace, ENT_XML1, 'UTF-8');
            $documentXml = str_replace($search, $replaceEscaped, $documentXml);
            $documentXml = preg_replace('/\{' . preg_quote(substr($search, 1, -1), '/') . '\}/', $replaceEscaped, $documentXml);
        }

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();

        \Log::info('Документ описи дел сгенерирован через прямой XML-подход', [
            'section_id' => $section->id,
            'articles_count' => $articles->count(),
        ]);

        return $tempFile;
    }

    private function fillTableInXml(string $documentXml, $articles): string
    {
        $pattern = '/<w:tr[^>]*>.*?\{number\}.*?<\/w:tr>/s';

        if (!preg_match($pattern, $documentXml, $matches)) {
            \Log::warning('Не найдена строка таблицы с переменной {number}');
            $documentXml = str_replace(['{number}', '{article_id}', '{article_name}', '{article_date}'], '', $documentXml);
            return $documentXml;
        }

        $templateRow = $matches[0];

        $tableRows = '';
        foreach ($articles as $index => $article) {
            $number = $index + 1;
            $articleId = (string)$article->id;
            $articleName = $article->name ?? '—';
            $articleDate = '—';
            if ($article->expiration_date) {
                $articleDate = $article->expiration_date->format('d.m.Y');
            } elseif ($article->listPeriod && $article->listPeriod->retention_period === 0) {
                $articleDate = 'Постоянно';
            }

            $row = $templateRow;
            $row = str_replace('{number}', htmlspecialchars((string)$number, ENT_XML1, 'UTF-8'), $row);
            $row = str_replace('{article_id}', htmlspecialchars($articleId, ENT_XML1, 'UTF-8'), $row);
            $row = str_replace('{article_name}', htmlspecialchars($articleName, ENT_XML1, 'UTF-8'), $row);
            $row = str_replace('{article_date}', htmlspecialchars($articleDate, ENT_XML1, 'UTF-8'), $row);

            $tableRows .= $row;
        }

        $documentXml = preg_replace($pattern, $tableRows, $documentXml);

        \Log::info('Таблица заполнена через XML-подход', ['rows_count' => $articles->count()]);

        return $documentXml;
    }

    private function generateFileName(Section $section): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $section->name ?? 'section');
        return "Опись_дел_{$safeName}_{$section->id}.docx";
    }
}
