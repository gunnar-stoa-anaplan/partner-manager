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
        $outcomeRowTemplate = $rowMatches[0][1];  // Row with outcome text in cell 0
        $continuationRowTemplate = $rowMatches[0][2] ?? $rowMatches[0][1]; // Row with vMerge

        // Build new rows
        $newRows = [$headerRow];

        foreach ($blueprint->outcomes as $outcome) {
            $items = $outcome->contentItems;

            if ($items->isEmpty()) {
                // Outcome with no content items — single row with just the outcome title
                $newRows[] = $this->buildOutcomeRow(
                    $outcomeRowTemplate,
                    $outcome->title,
                    '',
                    '',
                    '',
                    '',
                    false,
                    false,
                    false,
                );

                continue;
            }

            foreach ($items as $index => $item) {
                if ($index === 0) {
                    // First item uses the outcome row (shows outcome title)
                    $newRows[] = $this->buildOutcomeRow(
                        $outcomeRowTemplate,
                        $outcome->title,
                        $item->title,
                        $item->duration_display ?? '',
                        $item->source ?? '',
                        $item->prerequisites ?? '',
                        $item->delivery_applicable !== 'na',
                        $item->pre_sales_applicable !== 'na',
                        $item->gtm_applicable !== 'na',
                    );
                } else {
                    // Subsequent items use the continuation row (vMerge)
                    $newRows[] = $this->buildContinuationRow(
                        $continuationRowTemplate,
                        $item->title,
                        $item->duration_display ?? '',
                        $item->source ?? '',
                        $item->prerequisites ?? '',
                        $item->delivery_applicable !== 'na',
                        $item->pre_sales_applicable !== 'na',
                        $item->gtm_applicable !== 'na',
                    );
                }
            }
        }

        // Replace old rows with new rows
        $allOldRows = implode('', $rowMatches[0]);
        $allNewRows = implode('', $newRows);
        $xml = str_replace($allOldRows, $allNewRows, $xml);

        return $xml;
    }

    private function buildOutcomeRow(
        string $template,
        string $outcomeTitle,
        string $contentTitle,
        string $time,
        string $source,
        string $prerequisites,
        bool $delivery,
        bool $preSales,
        bool $gtm,
    ): string {
        $row = $template;

        // Cell 0: Outcome title — replace the existing outcome text
        $row = $this->replaceCellText($row, 0, $outcomeTitle);
        // Cell 2: Content title
        $row = $this->replaceCellText($row, 2, $contentTitle);
        // Cell 3: Time
        $row = $this->replaceCellText($row, 3, $time);
        // Cell 4: Source
        $row = $this->replaceCellText($row, 4, $source);
        // Cell 5: Prerequisites
        $row = $this->replaceCellText($row, 5, $prerequisites);
        // Cells 6, 7, 8: Delivery, Pre-Sales, GTM checkmarks
        $row = $this->replaceCellText($row, 6, $delivery ? "\u{2713}" : '');
        $row = $this->replaceCellText($row, 7, $preSales ? "\u{2713}" : '');
        $row = $this->replaceCellText($row, 8, $gtm ? "\u{2713}" : '');

        return $row;
    }

    private function buildContinuationRow(
        string $template,
        string $contentTitle,
        string $time,
        string $source,
        string $prerequisites,
        bool $delivery,
        bool $preSales,
        bool $gtm,
    ): string {
        $row = $template;

        // Cell 2: Content title
        $row = $this->replaceCellText($row, 2, $contentTitle);
        // Cell 3: Time
        $row = $this->replaceCellText($row, 3, $time);
        // Cell 4: Source
        $row = $this->replaceCellText($row, 4, $source);
        // Cell 5: Prerequisites
        $row = $this->replaceCellText($row, 5, $prerequisites);
        // Cells 6, 7, 8: Delivery, Pre-Sales, GTM
        $row = $this->replaceCellText($row, 6, $delivery ? "\u{2713}" : '');
        $row = $this->replaceCellText($row, 7, $preSales ? "\u{2713}" : '');
        $row = $this->replaceCellText($row, 8, $gtm ? "\u{2713}" : '');

        return $row;
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
