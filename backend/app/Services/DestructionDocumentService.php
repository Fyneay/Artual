<?php

namespace App\Services;

use App\Models\Destruction;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DestructionDocumentService
{
    private string $templatePath;

    public function __construct()
    {
        $this->templatePath = resource_path('templates/act_destruction.docx');
    }


    public function generateDestructionAct(Destruction $destruction): StreamedResponse
    {
        $destruction->load(['creator', 'articles.files']);

        $tempFile = $this->generateDocumentDirectly($destruction);

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }, $this->generateFileName($destruction), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    private function generateDocumentDirectly(Destruction $destruction): string
    {
        $tempFile = sys_get_temp_dir() . '/destruction_act_' . uniqid() . '.docx';

        copy($this->templatePath, $tempFile);

        //Открывается как zip для чтения xml
        $zip = new \ZipArchive();
        if ($zip->open($tempFile) !== TRUE) {
            throw new \Exception('Не удалось открыть шаблон для обработки');
        }

        $documentXml = $zip->getFromName('word/document.xml');

        if ($documentXml === false) {
            $zip->close();
            throw new \Exception('Не удалось прочитать document.xml из шаблона');
        }

        $replacements = [
            '{title}' => $destruction->name ?? '—',
            '{period}' => $this->formatPeriod($destruction),
            '{date}' => $destruction->created_at
                ? $destruction->created_at->format('d.m.Y')
                : '—',
            '{creator}' => $destruction->creator->nickname ?? '—',
            '{articles_count}' => (string)($destruction->articles->count() ?? 0),
        ];

        foreach ($replacements as $search => $replace) {
            $replaceEscaped = htmlspecialchars($replace, ENT_XML1, 'UTF-8');
            $documentXml = str_replace($search, $replaceEscaped, $documentXml);
            $documentXml = preg_replace('/\{' . preg_quote(substr($search, 1, -1), '/') . '\}/', $replaceEscaped, $documentXml);
        }

        if ($destruction->articles->isNotEmpty()) {
            $documentXml = $this->fillTableInXml($documentXml, $destruction);
        }

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();

        \Log::info('Документ сгенерирован через прямой XML-подход');

        return $tempFile;
    }

    private function fillTableInXml(string $documentXml, Destruction $destruction): string
    {
        $articles = $destruction->articles;

        $pattern = '/<w:tr[^>]*>.*?\{number\}.*?<\/w:tr>/s';

        if (!preg_match($pattern, $documentXml, $matches)) {
            \Log::warning('Не найдена строка таблицы с переменной {number}');
            $documentXml = str_replace('{number}', '', $documentXml);
            return $documentXml;
        }

        $templateRow = $matches[0];

        $tableRows = '';
        foreach ($articles as $index => $article) {
            $number = $index + 1;
            $articleId = (string)$article->id;
            $articleName = $article->name ?? '—';
            $articleDate = $article->created_at
                ? $article->created_at->format('d.m.Y')
                : '—';

            $filesCount = $article->files ? $article->files->count() : 0;

            $row = $templateRow;
            $row = str_replace('{number}', htmlspecialchars((string)$number, ENT_XML1, 'UTF-8'), $row);
            $row = str_replace('{article_id}', htmlspecialchars($articleId, ENT_XML1, 'UTF-8'), $row);
            $row = str_replace('{article_name}', htmlspecialchars($articleName, ENT_XML1, 'UTF-8'), $row);
            $row = str_replace('{article_date}', htmlspecialchars($articleDate, ENT_XML1, 'UTF-8'), $row);
            $row = str_replace('{files_count}', htmlspecialchars((string)$filesCount, ENT_XML1, 'UTF-8'), $row);

            $tableRows .= $row;
        }

        $documentXml = preg_replace($pattern, $tableRows, $documentXml);

        \Log::info('Таблица заполнена через XML-подход', ['rows_count' => count($articles)]);

        return $documentXml;
    }

    private function fillArticlesTable(TemplateProcessor $processor, Destruction $destruction): void
    {
        $articles = $destruction->articles;

        if ($articles->isEmpty()) {
            return;
        }

        try {
        $rows = [];
            foreach ($articles as $index => $article) {
                $filesCount = $article->files ? $article->files->count() : 0;

                $rows[] = [
                    'number' => $index + 1,
                    'article_id' => (string)$article->id,
                    'article_name' => $article->name ?? '—',
                    'article_date' => $article->created_at
                        ? $article->created_at->format('d.m.Y')
                        : '—',
                    'files_count' => $filesCount,
                ];
            }

        $processor->cloneRow('number', count($rows));

            \Log::info('Строки таблицы клонированы успешно', [
                'count' => count($rows)
            ]);

            foreach ($rows as $row) {
                $num = (string)$row['number'];
                $processor->setValue("number#{$num}", $num);
                $processor->setValue("article_id#{$num}", $row['article_id']);
                $processor->setValue("article_name#{$num}", $row['article_name']);
                $processor->setValue("article_date#{$num}", $row['article_date']);
                $processor->setValue("files_count#{$num}", (string)$row['files_count']);
            }

            \Log::info('Данные таблицы заполнены успешно');

        } catch (\Exception $e) {
            \Log::warning('Ошибка при заполнении таблицы дел', [
                'destruction_id' => $destruction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function formatPeriod(Destruction $destruction): string
    {
        if ($destruction->articles->isEmpty()) {
            return '—';
        }

        $dates = $destruction->articles
            ->map(fn($article) => $article->created_at)
            ->filter()
            ->sort();

        if ($dates->isEmpty()) {
            return '—';
        }

        $start = $dates->first()->format('d.m.Y');
        $end = $dates->last()->format('d.m.Y');

        return $start === $end ? $start : "{$start} - {$end}";
    }

    private function generateFileName(Destruction $destruction): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $destruction->name);
        return "Акт_уничтожения_{$safeName}_{$destruction->id}.docx";
    }

    private function fixTemplateVariables(string $templatePath): string
    {
        if (!file_exists($templatePath)) {
            return $templatePath;
        }

        $zip = new \ZipArchive();
        if ($zip->open($templatePath) !== TRUE) {
            return $templatePath;
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($documentXml === false) {
            return $templatePath;
        }

        if (preg_match('/<w:t[^>]*>\{[a-zA-Z0-9_]+\}<\/w:t>/', $documentXml)) {
            return $templatePath;
        }

        $fixedTemplatePath = sys_get_temp_dir() . '/fixed_template_' . uniqid() . '.docx';

        copy($templatePath, $fixedTemplatePath);

        if ($zip->open($fixedTemplatePath) !== TRUE) {
            \Log::warning('Не удалось открыть шаблон для исправления');
            return $templatePath;
        }

        $documentXml = $zip->getFromName('word/document.xml');
//        $documentXml = preg_replace(
//            '/<w:t[^>]*>\{<\/w:t>(?:\s*<w:rPr>.*?<\/w:rPr>)?\s*<w:t[^>]*>([a-zA-Z0-9_]+)<\/w:t>(?:\s*<w:rPr>.*?<\/w:rPr>)?\s*<w:t[^>]*>\}<\/w:t>/',
//            '<w:t>{$1}</w:t>',
//            $documentXml
//        );

        $documentXml = preg_replace(
            '/<w:t[^>]*>\{<\/w:t>\s*<w:t[^>]*>([a-zA-Z0-9_]+)<\/w:t>\s*<w:t[^>]*>\}<\/w:t>/',
            '<w:t>{$1}</w:t>',
            $documentXml
        );

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();

        \Log::info('Шаблон исправлен для корректной обработки переменных');

        return $fixedTemplatePath;
    }
}
