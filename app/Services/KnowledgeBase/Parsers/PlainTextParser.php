<?php

namespace App\Services\KnowledgeBase\Parsers;

use App\Services\KnowledgeBase\Contracts\DocumentParser;

class PlainTextParser implements DocumentParser
{
    public function parse(string $filePath): string
    {
        return file_get_contents($filePath);
    }
}
