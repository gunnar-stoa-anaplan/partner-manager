<?php

namespace App\Services\KnowledgeBase\Contracts;

interface DocumentParser
{
    /**
     * Parse a document file and return its text content.
     */
    public function parse(string $filePath): string;
}
