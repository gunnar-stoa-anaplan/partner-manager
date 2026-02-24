<?php

namespace Tests\Unit\KnowledgeBase;

use App\Services\KnowledgeBase\DocumentParserFactory;
use App\Services\KnowledgeBase\Parsers\DocxParser;
use App\Services\KnowledgeBase\Parsers\HtmlParser;
use App\Services\KnowledgeBase\Parsers\PdfParser;
use App\Services\KnowledgeBase\Parsers\PlainTextParser;
use App\Services\KnowledgeBase\Parsers\SpreadsheetParser;
use InvalidArgumentException;
use Tests\TestCase;

class DocumentParserFactoryTest extends TestCase
{
    private DocumentParserFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new DocumentParserFactory;
    }

    public function test_it_returns_pdf_parser_for_pdf_mime(): void
    {
        $parser = $this->factory->make('application/pdf');

        $this->assertInstanceOf(PdfParser::class, $parser);
    }

    public function test_it_returns_docx_parser_for_docx_mime(): void
    {
        $parser = $this->factory->make('application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->assertInstanceOf(DocxParser::class, $parser);
    }

    public function test_it_returns_spreadsheet_parser_for_xlsx_mime(): void
    {
        $parser = $this->factory->make('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertInstanceOf(SpreadsheetParser::class, $parser);
    }

    public function test_it_returns_spreadsheet_parser_for_xls_mime(): void
    {
        $parser = $this->factory->make('application/vnd.ms-excel');

        $this->assertInstanceOf(SpreadsheetParser::class, $parser);
    }

    public function test_it_returns_spreadsheet_parser_for_csv_mime(): void
    {
        $parser = $this->factory->make('text/csv');

        $this->assertInstanceOf(SpreadsheetParser::class, $parser);
    }

    public function test_it_returns_html_parser_for_html_mime(): void
    {
        $parser = $this->factory->make('text/html');

        $this->assertInstanceOf(HtmlParser::class, $parser);
    }

    public function test_it_returns_plain_text_parser_for_text_mime(): void
    {
        $parser = $this->factory->make('text/plain');

        $this->assertInstanceOf(PlainTextParser::class, $parser);
    }

    public function test_it_returns_plain_text_parser_for_markdown_mime(): void
    {
        $parser = $this->factory->make('text/markdown');

        $this->assertInstanceOf(PlainTextParser::class, $parser);
    }

    public function test_it_throws_for_unsupported_mime_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported MIME type: application/octet-stream');

        $this->factory->make('application/octet-stream');
    }
}
