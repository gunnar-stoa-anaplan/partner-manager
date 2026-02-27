<?php

namespace App\Services\KnowledgeBase;

class MimeTypeResolver
{
    /**
     * Extension-to-MIME-type mapping aligned with DocumentParserFactory.
     *
     * @var array<string, string>
     */
    protected const EXTENSION_MAP = [
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel',
        'csv' => 'text/csv',
        'html' => 'text/html',
        'htm' => 'text/html',
        'txt' => 'text/plain',
        'md' => 'text/markdown',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ppt' => 'application/vnd.ms-powerpoint',
    ];

    /**
     * Resolve MIME type from a file extension.
     */
    public function fromExtension(string $extension): ?string
    {
        return self::EXTENSION_MAP[strtolower($extension)] ?? null;
    }

    /**
     * Resolve MIME type from a filename or path.
     */
    public function fromFilename(string $filename): ?string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return $this->fromExtension($ext);
    }
}
