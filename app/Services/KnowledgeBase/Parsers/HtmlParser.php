<?php

namespace App\Services\KnowledgeBase\Parsers;

use App\Services\KnowledgeBase\Contracts\DocumentParser;

class HtmlParser implements DocumentParser
{
    public function parse(string $filePath): string
    {
        $html = file_get_contents($filePath);

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Collapse whitespace
        $text = (string) preg_replace('/[ \t]+/', ' ', $text);
        $text = (string) preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
