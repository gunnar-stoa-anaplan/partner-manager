<?php

namespace App\Services\KnowledgeBase\Parsers;

use App\Services\KnowledgeBase\Contracts\DocumentParser;
use PhpOffice\PhpWord\IOFactory;

class DocxParser implements DocumentParser
{
    public function parse(string $filePath): string
    {
        $phpWord = IOFactory::load($filePath);
        $text = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $elementText = $element->getText();

                    if (is_string($elementText) && trim($elementText) !== '') {
                        $text[] = $elementText;
                    }
                }
            }
        }

        return implode("\n", $text);
    }
}
