<?php

namespace App\Services\KnowledgeBase\Parsers;

use App\Services\KnowledgeBase\Contracts\DocumentParser;
use Smalot\PdfParser\Parser;

class PdfParser implements DocumentParser
{
    public function parse(string $filePath): string
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($filePath);

        return $pdf->getText();
    }
}
