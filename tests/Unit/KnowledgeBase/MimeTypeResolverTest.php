<?php

namespace Tests\Unit\KnowledgeBase;

use App\Services\KnowledgeBase\MimeTypeResolver;
use PHPUnit\Framework\TestCase;

class MimeTypeResolverTest extends TestCase
{
    private MimeTypeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new MimeTypeResolver;
    }

    public function test_resolves_pdf_extension(): void
    {
        $this->assertSame('application/pdf', $this->resolver->fromExtension('pdf'));
    }

    public function test_resolves_docx_extension(): void
    {
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            $this->resolver->fromExtension('docx')
        );
    }

    public function test_resolves_xlsx_extension(): void
    {
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $this->resolver->fromExtension('xlsx')
        );
    }

    public function test_resolves_xls_extension(): void
    {
        $this->assertSame('application/vnd.ms-excel', $this->resolver->fromExtension('xls'));
    }

    public function test_resolves_csv_extension(): void
    {
        $this->assertSame('text/csv', $this->resolver->fromExtension('csv'));
    }

    public function test_resolves_html_and_htm_extensions(): void
    {
        $this->assertSame('text/html', $this->resolver->fromExtension('html'));
        $this->assertSame('text/html', $this->resolver->fromExtension('htm'));
    }

    public function test_resolves_txt_extension(): void
    {
        $this->assertSame('text/plain', $this->resolver->fromExtension('txt'));
    }

    public function test_resolves_md_extension(): void
    {
        $this->assertSame('text/markdown', $this->resolver->fromExtension('md'));
    }

    public function test_resolves_pptx_extension(): void
    {
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            $this->resolver->fromExtension('pptx')
        );
    }

    public function test_resolves_ppt_extension(): void
    {
        $this->assertSame('application/vnd.ms-powerpoint', $this->resolver->fromExtension('ppt'));
    }

    public function test_returns_null_for_unsupported_extension(): void
    {
        $this->assertNull($this->resolver->fromExtension('zip'));
        $this->assertNull($this->resolver->fromExtension('exe'));
    }

    public function test_is_case_insensitive(): void
    {
        $this->assertSame('application/pdf', $this->resolver->fromExtension('PDF'));
        $this->assertSame('text/html', $this->resolver->fromExtension('HTML'));
    }

    public function test_from_filename_extracts_extension(): void
    {
        $this->assertSame('application/pdf', $this->resolver->fromFilename('report.pdf'));
        $this->assertSame('text/plain', $this->resolver->fromFilename('docs/readme.txt'));
    }

    public function test_from_filename_returns_null_for_no_extension(): void
    {
        $this->assertNull($this->resolver->fromFilename('Makefile'));
    }
}
