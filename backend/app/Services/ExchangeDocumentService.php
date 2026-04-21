<?php

namespace App\Services;

use App\Models\Exchange;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExchangeDocumentService
{
    private string $templatePath;

    public function __construct()
    {
        $this->templatePath = resource_path('templates/act_priema.docx');
    }
    public function generateExchangeAct(Exchange $exchange): StreamedResponse
    {
        $exchange->load(['creator', 'articles']);

        $tempFile = $this->generateDocumentDirectly($exchange);

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }, $this->generateFileName($exchange), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    private function generateDocumentDirectly(Exchange $exchange): string
    {
        $tempFile = sys_get_temp_dir() . '/exchange_act_' . uniqid() . '.docx';

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


        $replacements = [
            '{title}' => $exchange->name ?? '—',
            '{reason}' => $exchange->reason ?? '—',
            '{date}' => $exchange->created_at
                ? $exchange->created_at->format('d.m.Y')
                : '—',
            '{fund_name}' => $exchange->fund_name ?? '—',
            '{receiving_organization}' => $exchange->receiving_organization ?? '—',
            '{creator}' => $exchange->creator->nickname ?? '—',
            '{articles_count}' => (string)($exchange->articles->count() ?? 0),
        ];

        foreach ($replacements as $search => $replace) {
            $replaceEscaped = htmlspecialchars($replace, ENT_XML1, 'UTF-8');
            $documentXml = str_replace($search, $replaceEscaped, $documentXml);
            $documentXml = preg_replace('/\{' . preg_quote(substr($search, 1, -1), '/') . '\}/', $replaceEscaped, $documentXml);
        }

        if ($exchange->articles->isNotEmpty()) {
            $documentXml = $this->fillTableInXml($documentXml, $exchange);
        }

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();

        \Log::info('Документ акта приема сгенерирован через прямой XML-подход');

        return $tempFile;
    }

    private function fillTableInXml(string $documentXml, Exchange $exchange): string
    {
        $articles = $exchange->articles;

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

            $row = $templateRow;
            $row = str_replace('{number}', htmlspecialchars((string)$number, ENT_XML1, 'UTF-8'), $row);
            $row = str_replace('{article_id}', htmlspecialchars($articleId, ENT_XML1, 'UTF-8'), $row);
            $row = str_replace('{article_name}', htmlspecialchars($articleName, ENT_XML1, 'UTF-8'), $row);
            $row = str_replace('{article_date}', htmlspecialchars($articleDate, ENT_XML1, 'UTF-8'), $row);

            $tableRows .= $row;
        }

        $documentXml = preg_replace($pattern, $tableRows, $documentXml);

        \Log::info('Таблица заполнена через XML-подход', ['rows_count' => count($articles)]);

        return $documentXml;
    }

    private function generateFileName(Exchange $exchange): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $exchange->name);
        return "Акт_приема_{$safeName}_{$exchange->id}.docx";
    }
}
