<?php

namespace Tests\Unit\KnowledgeBase;

use App\Services\KnowledgeBase\Parsers\PresentationParser;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PHPUnit\Framework\TestCase;

class PresentationParserTest extends TestCase
{
    private string $fixturePath;

    private PresentationParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new PresentationParser;
        $this->fixturePath = sys_get_temp_dir().'/kb_test_'.uniqid().'.pptx';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->fixturePath)) {
            unlink($this->fixturePath);
        }

        parent::tearDown();
    }

    public function test_it_extracts_text_from_slides(): void
    {
        $pptx = new PhpPresentation;

        $slide1 = $pptx->getActiveSlide();
        $shape1 = $slide1->createRichTextShape();
        $shape1->createTextRun('Demand Planning Overview');

        $slide2 = $pptx->createSlide();
        $shape2 = $slide2->createRichTextShape();
        $shape2->createTextRun('Inventory Optimization');

        $this->writeFixture($pptx);

        $result = $this->parser->parse($this->fixturePath);

        $this->assertStringContainsString('--- Slide 1 ---', $result);
        $this->assertStringContainsString('Demand Planning Overview', $result);
        $this->assertStringContainsString('--- Slide 2 ---', $result);
        $this->assertStringContainsString('Inventory Optimization', $result);
    }

    public function test_it_extracts_text_from_tables(): void
    {
        $pptx = new PhpPresentation;

        $slide = $pptx->getActiveSlide();
        $table = $slide->createTableShape(2);
        $row = $table->createRow();
        $row->nextCell()->createTextRun('Module');
        $row->nextCell()->createTextRun('Purpose');

        $this->writeFixture($pptx);

        $result = $this->parser->parse($this->fixturePath);

        $this->assertStringContainsString('Module', $result);
        $this->assertStringContainsString('Purpose', $result);
    }

    public function test_it_handles_empty_slides(): void
    {
        $pptx = new PhpPresentation;

        // First slide is empty (default), second has content
        $slide2 = $pptx->createSlide();
        $shape = $slide2->createRichTextShape();
        $shape->createTextRun('Content on second slide');

        $this->writeFixture($pptx);

        $result = $this->parser->parse($this->fixturePath);

        $this->assertStringNotContainsString('--- Slide 1 ---', $result);
        $this->assertStringContainsString('--- Slide 2 ---', $result);
        $this->assertStringContainsString('Content on second slide', $result);
    }

    public function test_it_extracts_multiple_paragraphs(): void
    {
        $pptx = new PhpPresentation;

        $slide = $pptx->getActiveSlide();
        $shape = $slide->createRichTextShape();
        $shape->createTextRun('First paragraph');
        $shape->createParagraph()->createTextRun('Second paragraph');

        $this->writeFixture($pptx);

        $result = $this->parser->parse($this->fixturePath);

        $this->assertStringContainsString('First paragraph', $result);
        $this->assertStringContainsString('Second paragraph', $result);
    }

    private function writeFixture(PhpPresentation $pptx): void
    {
        $writer = IOFactory::createWriter($pptx, 'PowerPoint2007');
        $writer->save($this->fixturePath);
    }
}
