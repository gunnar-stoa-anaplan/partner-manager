<?php

namespace App\Services\KnowledgeBase;

use App\Services\KnowledgeBase\Contracts\DocumentParser;
use App\Services\KnowledgeBase\Parsers\DocxParser;
use App\Services\KnowledgeBase\Parsers\HtmlParser;
use App\Services\KnowledgeBase\Parsers\PdfParser;
use App\Services\KnowledgeBase\Parsers\PlainTextParser;
use App\Services\KnowledgeBase\Parsers\SpreadsheetParser;
use InvalidArgumentException;

class DocumentParserFactory
{
    /**
     * Create a parser for the given MIME type.
     */
    public function make(string $mimeType): DocumentParser
    {
        return match ($mimeType) {
            'application/pdf' => new PdfParser,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => new DocxParser,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv' => new SpreadsheetParser,
            'text/html' => new HtmlParser,
            'text/plain', 'text/markdown' => new PlainTextParser,
            default => throw new InvalidArgumentException("Unsupported MIME type: {$mimeType}"),
        };
    }
}
