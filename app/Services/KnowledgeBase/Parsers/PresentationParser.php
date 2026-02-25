<?php

namespace App\Services\KnowledgeBase\Parsers;

use App\Services\KnowledgeBase\Contracts\DocumentParser;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Shape\Group;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\Table;

class PresentationParser implements DocumentParser
{
    public function parse(string $filePath): string
    {
        // PhpPresentation triggers DOMXPath warnings on slide masters with
        // vendor-specific XML namespace prefixes. Temporarily suppress
        // warnings so Laravel's error handler does not convert them to exceptions.
        $presentation = @IOFactory::load($filePath);
        $text = [];

        foreach ($presentation->getAllSlides() as $index => $slide) {
            $slideNumber = $index + 1;
            $lines = $this->extractShapeText($slide->getShapeCollection());

            if ($lines !== []) {
                $text[] = "--- Slide {$slideNumber} ---";
                array_push($text, ...$lines);
            }
        }

        return implode("\n", $text);
    }

    /**
     * @param  iterable<\PhpOffice\PhpPresentation\AbstractShape>  $shapes
     * @return list<string>
     */
    private function extractShapeText(iterable $shapes): array
    {
        $lines = [];

        foreach ($shapes as $shape) {
            if ($shape instanceof RichText) {
                $line = $this->extractRichText($shape);

                if ($line !== '') {
                    $lines[] = $line;
                }
            } elseif ($shape instanceof Table) {
                array_push($lines, ...$this->extractTableText($shape));
            } elseif ($shape instanceof Group) {
                array_push($lines, ...$this->extractShapeText($shape->getShapeCollection()));
            }
        }

        return $lines;
    }

    private function extractRichText(RichText $richText): string
    {
        $parts = [];

        foreach ($richText->getParagraphs() as $paragraph) {
            $paragraphText = '';

            foreach ($paragraph->getRichTextElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $paragraphText .= $element->getText();
                }
            }

            if (trim($paragraphText) !== '') {
                $parts[] = trim($paragraphText);
            }
        }

        return implode("\n", $parts);
    }

    /**
     * @return list<string>
     */
    private function extractTableText(Table $table): array
    {
        $lines = [];

        foreach ($table->getRows() as $row) {
            $cells = [];

            foreach ($row->getCells() as $cell) {
                $cellParts = [];

                foreach ($cell->getParagraphs() as $paragraph) {
                    $paragraphText = '';

                    foreach ($paragraph->getRichTextElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $paragraphText .= $element->getText();
                        }
                    }

                    if (trim($paragraphText) !== '') {
                        $cellParts[] = trim($paragraphText);
                    }
                }

                $cells[] = implode(' ', $cellParts);
            }

            $rowText = implode(' | ', $cells);

            if (trim($rowText) !== '') {
                $lines[] = $rowText;
            }
        }

        return $lines;
    }
}
