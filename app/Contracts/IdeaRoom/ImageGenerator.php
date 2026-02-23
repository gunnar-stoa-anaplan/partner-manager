<?php

namespace App\Contracts\IdeaRoom;

interface ImageGenerator
{
    /**
     * Generate an image from a text prompt.
     *
     * @param  array{
     *   prompt: string,
     *   size?: string,
     *   quality?: string,
     *   style?: string,
     *   model?: string,
     * }  $options
     * @return string Raw image binary data (PNG)
     */
    public function generate(array $options): string;
}
