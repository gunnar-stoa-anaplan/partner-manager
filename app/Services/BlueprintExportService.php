<?php

namespace App\Services;

use App\Models\Blueprint;
use ZipArchive;

class BlueprintExportService
{
    public function __construct(
        private string $templatePath = '',
    ) {
        if ($this->templatePath === '') {
            $this->templatePath = base_path('Blueprint_template.pptx');
        }
    }

    /**
     * Export a blueprint as a PPTX file based on the template.
     *
     * Slide 2: Blueprint name replaces the placeholder.
     * Slide 3: Title updated, table populated with outcomes + content items.
     */
    public function export(Blueprint $blueprint): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'blueprint_').'.pptx';
        copy($this->templatePath, $tempPath);

        $zip = new ZipArchive;
        $zip->open($tempPath);

        $this->populateSlide2($zip, $blueprint);
        $this->populateSlide3($zip, $blueprint);

        $zip->close();

        return $tempPath;
    }

    private function populateSlide2(ZipArchive $zip, Blueprint $blueprint): void
    {
        $xml = $zip->getFromName('ppt/slides/slide2.xml');

        // Replace the placeholder text "ADO" with the blueprint name
        $xml = $this->replaceTextElement($xml, 'ADO', $blueprint->name);

        $zip->addFromString('ppt/slides/slide2.xml', $xml);
    }

    private function populateSlide3(ZipArchive $zip, Blueprint $blueprint): void
    {
        $xml = $zip->getFromName('ppt/slides/slide3.xml');

        // Update the slide title
        $categoryLabel = Blueprint::CATEGORIES[$blueprint->category] ?? $blueprint->category;
        $slideTitle = "{$categoryLabel}: {$blueprint->name}";
        $xml = $this->replaceTextElement(
            $xml,
            'App Enablement Prerequisite: ADO Enablement',
            $slideTitle
        );

        // Build new table rows from blueprint outcomes + content items
        $xml = $this->rebuildTable($xml, $blueprint);

        $zip->addFromString('ppt/slides/slide3.xml', $xml);
    }

    private function rebuildTable(string $xml, Blueprint $blueprint): string
    {
        // Extract existing rows
        preg_match_all('/<a:tr [^>]*>.*?<\/a:tr>/s', $xml, $rowMatches);

        if (count($rowMatches[0]) < 2) {
            return $xml;
        }

        $headerRow = $rowMatches[0][0];
        $outcomeRowTemplate = $rowMatches[0][1];

        // Strip rowSpan attributes — the template uses merged rows for outcomes,
        // but we put all content as multiline text in a single row per outcome
        $outcomeRowTemplate = preg_replace('/\s*rowSpan="\d+"/', '', $outcomeRowTemplate);

        // Build one row per outcome — content items as multiline text within the row
        $newRows = [$headerRow];

        foreach ($blueprint->outcomes as $outcome) {
            $items = $outcome->contentItems;

            // Collect content lines and aggregate role applicability
            $contentLines = [];
            $timeLines = [];
            $sourceLines = [];
            $prereqLines = [];
            $hasDelivery = false;
            $hasPreSales = false;
            $hasGtm = false;

            foreach ($items as $item) {
                $contentLines[] = $item->title;
                $timeLines[] = $item->duration_display ?? '';
                $sourceLines[] = $item->source ?? '';
                $prereqLines[] = $item->prerequisites ?? '';
                $hasDelivery = $hasDelivery || $item->delivery_applicable !== 'na';
                $hasPreSales = $hasPreSales || $item->pre_sales_applicable !== 'na';
                $hasGtm = $hasGtm || $item->gtm_applicable !== 'na';
            }

            $row = $outcomeRowTemplate;

            // Cell 0: Outcome title (numbered)
            $row = $this->replaceCellText($row, 0, $outcome->sort_order.'. '.$outcome->title);
            // Cell 2: Content items as multiline
            $row = $this->setCellMultiline($row, 2, $contentLines);
            // Cell 3: Time per content item
            $row = $this->setCellMultiline($row, 3, $timeLines);
            // Cell 4: Source per content item
            $row = $this->setCellMultiline($row, 4, $sourceLines);
            // Cell 5: Prerequisites per content item
            $row = $this->setCellMultiline($row, 5, $prereqLines);
            // Cells 6, 7, 8: Aggregated role applicability
            $row = $this->replaceCellText($row, 6, $hasDelivery ? "\u{2713}" : '');
            $row = $this->replaceCellText($row, 7, $hasPreSales ? "\u{2713}" : '');
            $row = $this->replaceCellText($row, 8, $hasGtm ? "\u{2713}" : '');

            $newRows[] = $row;
        }

        // Replace old rows with new rows
        $allOldRows = implode('', $rowMatches[0]);
        $allNewRows = implode('', $newRows);
        $xml = str_replace($allOldRows, $allNewRows, $xml);

        return $xml;
    }

    /**
     * Set multiline text in a specific cell by creating multiple <a:p> paragraphs.
     */
    private function setCellMultiline(string $rowXml, int $cellIndex, array $lines): string
    {
        if (empty($lines)) {
            return $this->replaceCellText($rowXml, $cellIndex, '');
        }

        if (count($lines) === 1) {
            return $this->replaceCellText($rowXml, $cellIndex, $lines[0]);
        }

        // Locate the target cell
        $offset = 0;
        $cellCount = 0;

        while (($pos = strpos($rowXml, '<a:tc', $offset)) !== false) {
            $endPos = strpos($rowXml, '</a:tc>', $pos);
            if ($endPos === false) {
                break;
            }
            $endPos += strlen('</a:tc>');

            if ($cellCount === $cellIndex) {
                $cellXml = substr($rowXml, $pos, $endPos - $pos);

                // Extract the first <a:p> as a template for paragraph formatting
                if (preg_match('/(<a:p>.*?<\/a:p>)/s', $cellXml, $pMatch)) {
                    $pTemplate = $pMatch[1];

                    // Build new paragraphs from the template
                    $newParagraphs = [];
                    foreach ($lines as $line) {
                        $p = preg_replace_callback('/<a:t>(.*?)<\/a:t>/s', function () use ($line, &$replaced) {
                            if (! $replaced) {
                                $replaced = true;

                                return '<a:t>'.htmlspecialchars($line, ENT_XML1).'</a:t>';
                            }

                            return '<a:t></a:t>';
                        }, $pTemplate);
                        $replaced = false;
                        $newParagraphs[] = $p;
                    }

                    // Replace all existing <a:p> elements with the new ones
                    $newCellXml = preg_replace(
                        '/(<a:p>.*?<\/a:p>\s*)+/s',
                        implode('', $newParagraphs),
                        $cellXml,
                        1
                    );

                    return substr($rowXml, 0, $pos).$newCellXml.substr($rowXml, $endPos);
                }

                break;
            }

            $cellCount++;
            $offset = $endPos;
        }

        return $rowXml;
    }

    /**
     * Replace text content in a specific cell (by index) within a table row.
     */
    private function replaceCellText(string $rowXml, int $cellIndex, string $newText): string
    {
        // Match all <a:tc> elements
        $offset = 0;
        $cellCount = 0;

        while (($pos = strpos($rowXml, '<a:tc', $offset)) !== false) {
            // Find the closing </a:tc>
            $endPos = strpos($rowXml, '</a:tc>', $pos);
            if ($endPos === false) {
                break;
            }
            $endPos += strlen('</a:tc>');

            if ($cellCount === $cellIndex) {
                $cellXml = substr($rowXml, $pos, $endPos - $pos);
                $newCellXml = $this->setCellTextContent($cellXml, $newText);

                return substr($rowXml, 0, $pos).$newCellXml.substr($rowXml, $endPos);
            }

            $cellCount++;
            $offset = $endPos;
        }

        return $rowXml;
    }

    /**
     * Replace all <a:t> text content within a cell, preserving formatting.
     */
    private function setCellTextContent(string $cellXml, string $newText): string
    {
        // Find the first <a:t> element and replace its content,
        // then clear any additional <a:t> elements (preserves run formatting)
        $replaced = false;

        return preg_replace_callback('/<a:t>(.*?)<\/a:t>/s', function ($match) use ($newText, &$replaced) {
            if (! $replaced) {
                $replaced = true;

                return '<a:t>'.htmlspecialchars($newText, ENT_XML1).'</a:t>';
            }

            // Clear subsequent text runs in the same cell
            return '<a:t></a:t>';
        }, $cellXml);
    }

    /**
     * Replace a specific text string within <a:t> elements.
     */
    private function replaceTextElement(string $xml, string $search, string $replace): string
    {
        return str_replace(
            '<a:t>'.$search.'</a:t>',
            '<a:t>'.htmlspecialchars($replace, ENT_XML1).'</a:t>',
            $xml
        );
    }
}
